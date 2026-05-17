<?php
require_once __DIR__ . '/includes/helpers.php';
require_once __DIR__ . '/includes/mail.php';

$msg = null;
$err = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();
    if (!rate_limit('forgot_pw', 3, 600)) {
        $err = 'Trop de tentatives. Réessayez plus tard.';
    } else {
        $email = strtolower(trim((string) ($_POST['email'] ?? '')));
        if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $row = db_one('SELECT id FROM customers WHERE email = ?', [$email]);
            if ($row) {
                $token = bin2hex(random_bytes(32));
                db_query(
                    'UPDATE customers SET reset_token = ?, reset_token_expires_at = DATE_ADD(NOW(), INTERVAL 1 HOUR) WHERE id = ?',
                    [$token, $row['id']]
                );
                @mail_password_reset($email, $token);
            }
        }
        // Same response whether the email existed or not (anti-enumeration)
        $msg = 'Si un compte existe avec cet email, un lien de réinitialisation vient d\'être envoyé.';
    }
}

$page_title = 'Mot de passe oublié';
$noindex    = true;
$body_class = 'gd-2026';
$extra_css  = ['/assets/css/home.css'];
require __DIR__ . '/includes/header.php';
?>

<section class="auth-page">
  <div class="container">
    <div class="auth-card">
      <span class="h-eyebrow">Mon compte</span>
      <h1 class="h-serif">Mot de passe <em>oublié</em>.</h1>
      <p class="auth-sub">Entrez l'email de votre compte. Nous vous enverrons un lien pour choisir un nouveau mot de passe.</p>

      <?php if ($msg): ?><div class="auth-success"><?= e($msg) ?></div><?php endif; ?>
      <?php if ($err): ?><div class="auth-error"><?= e($err) ?></div><?php endif; ?>

      <form method="post" class="auth-form" novalidate>
        <?= csrf_field() ?>
        <div class="gd-field">
          <label>Email</label>
          <input type="email" name="email" required autofocus autocomplete="email">
        </div>
        <button type="submit" class="h-btn h-btn-primary h-btn-lg" style="width:100%;">
          Envoyer le lien
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
        </button>
        <p style="text-align:center;margin-top:8px;"><a href="/connexion" class="auth-link">Retour à la connexion</a></p>
      </form>
    </div>
  </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
