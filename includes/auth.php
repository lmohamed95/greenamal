<?php
/**
 * GreenAmal · Admin authentication
 */

require_once __DIR__ . '/helpers.php';

/**
 * On the /admin/ surface, surface PHP errors directly to the page so the
 * site owner can diagnose 500s without SSH access to the error log. Public
 * pages keep display_errors off.
 */
if (strpos($_SERVER['REQUEST_URI'] ?? '', '/admin/') === 0) {
    @ini_set('display_errors', '1');
    @ini_set('display_startup_errors', '1');
    error_reporting(E_ALL);

    // Catch the fatal-after-output case (white screen of death) and dump it.
    register_shutdown_function(function () {
        $err = error_get_last();
        if ($err && in_array($err['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR], true)) {
            // Don't double-render if PHP already showed the error.
            if (!headers_sent()) http_response_code(500);
            echo "\n<pre style=\"background:#fde6e2;color:#a82e10;padding:16px;border-radius:8px;font:13px/1.5 ui-monospace,Menlo,monospace;white-space:pre-wrap;margin:16px;\">\n";
            echo "PHP fatal error\n";
            echo "  message : " . htmlspecialchars($err['message']) . "\n";
            echo "  file    : " . htmlspecialchars($err['file']) . "\n";
            echo "  line    : " . (int) $err['line'] . "\n";
            echo "</pre>";
        }
    });
}

function admin_user(): ?array {
    if (empty($_SESSION['admin_user_id'])) return null;
    static $user = null;
    if ($user === null) {
        $user = db_one('SELECT id, email, first_name, last_name, role FROM admin_users WHERE id = ?', [$_SESSION['admin_user_id']]);
    }
    return $user;
}

function admin_logged_in(): bool {
    return admin_user() !== null;
}

function admin_require_login(): void {
    if (!admin_logged_in()) {
        redirect('login');
    }
}

function admin_login(string $email, string $password): bool {
    $user = db_one('SELECT id, password_hash FROM admin_users WHERE email = ?', [$email]);
    if (!$user) return false;
    if (!password_verify($password, $user['password_hash'])) return false;
    session_regenerate_id(true); // defeat session fixation across the privilege boundary
    $_SESSION['admin_user_id'] = (int) $user['id'];
    db_query('UPDATE admin_users SET last_login_at = NOW() WHERE id = ?', [$user['id']]);
    return true;
}

function admin_logout(): void {
    unset($_SESSION['admin_user_id']);
    session_regenerate_id(true);
}
