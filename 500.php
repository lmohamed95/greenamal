<?php
http_response_code(500);
require_once __DIR__ . '/includes/helpers.php';
$page_title = 'Erreur serveur';
$noindex    = true;
$body_class = 'gd-2026';
$extra_css  = ['/assets/css/home.css'];
require __DIR__ . '/includes/header.php';
?>

<section class="static-page">
  <div class="container" style="max-width: 640px; text-align: center; padding-top: clamp(40px, 8vw, 96px);">
    <div class="error-code-h">500</div>
    <h1 class="h-serif" style="font-family:var(--font-display-h); font-weight:400; font-size: clamp(1.8rem, 4vw, 2.4rem); letter-spacing: -0.02em; color: var(--ink-h); margin: 12px 0 12px;">Une erreur est <em>survenue</em>.</h1>
    <p class="muted" style="margin: 0 auto 28px; max-width: 50ch;">Notre équipe a été notifiée. Si le problème persiste, n'hésitez pas à nous contacter.</p>
    <div style="display: inline-flex; gap: 10px; flex-wrap: wrap; justify-content: center;">
      <a href="/" class="h-btn h-btn-primary h-btn-lg">
        Retour à l'accueil
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
      </a>
      <a href="/contact" class="h-btn h-btn-ghost h-btn-lg">Nous contacter</a>
    </div>
  </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
