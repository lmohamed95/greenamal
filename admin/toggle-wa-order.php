<?php
/**
 * POST /admin/toggle-wa-order.php
 * Flips the `whatsapp_order_enabled` setting (0 ↔ 1).
 * When ON, the public site shows the green WhatsApp FAB and the
 * "Commander rapidement via WhatsApp" CTA on the product page.
 */
require_once __DIR__ . '/../includes/auth.php';
admin_require_login();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('settings.php');
}
csrf_verify();

$current = db_value("SELECT setting_value FROM settings WHERE setting_key = 'whatsapp_order_enabled'");
$next    = ($current === '1') ? '0' : '1';

db_query(
    "INSERT INTO settings (setting_key, setting_value) VALUES ('whatsapp_order_enabled', ?)
     ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)",
    [$next]
);

flash_set('success', $next === '1'
    ? 'Commande via WhatsApp activée · le bouton flottant et le CTA produit sont désormais visibles.'
    : 'Commande via WhatsApp désactivée · les boutons sont masqués sur le site.');

$back = $_POST['back'] ?? 'settings.php';
$back = preg_replace('#[^a-z0-9_\-./?=&]#i', '', $back);
redirect($back);
