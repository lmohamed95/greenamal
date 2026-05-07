<?php
/**
 * GreenAmal · Configuration
 *
 * Edit DB credentials below for your local environment.
 * For production (Namecheap), copy this file as config.local.php and override.
 */

// =====================================================================
// Database (defaults match MAMP)
// =====================================================================
define('DB_HOST', '127.0.0.1');
define('DB_PORT', '3306');           // Homebrew MySQL: 3306 · MAMP: 8889
define('DB_NAME', 'greenamal');
define('DB_USER', 'root');
define('DB_PASS', '');               // Homebrew: empty · MAMP: 'root'

// =====================================================================
// Site
// =====================================================================
define('SITE_NAME', 'GreenAmal');
define('SITE_URL', 'http://localhost:8000');  // adjust to your local URL · Namecheap prod: https://greenamal.com
define('CURRENCY_SYMBOL', 'DH');
define('SHIPPING_FEE', 30);
define('FREE_SHIPPING_THRESHOLD', 350);
define('CONTACT_EMAIL', 'contact@greenamal.com');
define('CONTACT_PHONE', '+212 627-634472');
define('WHATSAPP_NUMBER', '212627634472');

// =====================================================================
// Environment
// =====================================================================
define('APP_ENV', 'local');           // 'local' | 'production'
define('APP_DEBUG', APP_ENV === 'local');

// Local override (gitignored in production)
if (file_exists(__DIR__ . '/config.local.php')) {
    require __DIR__ . '/config.local.php';
}

// Error display (only in local)
if (APP_DEBUG) {
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
} else {
    error_reporting(E_ERROR | E_PARSE);
    ini_set('display_errors', '0');
}

// Default timezone
date_default_timezone_set('Africa/Casablanca');

// Session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
