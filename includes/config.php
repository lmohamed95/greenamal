<?php
/**
 * GreenAmal · Configuration
 *
 * Defaults below are aimed at local dev (MAMP / Homebrew MySQL). On
 * production, drop a `config.local.php` next to this file with overrides —
 * it is loaded first so its `define()`s win over these defaults.
 */

// Load production / local overrides BEFORE defaults so they actually win.
// (PHP can't redefine an already-defined constant; defaults below use the
//  `defined(...) || define(...)` idiom so they only fill in what's missing.)
if (file_exists(__DIR__ . '/config.local.php')) {
    require __DIR__ . '/config.local.php';
}

// =====================================================================
// Database (defaults match MAMP / Homebrew)
// =====================================================================
defined('DB_HOST') || define('DB_HOST', '127.0.0.1');
defined('DB_PORT') || define('DB_PORT', '3306');     // Homebrew MySQL: 3306 · MAMP: 8889
defined('DB_NAME') || define('DB_NAME', 'greenamal');
defined('DB_USER') || define('DB_USER', 'root');
defined('DB_PASS') || define('DB_PASS', '');         // Homebrew: empty · MAMP: 'root'

// =====================================================================
// Site
// =====================================================================
defined('SITE_NAME') || define('SITE_NAME', 'GreenAmal');
defined('SITE_URL')  || define('SITE_URL', 'http://localhost:8000');
defined('CURRENCY_SYMBOL')        || define('CURRENCY_SYMBOL', 'DH');
defined('SHIPPING_FEE')           || define('SHIPPING_FEE', 30);
defined('FREE_SHIPPING_THRESHOLD')|| define('FREE_SHIPPING_THRESHOLD', 350);
defined('CONTACT_EMAIL')          || define('CONTACT_EMAIL', 'contact@greenamal.com');
defined('CONTACT_PHONE')          || define('CONTACT_PHONE', '+212 627-634472');
defined('WHATSAPP_NUMBER')        || define('WHATSAPP_NUMBER', '212627634472');

// =====================================================================
// Environment
// =====================================================================
defined('APP_ENV')   || define('APP_ENV', 'local');                    // 'local' | 'production'
defined('APP_DEBUG') || define('APP_DEBUG', APP_ENV === 'local');

// HMAC secret for tokenised links (order-confirmation, password reset, etc.)
// MUST be overridden in config.local.php on production with a long random string.
defined('APP_SECRET') || define('APP_SECRET', 'CHANGE-ME-IN-config.local.php-' . hash('sha256', __DIR__));

// Error display (only in local) · always log
error_reporting(E_ALL);
if (APP_DEBUG) {
    ini_set('display_errors', '1');
} else {
    ini_set('display_errors', '0');
    ini_set('log_errors', '1');
    $log_dir = __DIR__ . '/../storage/logs';
    if (!is_dir($log_dir)) @mkdir($log_dir, 0755, true);
    ini_set('error_log', $log_dir . '/php-errors.log');
}

// Default timezone
date_default_timezone_set('Africa/Casablanca');

// Session — harden cookie before starting
if (session_status() === PHP_SESSION_NONE) {
    $is_https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https')
        || (($_SERVER['SERVER_PORT'] ?? '') == 443);
    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => '/',
        'domain'   => '',
        'secure'   => $is_https,
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_name('GASESSID');
    session_start();
}
