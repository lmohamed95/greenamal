<?php
http_response_code(500);
require_once __DIR__ . '/includes/helpers.php';
$page_title = 'Erreur serveur';
$noindex    = true;
require __DIR__ . '/includes/header.php';
?>

<section class="container error-page" style="padding:96px 16px;text-align:center;max-width:640px;">
  <div class="error-code">500</div>
  <h1>Une erreur est survenue</h1>
  <p style="color:var(--ink-soft);margin:12px 0 28px;">Notre équipe a été notifiée. Si le problème persiste, n'hésitez pas à nous contacter.</p>
  <div class="error-actions">
    <a href="/" class="btn btn-primary">Retour à l'accueil</a>
    <a href="/contact.php" class="btn btn-ghost">Nous contacter</a>
  </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
