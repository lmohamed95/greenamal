<?php
require_once __DIR__ . '/includes/helpers.php';
customer_require_login();

$user = customer_user();
$tab = $_GET['tab'] ?? 'orders';

$err = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['op'] ?? '') === 'profile') {
    csrf_verify();
    $first = trim((string) ($_POST['first_name'] ?? ''));
    $last  = trim((string) ($_POST['last_name'] ?? ''));
    $phone = trim((string) ($_POST['phone'] ?? ''));
    $city  = trim((string) ($_POST['city'] ?? ''));
    $addr  = trim((string) ($_POST['address'] ?? ''));
    $post  = trim((string) ($_POST['postcode'] ?? ''));
    db_query(
        'UPDATE customers SET first_name=?, last_name=?, phone=?, city=?, address=?, postcode=? WHERE id = ?',
        [$first, $last, $phone, $city, $addr, $post, $user['id']]
    );
    flash_set('success', 'Profil mis à jour.');
    redirect('mon-compte?tab=profile');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['op'] ?? '') === 'password') {
    csrf_verify();
    $cur  = (string) ($_POST['current'] ?? '');
    $pw   = (string) ($_POST['password'] ?? '');
    $pw2  = (string) ($_POST['password2'] ?? '');
    $hash = db_value('SELECT password_hash FROM customers WHERE id = ?', [$user['id']]);
    if (!password_verify($cur, $hash))   $err = 'Mot de passe actuel incorrect.';
    elseif (strlen($pw) < 8)             $err = 'Le nouveau mot de passe doit faire au moins 8 caractères.';
    elseif ($pw !== $pw2)                $err = 'Les mots de passe ne correspondent pas.';
    else {
        db_query('UPDATE customers SET password_hash = ? WHERE id = ?', [password_hash($pw, PASSWORD_BCRYPT), $user['id']]);
        flash_set('success', 'Mot de passe modifié.');
        redirect('mon-compte?tab=password');
    }
}

$orders = db_all(
    'SELECT id, order_number, total, status, created_at FROM orders WHERE customer_id = ? ORDER BY created_at DESC LIMIT 50',
    [$user['id']]
);

$flash = flash_pop();

$page_title = 'Mon compte';
$noindex    = true;
$body_class = 'gd-2026';
$extra_css  = ['/assets/css/home.css'];
require __DIR__ . '/includes/header.php';
?>

<section class="account-page">
  <div class="container">
    <div class="account-head">
      <div>
        <span class="h-eyebrow">Mon compte</span>
        <h1 class="h-serif">Bonjour, <em><?= e($user['first_name'] ?: 'vous') ?></em>.</h1>
        <p class="muted" style="margin:6px 0 0; font-size: 14px;"><?= e($user['email']) ?></p>
      </div>
      <form method="post" action="/deconnexion" style="margin: 0;">
        <?= csrf_field() ?>
        <button type="submit" class="h-btn h-btn-ghost">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 21H5a2 2 0 01-2-2V5a2 2 0 012-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
          Se déconnecter
        </button>
      </form>
    </div>

    <?php if ($flash): ?>
      <div class="auth-<?= $flash['type'] === 'success' ? 'success' : 'error' ?>" style="margin-bottom:20px;"><?= e($flash['msg']) ?></div>
    <?php endif; ?>
    <?php if ($err): ?><div class="auth-error" style="margin-bottom:20px;"><?= e($err) ?></div><?php endif; ?>

    <nav class="account-tabs">
      <a href="?tab=orders"   class="<?= $tab === 'orders'   ? 'active' : '' ?>">Mes commandes</a>
      <a href="?tab=profile"  class="<?= $tab === 'profile'  ? 'active' : '' ?>">Profil &amp; adresse</a>
      <a href="?tab=password" class="<?= $tab === 'password' ? 'active' : '' ?>">Mot de passe</a>
    </nav>

    <?php if ($tab === 'orders'): ?>
      <?php if ($orders): ?>
        <div class="account-card account-card-table">
          <table class="account-table">
            <thead><tr><th>Commande</th><th>Date</th><th>Total</th><th>Statut</th><th></th></tr></thead>
            <tbody>
            <?php foreach ($orders as $o):
              [$lbl, $cls] = order_status_label($o['status']);
            ?>
              <tr>
                <td><strong><?= e($o['order_number']) ?></strong></td>
                <td class="cell-mute"><?= date('j M Y', strtotime($o['created_at'])) ?></td>
                <td><?= e(price((float) $o['total'])) ?></td>
                <td><span class="status-pill status-<?= e($cls) ?>"><?= e($lbl) ?></span></td>
                <td><a href="/confirmation-commande?order=<?= e(urlencode($o['order_number'])) ?>" class="auth-link">Détails →</a></td>
              </tr>
            <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      <?php else: ?>
        <div class="cart-empty" style="background:var(--paper);">
          <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.4"><path d="M6 2L3 6v14a2 2 0 002 2h14a2 2 0 002-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 01-8 0"/></svg>
          <h2 class="h-serif">Aucune commande pour le moment</h2>
          <p>Découvrez nos produits naturels du Maroc.</p>
          <a href="/boutique" class="h-btn h-btn-primary h-btn-lg">
            Voir la boutique
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
          </a>
        </div>
      <?php endif; ?>

    <?php elseif ($tab === 'profile'): ?>
      <form method="post" class="account-card">
        <?= csrf_field() ?>
        <input type="hidden" name="op" value="profile">
        <div class="gd-form-grid">
          <div class="gd-field"><label>Prénom <span class="req">*</span></label><input type="text" name="first_name" value="<?= e($user['first_name']) ?>" required></div>
          <div class="gd-field"><label>Nom <span class="req">*</span></label><input type="text" name="last_name" value="<?= e($user['last_name']) ?>" required></div>
          <div class="gd-field gd-field-full"><label>Téléphone</label><input type="tel" name="phone" value="<?= e($user['phone']) ?>" placeholder="+212 6 …"></div>
          <div class="gd-field gd-field-full"><label>Adresse</label><input type="text" name="address" value="<?= e($user['address'] ?? '') ?>"></div>
          <div class="gd-field"><label>Ville</label><input type="text" name="city" value="<?= e($user['city'] ?? '') ?>"></div>
          <div class="gd-field"><label>Code postal</label><input type="text" name="postcode" value="<?= e($user['postcode'] ?? '') ?>"></div>
        </div>
        <button type="submit" class="h-btn h-btn-primary h-btn-lg" style="margin-top:18px;">
          Enregistrer
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
        </button>
      </form>

    <?php else: ?>
      <form method="post" class="account-card" style="max-width:520px;">
        <?= csrf_field() ?>
        <input type="hidden" name="op" value="password">
        <div class="gd-form-grid">
          <div class="gd-field gd-field-full"><label>Mot de passe actuel</label><input type="password" name="current" required autocomplete="current-password"></div>
          <div class="gd-field"><label>Nouveau mot de passe</label><input type="password" name="password" required minlength="8" autocomplete="new-password"></div>
          <div class="gd-field"><label>Confirmer</label><input type="password" name="password2" required minlength="8" autocomplete="new-password"></div>
        </div>
        <p class="form-hint" style="margin-top:6px;">8 caractères minimum.</p>
        <button type="submit" class="h-btn h-btn-primary h-btn-lg" style="margin-top:14px;">
          Modifier le mot de passe
        </button>
      </form>
    <?php endif; ?>
  </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
