<?php
require_once __DIR__ . '/includes/helpers.php';

if (customer_logged_in()) {
    redirect('account.php');
}

$next = $_GET['next'] ?? 'account.php';
$err = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();
    if (!rate_limit('client_login', 5, 60)) {
        $err = 'Trop de tentatives. Réessayez dans une minute.';
    } else {
        $email = trim((string) ($_POST['email'] ?? ''));
        $pw    = (string) ($_POST['password'] ?? '');
        $res = customer_login($email, $pw);
        if ($res['ok']) {
            $next = $_POST['next'] ?? 'account.php';
            redirect(filter_var($next, FILTER_VALIDATE_URL) === false && str_starts_with($next, '/') === false ? $next : 'account.php');
        }
        $err = $res['msg'];
    }
}

$page_title = 'Connexion';
$page_desc  = 'Connectez-vous à votre compte GreenAmal pour suivre vos commandes et profiter de promotions exclusives.';
$noindex    = true;
require __DIR__ . '/includes/header.php';
?>

<section class="container" style="padding:48px 16px;max-width:480px;">
  <div class="auth-card">
    <h1 class="auth-title">Connexion</h1>
    <p class="auth-sub">Pas encore de compte ? <a href="register.php">Créer un compte</a></p>

    <?php if ($err): ?><div class="form-error"><?= e($err) ?></div><?php endif; ?>

    <form method="post" class="auth-form" novalidate>
      <?= csrf_field() ?>
      <input type="hidden" name="next" value="<?= e($next) ?>">
      <label>Email
        <input type="email" name="email" required autocomplete="email" autofocus>
      </label>
      <label>Mot de passe
        <input type="password" name="password" required autocomplete="current-password">
      </label>
      <div class="auth-row">
        <a href="forgot-password.php" class="auth-link">Mot de passe oublié ?</a>
      </div>
      <button type="submit" class="btn btn-primary btn-lg btn-block">Se connecter</button>
    </form>
  </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
