<?php
/**
 * Admin image upload endpoint.
 *
 * POST multipart/form-data:
 *   - image:  the file
 *   - target: 'categories' | 'products' (defaults to 'products')
 *
 * Returns JSON: { ok, url, filename }
 */

// Always answer in JSON, even on fatal errors. The widget tries to JSON.parse
// the response — an HTML error page would surface as "Réponse serveur invalide".
header('Content-Type: application/json; charset=utf-8');
ob_start();

set_error_handler(function ($severity, $message, $file, $line) {
    if (!(error_reporting() & $severity)) return false;
    throw new ErrorException($message, 0, $severity, $file, $line);
});

register_shutdown_function(function () {
    $err = error_get_last();
    if ($err && in_array($err['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR], true)) {
        @ob_clean();
        http_response_code(500);
        echo json_encode([
            'ok'      => false,
            'error'   => 'fatal',
            'message' => $err['message'] . ' @ ' . basename($err['file']) . ':' . $err['line'],
        ]);
    } else {
        @ob_end_flush();
    }
});

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/image.php';

// Don't let admin_require_login() redirect this XHR endpoint to an HTML login
// page — answer in JSON instead so the upload widget can show a real error.
if (!admin_logged_in()) {
    http_response_code(401);
    echo json_encode(['ok' => false, 'error' => 'unauthorized', 'message' => 'Session expirée, reconnectez-vous.']);
    exit;
}

// Bail early if GD isn't compiled in — image_make_responsive needs it.
if (!function_exists('imagecreatetruecolor')) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => 'no_gd', 'message' => 'Extension GD manquante sur le serveur. Demandez à l\'hébergeur d\'activer php-gd.']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['ok' => false, 'error' => 'method_not_allowed']);
    exit;
}

$given_csrf = $_POST['_csrf'] ?? '';
$stored_csrf = $_SESSION['_csrf'] ?? '';
if (!is_string($given_csrf) || !is_string($stored_csrf) || $stored_csrf === '' || !hash_equals($stored_csrf, $given_csrf)) {
    http_response_code(403);
    echo json_encode(['ok' => false, 'error' => 'csrf']);
    exit;
}

if (empty($_FILES['image']) || !is_array($_FILES['image'])) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'no_file']);
    exit;
}

$f = $_FILES['image'];
$target = $_POST['target'] ?? 'products';
$target = in_array($target, ['categories', 'products', 'hero'], true) ? $target : 'products';

// Upload error?
if ($f['error'] !== UPLOAD_ERR_OK) {
    $error_messages = [
        UPLOAD_ERR_INI_SIZE   => 'Fichier trop volumineux (limite serveur).',
        UPLOAD_ERR_FORM_SIZE  => 'Fichier trop volumineux (limite formulaire).',
        UPLOAD_ERR_PARTIAL    => 'Téléversement interrompu.',
        UPLOAD_ERR_NO_FILE    => 'Aucun fichier reçu.',
        UPLOAD_ERR_NO_TMP_DIR => 'Configuration serveur invalide.',
        UPLOAD_ERR_CANT_WRITE => 'Impossible d\'écrire sur le serveur.',
    ];
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'upload_error', 'message' => $error_messages[$f['error']] ?? 'Erreur inconnue']);
    exit;
}

// Size check (5 MB)
$max_bytes = 5 * 1024 * 1024;
if ($f['size'] > $max_bytes) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'too_large', 'message' => 'L\'image dépasse 5 Mo.']);
    exit;
}

// MIME / extension check · don't trust client. Prefer finfo (more accurate);
// fall back to getimagesize() since not every shared host has the fileinfo
// extension enabled, and we only accept images here anyway.
$allowed = [
    'image/jpeg' => 'jpg',
    'image/png'  => 'png',
    'image/webp' => 'webp',
    'image/gif'  => 'gif',
];
$mime = null;
if (function_exists('finfo_open')) {
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime  = $finfo ? finfo_file($finfo, $f['tmp_name']) : null;
    if ($finfo) finfo_close($finfo);
}
if (!$mime) {
    $probe = @getimagesize($f['tmp_name']);
    $mime  = $probe['mime'] ?? null;
}
if (!$mime || !isset($allowed[$mime])) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'bad_type', 'message' => 'Format accepté : JPG, PNG, WebP, GIF.']);
    exit;
}
// Double-check it's actually a decodable image (defends against polyglot files)
if (@getimagesize($f['tmp_name']) === false) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'invalid_image']);
    exit;
}

// Slug-style base name (no original filename → no path-traversal vector)
$base = bin2hex(random_bytes(8)) . '-' . date('YmdHis');
$rel_dir = "/assets/img/uploads/{$target}";
$abs_dir = realpath(__DIR__ . '/..') . $rel_dir;

if (!is_dir($abs_dir) && !mkdir($abs_dir, 0755, true)) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => 'mkdir_failed']);
    exit;
}

// Move the upload temp file to a staging path
$staging = $abs_dir . '/' . $base . '.tmp';
if (!move_uploaded_file($f['tmp_name'], $staging)) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => 'move_failed']);
    exit;
}

// Build the responsive set: <base>.jpg (1600), <base>.webp (1600), <base>-md.webp (800), <base>-md.jpg (800)
try {
    $paths = image_make_responsive($staging, $abs_dir, $base);
    @unlink($staging);
} catch (Throwable $e) {
    @unlink($staging);
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => 'optimize_failed', 'message' => $e->getMessage()]);
    exit;
}

@chmod($paths['jpg'], 0644);
@chmod($paths['webp'], 0644);
@chmod($paths['jpg_mobile'], 0644);
@chmod($paths['webp_mobile'], 0644);

$canonical_url = $rel_dir . '/' . $base . '.jpg';

echo json_encode([
    'ok'              => true,
    'url'             => $canonical_url,
    'filename'        => $base . '.jpg',
    'webp_url'        => $rel_dir . '/' . $base . '.webp',
    'mobile_url'      => $rel_dir . '/' . $base . '-md.jpg',
    'mobile_webp_url' => $rel_dir . '/' . $base . '-md.webp',
    'original_size'   => $f['size'],
    'optimized_size'  => filesize($paths['jpg']),
    'mime'            => $mime,
]);
