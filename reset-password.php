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
    $err = 'Ce lien est invalide ou a expiré. <a href="forgot-password">Demander un nouveau lien</a>';
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
require __DIR__ . '/includes/header.php';
?>

<section class="container" style="padding:48px 16px;max-width:480px;">
  <div class="auth-card">
    <h1 class="auth-title">Nouveau mot de passe</h1>

    <?php if ($ok): ?>
      <div class="form-success">Mot de passe mis à jour. <a href="account">Aller à mon compte →</a></div>
    <?php elseif ($err): ?>
      <div class="form-error"><?= $err /* contains safe HTML */ ?></div>
    <?php endif; ?>

    <?php if ($row && !$ok): ?>
      <p class="auth-sub">Choisissez un nouveau mot de passe pour <strong><?= e($row['email']) ?></strong>.</p>
      <form method="post" class="auth-form" novalidate>
        <?= csrf_field() ?>
        <input type="hidden" name="token" value="<?= e($token) ?>">
        <label>Nouveau mot de passe <input type="password" name="password"  required minlength="8" autocomplete="new-password" autofocus></label>
        <label>Confirmer              <input type="password" name="password2" required minlength="8" autocomplete="new-password"></label>
        <small class="form-hint">8 caractères minimum.</small>
        <button type="submit" class="btn btn-primary btn-lg btn-block">Enregistrer</button>
      </form>
    <?php endif; ?>
  </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
