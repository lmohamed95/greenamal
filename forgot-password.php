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
require __DIR__ . '/includes/header.php';
?>

<section class="container" style="padding:48px 16px;max-width:480px;">
  <div class="auth-card">
    <h1 class="auth-title">Mot de passe oublié</h1>
    <p class="auth-sub">Entrez l'email de votre compte. Nous vous enverrons un lien pour choisir un nouveau mot de passe.</p>

    <?php if ($msg): ?><div class="form-success"><?= e($msg) ?></div><?php endif; ?>
    <?php if ($err): ?><div class="form-error"><?= e($err) ?></div><?php endif; ?>

    <form method="post" class="auth-form" novalidate>
      <?= csrf_field() ?>
      <label>Email <input type="email" name="email" required autofocus autocomplete="email"></label>
      <button type="submit" class="btn btn-primary btn-lg btn-block">Envoyer le lien</button>
      <p style="text-align:center;margin-top:12px;"><a href="login" class="auth-link">Retour à la connexion</a></p>
    </form>
  </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
