<?php
/**
 * POST /admin/toggle-coming-soon.php
 * Flips the `coming_soon_mode` setting (0 ↔ 1).
 */
require_once __DIR__ . '/../includes/auth.php';
admin_require_login();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('index.php');
}
csrf_verify();

$current = db_value("SELECT setting_value FROM settings WHERE setting_key = 'coming_soon_mode'");
$next = ($current === '1') ? '0' : '1';

db_query(
    "INSERT INTO settings (setting_key, setting_value) VALUES ('coming_soon_mode', ?)
     ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)",
    [$next]
);

flash_set('success', $next === '1'
    ? 'Mode « Bientôt disponible » activé · la boutique est masquée pour les visiteurs.'
    : 'Boutique de nouveau accessible aux visiteurs.');

$back = $_POST['back'] ?? 'index.php';
$back = preg_replace('#[^a-z0-9_\-./?=&]#i', '', $back);
redirect($back);
