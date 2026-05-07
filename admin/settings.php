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

  <!-- Display & demo toggles (separate POST, not part of the main settings form) -->
  <div class="card" style="margin-bottom: 18px;">
    <div class="card-head"><h3>Affichage & démo</h3></div>
    <div class="card-body" style="display:flex; flex-direction:column; gap:14px;">
      <div class="settings-toggle-row">
        <div>
          <strong>Mode « Bientôt disponible »</strong>
          <div class="cell-mute">Masque la boutique aux visiteurs et affiche un écran d'attente. Vous gardez l'accès admin.</div>
        </div>
        <form method="post" action="toggle-coming-soon.php" style="display:inline; margin:0;"
              onsubmit="return confirm('<?= ($settings['coming_soon_mode'] ?? '0') === '1' ? "Réactiver la boutique pour tous les visiteurs ?" : "Activer le mode bientôt disponible ?" ?>');">
          <?= csrf_field() ?>
          <input type="hidden" name="back" value="settings.php">
          <button type="submit" class="cs-toggle-btn<?= ($settings['coming_soon_mode'] ?? '0') === '1' ? ' is-on' : '' ?>">
            <span class="cs-dot"></span>
            <span class="cs-label"><?= ($settings['coming_soon_mode'] ?? '0') === '1' ? 'Activé' : 'Désactivé' ?></span>
          </button>
        </form>
      </div>

      <div class="settings-toggle-row">
        <div>
          <strong>Mode démo (tableau de bord)</strong>
          <div class="cell-mute">Affiche des données fictives sur le tableau de bord pour que vous puissiez voir à quoi il ressemble une fois alimenté. La base de données reste vide.</div>
        </div>
        <form method="post" action="toggle-demo.php" style="display:inline; margin:0;">
          <?= csrf_field() ?>
          <input type="hidden" name="back" value="settings.php">
          <button type="submit" class="cs-toggle-btn<?= ($settings['dashboard_demo_mode'] ?? '0') === '1' ? ' is-on' : '' ?>">
            <span class="cs-dot"></span>
            <span class="cs-label"><?= ($settings['dashboard_demo_mode'] ?? '0') === '1' ? 'Activé' : 'Désactivé' ?></span>
          </button>
        </form>
      </div>
    </div>
  </div>

  <form method="post">
    <?= csrf_field() ?>

    <div class="card" style="margin-bottom: 16px;">
      <div class="card-head">
        <h3>Image du hero (page d'accueil)</h3>
        <span class="head-meta">visible en haut de la page d'accueil</span>
      </div>
      <div class="card-body">
        <div class="upload-widget"
             data-target="hero"
             data-name="hero_image_url"
             data-current="<?= e($settings['hero_image_url'] ?? '/assets/img/categories/huiles-essentielles.jpg') ?>"></div>
        <p class="help" style="margin-top: 10px;">Format conseillé : JPG ou WebP, ratio portrait 4:5, au moins 1200 × 1500 px. Maximum 5 Mo.</p>
      </div>
    </div>

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
              <span class="help">en DH</span>
            </div>
            <div class="field">
              <label>Seuil livraison gratuite</label>
              <input type="number" name="shipping_free_threshold" value="<?= e($settings['shipping_free_threshold'] ?? '350') ?>">
              <span class="help">en DH</span>
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
