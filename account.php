<?php
require_once __DIR__ . '/includes/helpers.php';
customer_require_login();

$user = customer_user();
$tab = $_GET['tab'] ?? 'orders';

$err = null;
$msg_ok = null;

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
    redirect('account.php?tab=profile');
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
        redirect('account.php?tab=password');
    }
}

$orders = db_all(
    'SELECT id, order_number, total, status, created_at FROM orders WHERE customer_id = ? ORDER BY created_at DESC LIMIT 50',
    [$user['id']]
);

$flash = flash_pop();

$page_title = 'Mon compte';
$noindex    = true;
require __DIR__ . '/includes/header.php';
?>

<section class="container" style="padding:32px 16px;max-width:920px;">
  <div class="account-head">
    <div>
      <h1 style="margin:0;">Bonjour, <?= e($user['first_name'] ?: 'vous') ?> 👋</h1>
      <p style="color:var(--ink-soft);margin:4px 0 0;"><?= e($user['email']) ?></p>
    </div>
    <a href="logout.php" class="btn btn-ghost">Se déconnecter</a>
  </div>

  <?php if ($flash): ?><div class="form-<?= e($flash['type']) ?>"><?= e($flash['msg']) ?></div><?php endif; ?>
  <?php if ($err): ?><div class="form-error"><?= e($err) ?></div><?php endif; ?>

  <nav class="account-tabs">
    <a href="?tab=orders"  class="<?= $tab === 'orders'   ? 'active' : '' ?>">Mes commandes</a>
    <a href="?tab=profile" class="<?= $tab === 'profile'  ? 'active' : '' ?>">Profil & adresse</a>
    <a href="?tab=password" class="<?= $tab === 'password' ? 'active' : '' ?>">Mot de passe</a>
  </nav>

  <?php if ($tab === 'orders'): ?>
    <?php if ($orders): ?>
      <div class="card" style="padding:0;overflow:hidden;">
        <table class="data-table">
          <thead><tr><th>Commande</th><th>Date</th><th>Total</th><th>Statut</th><th></th></tr></thead>
          <tbody>
          <?php foreach ($orders as $o):
            [$lbl, $cls] = order_status_label($o['status']);
          ?>
            <tr>
              <td><strong><?= e($o['order_number']) ?></strong></td>
              <td class="cell-mute"><?= date('j M Y', strtotime($o['created_at'])) ?></td>
              <td><?= e(price((float) $o['total'])) ?></td>
              <td><span class="badge-status <?= e($cls) ?>"><?= e($lbl) ?></span></td>
              <td><a href="order-confirmation.php?n=<?= e($o['order_number']) ?>" class="auth-link">Détails →</a></td>
            </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php else: ?>
      <div class="empty-state">
        <h2>Aucune commande pour le moment</h2>
        <p>Découvrez nos produits naturels du Maroc.</p>
        <a href="shop.php" class="btn btn-primary">Voir la boutique</a>
      </div>
    <?php endif; ?>
  <?php elseif ($tab === 'profile'): ?>
    <form method="post" class="auth-form card" style="max-width:560px;padding:24px;">
      <?= csrf_field() ?>
      <input type="hidden" name="op" value="profile">
      <div class="form-row">
        <label>Prénom <input type="text" name="first_name" value="<?= e($user['first_name']) ?>" required></label>
        <label>Nom    <input type="text" name="last_name"  value="<?= e($user['last_name']) ?>"  required></label>
      </div>
      <label>Téléphone <input type="tel" name="phone" value="<?= e($user['phone']) ?>"></label>
      <label>Adresse   <input type="text" name="address" value="<?= e($user['address'] ?? '') ?>"></label>
      <div class="form-row">
        <label>Ville       <input type="text" name="city"     value="<?= e($user['city'] ?? '') ?>"></label>
        <label>Code postal <input type="text" name="postcode" value="<?= e($user['postcode'] ?? '') ?>"></label>
      </div>
      <button type="submit" class="btn btn-primary">Enregistrer</button>
    </form>
  <?php else: ?>
    <form method="post" class="auth-form card" style="max-width:480px;padding:24px;">
      <?= csrf_field() ?>
      <input type="hidden" name="op" value="password">
      <label>Mot de passe actuel <input type="password" name="current"  required autocomplete="current-password"></label>
      <label>Nouveau mot de passe <input type="password" name="password"  required minlength="8" autocomplete="new-password"></label>
      <label>Confirmer            <input type="password" name="password2" required minlength="8" autocomplete="new-password"></label>
      <button type="submit" class="btn btn-primary">Modifier</button>
    </form>
  <?php endif; ?>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
