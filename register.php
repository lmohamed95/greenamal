<?php
require_once __DIR__ . '/includes/helpers.php';
require_once __DIR__ . '/includes/mail.php';

if (customer_logged_in()) {
    redirect('mon-compte');
}

$err = null;
$old = ['email' => '', 'first_name' => '', 'last_name' => '', 'phone' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();
    if (!rate_limit('client_register', 5, 60)) {
        $err = 'Trop de tentatives. Réessayez dans une minute.';
    } else {
        $old['email']      = strtolower(trim((string) ($_POST['email'] ?? '')));
        $old['first_name'] = trim((string) ($_POST['first_name'] ?? ''));
        $old['last_name']  = trim((string) ($_POST['last_name'] ?? ''));
        $old['phone']      = trim((string) ($_POST['phone'] ?? ''));
        $pw                = (string) ($_POST['password'] ?? '');
        $pw2               = (string) ($_POST['password2'] ?? '');

        if (!filter_var($old['email'], FILTER_VALIDATE_EMAIL))         $err = 'Adresse email invalide.';
        elseif ($old['first_name'] === '' || $old['last_name'] === '') $err = 'Nom et prénom requis.';
        elseif (strlen($pw) < 8)                                       $err = 'Le mot de passe doit faire au moins 8 caractères.';
        elseif ($pw !== $pw2)                                          $err = 'Les mots de passe ne correspondent pas.';
        else {
            $exists = db_one('SELECT id, password_hash FROM customers WHERE email = ?', [$old['email']]);
            if ($exists && !empty($exists['password_hash'])) {
                $err = 'Un compte existe déjà avec cet email. <a href="connexion">Se connecter</a>';
            } else {
                $hash = password_hash($pw, PASSWORD_BCRYPT);
                if ($exists) {
                    db_query(
                        'UPDATE customers SET password_hash = ?, first_name = ?, last_name = ?, phone = ? WHERE id = ?',
                        [$hash, $old['first_name'], $old['last_name'], $old['phone'], $exists['id']]
                    );
                    $cid = (int) $exists['id'];
                } else {
                    $cid = db_insert('customers', [
                        'email' => $old['email'],
                        'password_hash' => $hash,
                        'first_name' => $old['first_name'],
                        'last_name'  => $old['last_name'],
                        'phone'      => $old['phone'],
                        'segment'    => 'new',
                    ]);
                }
                session_regenerate_id(true);
                $_SESSION['customer_id'] = $cid;
                @mail_welcome($old['email'], $old['first_name']);
                redirect('mon-compte');
            }
        }
    }
}

$page_title = 'Créer un compte';
$page_desc  = 'Créez votre compte GreenAmal pour suivre vos commandes et profiter de promotions exclusives.';
$noindex    = true;
require __DIR__ . '/includes/header.php';
?>

<section class="container" style="padding:48px 16px;max-width:520px;">
  <div class="auth-card">
    <h1 class="auth-title">Créer un compte</h1>
    <p class="auth-sub">Déjà inscrit ? <a href="connexion">Se connecter</a></p>

    <?php if ($err): ?><div class="form-error"><?= $err /* contains safe HTML */ ?></div><?php endif; ?>

    <form method="post" class="auth-form" novalidate>
      <?= csrf_field() ?>
      <div class="form-row">
        <label>Prénom <input type="text" name="first_name" required value="<?= e($old['first_name']) ?>" autocomplete="given-name"></label>
        <label>Nom    <input type="text" name="last_name"  required value="<?= e($old['last_name']) ?>"  autocomplete="family-name"></label>
      </div>
      <label>Email
        <input type="email" name="email" required value="<?= e($old['email']) ?>" autocomplete="email">
      </label>
      <label>Téléphone
        <input type="tel" name="phone" value="<?= e($old['phone']) ?>" placeholder="+212 6 ..." autocomplete="tel">
      </label>
      <div class="form-row">
        <label>Mot de passe       <input type="password" name="password"  required minlength="8" autocomplete="new-password"></label>
        <label>Confirmer            <input type="password" name="password2" required minlength="8" autocomplete="new-password"></label>
      </div>
      <small class="form-hint">8 caractères minimum.</small>
      <p class="form-hint">En créant un compte, vous acceptez nos <a href="cgv">CGV</a> et notre <a href="confidentialite">politique de confidentialité</a>.</p>
      <button type="submit" class="btn btn-primary btn-lg btn-block">Créer mon compte</button>
    </form>
  </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
