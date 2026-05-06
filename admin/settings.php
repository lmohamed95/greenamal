<?php
require_once __DIR__ . '/../includes/auth.php';
admin_require_login();

$page_title = 'Paramètres';
$current = 'settings';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();
    foreach ($_POST as $k => $v) {
        if ($k === '_csrf') continue;
        if (!is_string($v)) continue; // skip arrays
        db_query(
            "INSERT INTO settings (setting_key, setting_value) VALUES (?, ?)
             ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)",
            [$k, $v]
        );
    }
    redirect('settings.php?saved=1');
}

$settings = [];
foreach (db_all("SELECT setting_key, setting_value FROM settings") as $row) {
    $settings[$row['setting_key']] = $row['setting_value'];
}

$admins = db_all("SELECT id, email, first_name, last_name, role, last_login_at FROM admin_users ORDER BY id");

require __DIR__ . '/_includes/header.php';
?>

<div class="page">
  <div class="breadcrumb-admin"><a href="index.php">Tableau de bord</a><span>/</span><span>Paramètres</span></div>
  <div class="page-head">
    <div>
      <h1>Paramètres</h1>
      <p>Configuration de la boutique</p>
    </div>
  </div>

  <?php if (!empty($_GET['saved'])): ?>
    <div style="background: var(--success-bg); color: var(--success); padding: 12px 18px; border-radius: var(--radius-sm); margin-bottom: 18px; font-size: 0.88rem;">
      ✓ Paramètres enregistrés.
    </div>
  <?php endif; ?>

  <form method="post">
    <?= csrf_field() ?>
    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;" class="settings-grid">
      <div class="card">
        <div class="card-head"><h3>Informations de la boutique</h3></div>
        <div class="card-body">
          <div class="form-grid">
            <div class="field full">
              <label>Nom de la boutique</label>
              <input type="text" name="site_name" value="<?= e($settings['site_name'] ?? SITE_NAME) ?>">
            </div>
            <div class="field">
              <label>Email de contact</label>
              <input type="email" name="contact_email" value="<?= e($settings['contact_email'] ?? CONTACT_EMAIL) ?>">
            </div>
            <div class="field">
              <label>Téléphone</label>
              <input type="tel" name="contact_phone" value="<?= e($settings['contact_phone'] ?? CONTACT_PHONE) ?>">
            </div>
            <div class="field full">
              <label>Numéro WhatsApp (sans le +)</label>
              <input type="text" name="whatsapp_number" value="<?= e($settings['whatsapp_number'] ?? WHATSAPP_NUMBER) ?>">
            </div>
          </div>
        </div>
      </div>

      <div class="card">
        <div class="card-head"><h3>Livraison</h3></div>
        <div class="card-body">
          <div class="form-grid">
            <div class="field">
              <label>Frais standard</label>
              <input type="number" name="shipping_standard_fee" value="<?= e($settings['shipping_standard_fee'] ?? '30') ?>">
              <span class="help">en د.م.</span>
            </div>
            <div class="field">
              <label>Seuil livraison gratuite</label>
              <input type="number" name="shipping_free_threshold" value="<?= e($settings['shipping_free_threshold'] ?? '350') ?>">
              <span class="help">en د.م.</span>
            </div>
          </div>
        </div>
      </div>
    </div>

    <button type="submit" class="btn btn-primary" style="margin: 20px 0;">Enregistrer</button>
  </form>

  <div class="card">
    <div class="card-head">
      <h3>Équipe</h3>
      <button class="btn btn-outline btn-sm">+ Inviter un membre</button>
    </div>
    <table class="data-table">
      <thead><tr><th>Membre</th><th>Email</th><th>Rôle</th><th>Dernière connexion</th></tr></thead>
      <tbody>
        <?php foreach ($admins as $a):
          $name = trim(($a['first_name'] ?? '') . ' ' . ($a['last_name'] ?? ''));
        ?>
          <tr>
            <td><div class="cell-customer"><span class="cell-customer-avatar"><?= e(initials($name)) ?></span><strong><?= e($name) ?></strong></div></td>
            <td class="cell-mute"><?= e($a['email']) ?></td>
            <td><span class="badge-status status-success"><?= e(ucfirst(str_replace('_', ' ', $a['role']))) ?></span></td>
            <td class="cell-mute"><?= $a['last_login_at'] ? time_ago($a['last_login_at']) : 'jamais' ?></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<?php require __DIR__ . '/_includes/footer.php'; ?>
