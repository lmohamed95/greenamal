<?php
/**
 * Router for PHP's built-in dev server.
 *
 *   php -S localhost:8000 router.php
 *
 * Mirrors the rewrite rules from .htaccess so clean URLs (/boutique,
 * /p/<slug>, etc.) work in local development. Apache uses .htaccess in
 * production — the built-in server doesn't, hence this shim.
 */

$uri  = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$root = __DIR__;

// 1. Serve real static files (CSS, JS, images, fonts) as-is.
$static = $root . $uri;
if ($uri !== '/' && is_file($static)) {
    return false;
}

// 2. French URL aliases — exact matches.
$aliases = [
    '/boutique'              => '/shop.php',
    '/notre-histoire'        => '/about.php',
    '/panier'                => '/cart.php',
    '/paiement'              => '/checkout.php',
    '/mon-compte'            => '/account.php',
    '/connexion'             => '/login.php',
    '/inscription'           => '/register.php',
    '/deconnexion'           => '/logout.php',
    '/mot-de-passe-oublie'   => '/forgot-password.php',
    '/nouveau-mot-de-passe'  => '/reset-password.php',
    '/favoris'               => '/wishlist.php',
    '/recherche'             => '/search.php',
    '/confidentialite'       => '/privacy.php',
    '/mentions-legales'      => '/mentions.php',
    '/retours'               => '/returns.php',
    '/confirmation-commande' => '/order-confirmation.php',
    '/blog'                  => '/blog.php',
    '/contact'               => '/contact.php',
];

$clean = rtrim($uri, '/') ?: '/';
if (isset($aliases[$clean])) {
    require $root . $aliases[$clean];
    return true;
}

// 3. Pretty slug routes.
if (preg_match('#^/p/([a-z0-9\-]+)/?$#i', $clean, $m)) {
    $_GET['slug'] = $_REQUEST['slug'] = $m[1];
    require $root . '/product.php';
    return true;
}
if (preg_match('#^/c/([a-z0-9\-]+)/?$#i', $clean, $m)) {
    $_GET['cat'] = $_REQUEST['cat'] = $m[1];
    require $root . '/shop.php';
    return true;
}
if (preg_match('#^/post/([a-z0-9\-]+)/?$#i', $clean, $m)) {
    $_GET['slug'] = $_REQUEST['slug'] = $m[1];
    require $root . '/blog-post.php';
    return true;
}

// 4. /foo → /foo.php when foo.php exists at project root.
if ($clean !== '/' && preg_match('#^/([^.]+?)/?$#', $clean, $m)) {
    $php = $root . '/' . $m[1] . '.php';
    if (is_file($php)) {
        require $php;
        return true;
    }
}

// 5. Root → index.php
if ($uri === '/' || $uri === '') {
    require $root . '/index.php';
    return true;
}

// 6. Fallback: 404
http_response_code(404);
require $root . '/404.php';
return true;
