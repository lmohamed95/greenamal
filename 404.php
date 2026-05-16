<?php
http_response_code(404);
require_once __DIR__ . '/includes/helpers.php';
$page_title = 'Page introuvable';
$noindex    = true;
require __DIR__ . '/includes/header.php';
?>

<section class="container error-page" style="padding:96px 16px;text-align:center;max-width:640px;">
  <div class="error-code">404</div>
  <h1>Cette page n'existe pas</h1>
  <p style="color:var(--ink-soft);margin:12px 0 28px;">Le lien est peut-être cassé ou la page a été déplacée. Voici quelques pistes :</p>
  <div class="error-actions">
    <a href="/" class="btn btn-primary">Accueil</a>
    <a href="/boutique" class="btn btn-ghost">Voir la boutique</a>
    <a href="/contact" class="btn btn-ghost">Nous contacter</a>
  </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
