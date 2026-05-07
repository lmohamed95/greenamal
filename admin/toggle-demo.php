<?php
/**
 * POST /admin/toggle-demo.php
 * Flips the `dashboard_demo_mode` setting (0 ↔ 1).
 */
require_once __DIR__ . '/../includes/auth.php';
admin_require_login();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('index.php');
}
csrf_verify();

$current = db_value("SELECT setting_value FROM settings WHERE setting_key = 'dashboard_demo_mode'");
$next = ($current === '1') ? '0' : '1';

db_query(
    "INSERT INTO settings (setting_key, setting_value) VALUES ('dashboard_demo_mode', ?)
     ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)",
    [$next]
);

flash_set('success', $next === '1'
    ? 'Mode démo activé · le tableau de bord affiche maintenant des données fictives.'
    : 'Mode démo désactivé · retour aux données réelles.');

$back = $_POST['back'] ?? 'index.php';
$back = preg_replace('#[^a-z0-9_\-./?=&]#i', '', $back);
redirect($back);
