<?php
require_once __DIR__ . '/../includes/auth.php';

if (admin_logged_in()) {
    redirect('index.php');
}

$error = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();
    if (!rate_limit('admin_login', 5, 300)) {
        $error = 'Trop de tentatives. Réessayez dans quelques minutes.';
    } else {
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        if (admin_login($email, $password)) {
            redirect('index.php');
        } else {
            $error = 'Email ou mot de passe incorrect.';
        }
    }
}
?><!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Connexion — GreenAmal Admin</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@500;600&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="assets/css/admin.css">
</head>
<body>

<div class="auth-screen">
  <div class="auth-side">
    <div class="auth-brand">
      <span class="auth-brand-mark">G</span>
      <div>GreenAmal<div style="font-size: 0.7rem; letter-spacing: 0.18em; color: rgba(250,246,240,0.6); text-transform: uppercase; margin-top: 2px;">Tableau de bord</div></div>
    </div>
    <div class="auth-quote">
      <h2>"Une coopérative, des mains, un héritage."</h2>
      <p>Bienvenue dans le tableau de bord GreenAmal. Gérez votre catalogue, suivez vos commandes, animez votre boutique.</p>
    </div>
    <div class="auth-foot">© <?= date('Y') ?> GreenAmal · Coopérative Al Amal, Azrou — Maroc</div>
  </div>

  <div class="auth-form">
    <div class="auth-form-inner">
      <h1>Connexion</h1>
      <p>Connectez-vous pour accéder au tableau de bord.</p>

      <?php if ($error): ?>
        <div style="background: var(--danger-bg); color: var(--danger); padding: 10px 14px; border-radius: var(--radius-sm); margin-bottom: 18px; font-size: 0.88rem;">
          <?= e($error) ?>
        </div>
      <?php endif; ?>

      <form method="post">
        <?= csrf_field() ?>
        <div class="field">
          <label>Email <span class="required">*</span></label>
          <input type="email" name="email" placeholder="vous@greenamal.com" required value="<?= e($_POST['email'] ?? '') ?>">
        </div>
        <div class="field">
          <label>Mot de passe <span class="required">*</span></label>
          <input type="password" name="password" placeholder="••••••••" required>
        </div>

        <div class="row-between">
          <label class="checkbox-line"><input type="checkbox"> Se souvenir de moi</label>
          <a href="#">Mot de passe oublié ?</a>
        </div>

        <button type="submit" class="btn btn-primary btn-lg">
          Se connecter
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
        </button>
      </form>

      <div style="margin-top: 24px; padding: 14px; background: var(--surface-2); border-radius: var(--radius-sm); font-size: 0.82rem; color: var(--ink-soft);">
        <strong style="color: var(--ink);">Compte de démo :</strong><br>
        Email : <code>admin@greenamal.com</code><br>
        Mot de passe : <code>admin123</code>
      </div>

      <p style="text-align: center; margin-top: 24px; font-size: 0.82rem; color: var(--ink-mute);">
        Besoin d'aide ? <a href="#" style="color: var(--olive); font-weight: 500;">Contactez le support</a>
      </p>
    </div>
  </div>
</div>

<script src="assets/js/admin.js"></script>
</body>
</html>
