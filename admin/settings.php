<?php
require_once __DIR__ . '/../includes/auth.php';
admin_require_login();

$page_title = 'Paramètres';
$current = 'settings';

$pwd_error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();

    // Handle admin password change (dedicated form)
    if (($_POST['_action'] ?? '') === 'change_password') {
        if (!rate_limit('admin_change_password', 5, 300)) {
            $pwd_error = 'Trop de tentatives. Réessayez dans quelques minutes.';
        } else {
            $current  = (string) ($_POST['current_password'] ?? '');
            $new      = (string) ($_POST['new_password'] ?? '');
            $confirm  = (string) ($_POST['confirm_password'] ?? '');
            $admin    = admin_user();
            $row      = $admin ? db_one('SELECT password_hash FROM admin_users WHERE id = ?', [$admin['id']]) : null;

            if (!$row || !password_verify($current, $row['password_hash'])) {
                $pwd_error = 'Mot de passe actuel incorrect.';
            } elseif (strlen($new) < 8) {
                $pwd_error = 'Le nouveau mot de passe doit comporter au moins 8 caractères.';
            } elseif ($new !== $confirm) {
                $pwd_error = 'La confirmation ne correspond pas au nouveau mot de passe.';
            } elseif ($new === $current) {
                $pwd_error = 'Le nouveau mot de passe doit être différent de l\'actuel.';
            } else {
                db_query(
                    'UPDATE admin_users SET password_hash = ? WHERE id = ?',
                    [password_hash($new, PASSWORD_DEFAULT), $admin['id']]
                );
                session_regenerate_id(true);
                redirect('settings.php?pwd=1');
            }
        }
    } else {
        // Generic settings save
        foreach ($_POST as $k => $v) {
            if ($k === '_csrf' || $k === '_action') continue;
            if (!is_string($v)) continue; // skip arrays
            db_query(
                "INSERT INTO settings (setting_key, setting_value) VALUES (?, ?)
                 ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)",
                [$k, $v]
            );
        }
        redirect('settings.php?saved=1');
    }
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

  <?php if (!empty($_GET['pwd'])): ?>
    <div style="background: var(--success-bg); color: var(--success); padding: 12px 18px; border-radius: var(--radius-sm); margin-bottom: 18px; font-size: 0.88rem;">
      ✓ Mot de passe modifié avec succès.
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

      <div class="settings-toggle-row">
        <div>
          <strong>Commande via WhatsApp</strong>
          <div class="cell-mute">Affiche le bouton flottant WhatsApp en bas à droite du site et le CTA « Commander rapidement via WhatsApp » sur les fiches produits. Le numéro utilisé est celui du téléphone de contact.</div>
        </div>
        <form method="post" action="toggle-wa-order.php" style="display:inline; margin:0;">
          <?= csrf_field() ?>
          <input type="hidden" name="back" value="settings.php">
          <button type="submit" class="cs-toggle-btn<?= ($settings['whatsapp_order_enabled'] ?? '0') === '1' ? ' is-on' : '' ?>">
            <span class="cs-dot"></span>
            <span class="cs-label"><?= ($settings['whatsapp_order_enabled'] ?? '0') === '1' ? 'Activé' : 'Désactivé' ?></span>
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
              <label>Numéro WhatsApp</label>
              <input type="text" value="<?= e(wa_number()) ?>" readonly disabled style="opacity:0.6;">
              <span class="help">Synchronisé automatiquement avec le téléphone ci-dessus (chiffres uniquement, pour les liens wa.me).</span>
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

  <div class="card" style="margin-bottom: 16px;">
    <div class="card-head">
      <h3>Sécurité · mot de passe administrateur</h3>
      <span class="head-meta"><?= e(admin_user()['email'] ?? '') ?></span>
    </div>
    <div class="card-body">
      <?php if ($pwd_error): ?>
        <div style="background: var(--danger-bg); color: var(--danger); padding: 10px 14px; border-radius: var(--radius-sm); margin-bottom: 14px; font-size: 0.88rem;">
          <?= e($pwd_error) ?>
        </div>
      <?php endif; ?>
      <form method="post" autocomplete="off">
        <?= csrf_field() ?>
        <input type="hidden" name="_action" value="change_password">
        <div class="form-grid" style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 14px;">
          <div class="field">
            <label>Mot de passe actuel</label>
            <input type="password" name="current_password" autocomplete="current-password" required>
          </div>
          <div class="field">
            <label>Nouveau mot de passe</label>
            <input type="password" name="new_password" autocomplete="new-password" minlength="8" required>
            <span class="help">8 caractères minimum</span>
          </div>
          <div class="field">
            <label>Confirmer le mot de passe</label>
            <input type="password" name="confirm_password" autocomplete="new-password" minlength="8" required>
          </div>
        </div>
        <button type="submit" class="btn btn-primary" style="margin-top: 14px;">Modifier le mot de passe</button>
      </form>
    </div>
  </div>

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
