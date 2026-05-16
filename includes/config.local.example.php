<?php
/**
 * GreenAmal · Production overrides
 *
 * Copy this file to `config.local.php` on the production server and fill in the
 * real values. `config.local.php` is loaded automatically by config.php BEFORE
 * the defaults, so anything you define here wins.
 *
 * IMPORTANT:
 *   - Never commit config.local.php to git (already excluded via .gitignore).
 *   - When pasting from chat/docs, NEVER include the surrounding ``` markdown
 *     fences — a single backtick in this file is a fatal PHP parse error
 *     and produces an unhelpful blank 500 on every page.
 */

// =====================================================================
// Database · values from cPanel → MySQL® Databases
// (DB names/users on cPanel are auto-prefixed with the cPanel username)
// =====================================================================
// define('DB_HOST', 'localhost');
// define('DB_PORT', '3306');
// define('DB_NAME', 'cpaneluser_main');
// define('DB_USER', 'cpaneluser_app');
// define('DB_PASS', 'CHANGE_ME_LONG_RANDOM_STRING');

// =====================================================================
// Site URL · must match the canonical domain you serve from (https + apex)
// =====================================================================
// define('SITE_URL', 'https://greenamal.com');

// =====================================================================
// Environment · 'production' disables on-screen error display
// =====================================================================
// define('APP_ENV', 'production');

// =====================================================================
// APP_SECRET · HMAC key for tokenised links (order confirmations, password
// resets, etc.). REQUIRED in production — without a strong value, links from
// emails are forgeable. Generate locally with:
//   php -r "echo bin2hex(random_bytes(32));"
// =====================================================================
// define('APP_SECRET', 'paste-the-64-hex-char-output-here');

// =====================================================================
// Currency · displayed after every price (e.g. "129 DH")
// Default in config.php is 'DH'; override here if needed.
// MUST be a quoted string — `define('CURRENCY_SYMBOL', DH)` without quotes
// produces a numeric undefined-constant fallback in PHP <8 and a fatal in 8+.
// =====================================================================
// define('CURRENCY_SYMBOL', 'DH');

// =====================================================================
// Contact · used in footer, transactional emails, schema.org JSON-LD
// =====================================================================
// define('CONTACT_EMAIL', 'contact@greenamal.com');
// define('CONTACT_PHONE', '+212 627-634472');
// define('WHATSAPP_NUMBER', '212627634472');

// =====================================================================
// Mail · sender address (must match a real mailbox on the domain
// so SPF/DKIM align and messages don't land in spam)
// =====================================================================
// define('MAIL_FROM', 'noreply@greenamal.com');

// =====================================================================
// Optional · Resend API key for higher-deliverability transactional email
// (lets includes/mail.php switch from PHP mail() to Resend if implemented)
// =====================================================================
// define('RESEND_API_KEY', 're_xxxxxxxxxxxxxxxx');
