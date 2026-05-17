<?php
require_once __DIR__ . '/includes/helpers.php';

if (customer_logged_in()) {
    redirect('mon-compte');
}

$next = $_GET['next'] ?? 'account';
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
            $next = (string) ($_POST['next'] ?? 'account');
            // Only allow same-origin paths: a relative file path, or one absolute
            // path starting with a single "/" (not "//", which is protocol-relative).
            // Block schemes (javascript:, http://) and CRLF injection.
            $safe = $next !== ''
                && !preg_match('#[\r\n]#', $next)
                && !preg_match('#^[a-z][a-z0-9+.-]*:#i', $next)
                && !str_starts_with($next, '//');
            redirect($safe ? $next : 'account');
        }
        $err = $res['msg'];
    }
}

$page_title = 'Connexion';
$page_desc  = 'Connectez-vous à votre compte GreenAmal pour suivre vos commandes et profiter de promotions exclusives.';
$noindex    = true;
$body_class = 'gd-2026';
$extra_css  = ['/assets/css/home.css'];
require __DIR__ . '/includes/header.php';
?>

<section class="auth-page">
  <div class="container">
    <div class="auth-card">
      <span class="h-eyebrow">Mon compte</span>
      <h1 class="h-serif">Connexion</h1>
      <p class="auth-sub">Pas encore de compte ? <a href="/inscription">Créer un compte</a></p>

      <?php if ($err): ?><div class="auth-error"><?= e($err) ?></div><?php endif; ?>

      <form method="post" class="auth-form" novalidate>
        <?= csrf_field() ?>
        <input type="hidden" name="next" value="<?= e($next) ?>">
        <div class="gd-field">
          <label>Email</label>
          <input type="email" name="email" required autocomplete="email" autofocus>
        </div>
        <div class="gd-field">
          <label>Mot de passe</label>
          <input type="password" name="password" required autocomplete="current-password">
        </div>
        <div class="auth-row">
          <a href="/mot-de-passe-oublie" class="auth-link">Mot de passe oublié ?</a>
        </div>
        <button type="submit" class="h-btn h-btn-primary h-btn-lg" style="width:100%;">
          Se connecter
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
        </button>
      </form>
    </div>
  </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
