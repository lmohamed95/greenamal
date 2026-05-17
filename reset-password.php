<?php
require_once __DIR__ . '/includes/helpers.php';

$token = (string) ($_GET['token'] ?? $_POST['token'] ?? '');
$err = null;
$ok  = false;

$row = $token ? db_one(
    'SELECT id, email FROM customers WHERE reset_token = ? AND reset_token_expires_at > NOW() LIMIT 1',
    [$token]
) : null;

if (!$row) {
    $err = 'Ce lien est invalide ou a expiré. <a href="mot-de-passe-oublie">Demander un nouveau lien</a>';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $row) {
    csrf_verify();
    $pw  = (string) ($_POST['password']  ?? '');
    $pw2 = (string) ($_POST['password2'] ?? '');
    if (strlen($pw) < 8)         $err = 'Le mot de passe doit faire au moins 8 caractères.';
    elseif ($pw !== $pw2)        $err = 'Les mots de passe ne correspondent pas.';
    else {
        $hash = password_hash($pw, PASSWORD_BCRYPT);
        db_query(
            'UPDATE customers SET password_hash = ?, reset_token = NULL, reset_token_expires_at = NULL WHERE id = ?',
            [$hash, $row['id']]
        );
        session_regenerate_id(true);
        $_SESSION['customer_id'] = (int) $row['id'];
        $ok = true;
    }
}

$page_title = 'Nouveau mot de passe';
$noindex    = true;
$body_class = 'gd-2026';
$extra_css  = ['/assets/css/home.css'];
require __DIR__ . '/includes/header.php';
?>

<section class="auth-page">
  <div class="container">
    <div class="auth-card">
      <span class="h-eyebrow">Mon compte</span>
      <h1 class="h-serif">Nouveau mot de <em>passe</em>.</h1>

      <?php if ($ok): ?>
        <div class="auth-success">Mot de passe mis à jour. <a href="/mon-compte">Aller à mon compte →</a></div>
      <?php elseif ($err): ?>
        <div class="auth-error"><?= $err /* contains safe HTML */ ?></div>
      <?php endif; ?>

      <?php if ($row && !$ok): ?>
        <p class="auth-sub">Choisissez un nouveau mot de passe pour <strong><?= e($row['email']) ?></strong>.</p>
        <form method="post" class="auth-form" novalidate>
          <?= csrf_field() ?>
          <input type="hidden" name="token" value="<?= e($token) ?>">
          <div class="gd-field">
            <label>Nouveau mot de passe</label>
            <input type="password" name="password" required minlength="8" autocomplete="new-password" autofocus>
          </div>
          <div class="gd-field">
            <label>Confirmer</label>
            <input type="password" name="password2" required minlength="8" autocomplete="new-password">
          </div>
          <small class="form-hint">8 caractères minimum.</small>
          <button type="submit" class="h-btn h-btn-primary h-btn-lg" style="width:100%;">
            Enregistrer
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
          </button>
        </form>
      <?php endif; ?>
    </div>
  </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
