<?php
/**
 * GreenAmal · Production overrides
 *
 * Copy this file to `config.local.php` on the production server and fill in the
 * real credentials. `config.local.php` is loaded automatically by config.php
 * after the defaults, so anything you redefine here wins.
 *
 * IMPORTANT: never commit config.local.php to git (already excluded).
 */

// =====================================================================
// Database (Namecheap MySQL · values from cPanel → MySQL Databases)
// =====================================================================
// define('DB_HOST', 'localhost');
// define('DB_PORT', '3306');
// define('DB_NAME', 'greenam_main');
// define('DB_USER', 'greenam_app');
// define('DB_PASS', 'CHANGE_ME_LONG_RANDOM_STRING');

// =====================================================================
// Site URL · must match the canonical domain you serve from (https + apex)
// =====================================================================
// define('SITE_URL', 'https://greenamal.com');

// =====================================================================
// Environment · flip to 'production' to disable error display
// =====================================================================
// define('APP_ENV', 'production');

// =====================================================================
// Mail · sender address (must match a real mailbox on the domain to pass SPF/DKIM)
// =====================================================================
// define('MAIL_FROM', 'noreply@greenamal.com');

// =====================================================================
// Optional · Resend API key for higher-deliverability transactional email
// (lets includes/mail.php switch from PHP mail() to Resend if implemented)
// =====================================================================
// define('RESEND_API_KEY', 're_xxxxxxxxxxxxxxxx');
