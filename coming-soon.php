<?php
require_once __DIR__ . '/includes/helpers.php';

$page_title = 'Bientôt disponible';
$page_desc  = 'GreenAmal · Coopérative Al Amal — produits naturels du Moyen Atlas. La maison ouvre ses portes prochainement.';
$noindex    = true;

// Newsletter signup (reuse the existing newsletter_subscribers table)
$msg = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['email'])) {
    csrf_verify();
    $email = strtolower(trim((string) $_POST['email']));
    if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
        try {
            db_query(
                "INSERT INTO newsletter_subscribers (email, status, source) VALUES (?, 'subscribed', 'coming-soon')
                 ON DUPLICATE KEY UPDATE status = 'subscribed'",
                [$email]
            );
            $msg = ['type' => 'success', 'text' => 'Merci. Vous figurez désormais sur la liste d\'ouverture.'];
        } catch (Throwable $e) {
            $msg = ['type' => 'error', 'text' => 'Une erreur est survenue. Réessayez plus tard.'];
        }
    } else {
        $msg = ['type' => 'error', 'text' => 'Adresse email invalide.'];
    }
}
?><!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title><?= e($page_title) ?> · <?= e(SITE_NAME) ?></title>
<meta name="description" content="<?= e($page_desc) ?>">
<meta name="robots" content="noindex,follow">
<meta name="theme-color" content="#3A5A40">
<link rel="icon" type="image/svg+xml" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><circle cx='50' cy='50' r='48' fill='%233A5A40'/><text x='50' y='66' text-anchor='middle' font-family='serif' font-weight='600' font-size='52' fill='%23FAF6F0'>G</text></svg>">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,500;0,600;1,500&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
<style>
  :root {
    --olive:        #3A5A40;
    --olive-dark:   #2A4030;
    --terracotta:   #C8553D;
    --saffron:      #E0A458;
    --cream:        #FAF6F0;
    --sand:         #F1E9D7;
    --ink:          #1F2421;
    --ink-soft:     #4A4E4C;
    --line:         #E8E1D2;

    --font-display: 'Cormorant Garamond', 'Times New Roman', serif;
    --font-body:    'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
  }

  *, *::before, *::after { box-sizing: border-box; }
  html, body { margin: 0; padding: 0; }

  body {
    font-family: var(--font-body);
    background: var(--cream);
    color: var(--ink);
    min-height: 100vh;
    -webkit-font-smoothing: antialiased;
    display: flex;
    flex-direction: column;
  }

  .cs-wrap {
    flex: 1;
    display: flex;
    flex-direction: column;
    padding: 32px clamp(20px, 4vw, 48px);
    max-width: 720px;
    width: 100%;
    margin: 0 auto;
  }

  /* Header */
  .cs-head {
    display: flex;
    align-items: center;
    gap: 12px;
    margin-bottom: auto;
  }
  .cs-logo-mark {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: var(--olive);
    color: var(--cream);
    display: flex;
    align-items: center;
    justify-content: center;
    font-family: var(--font-display);
    font-weight: 600;
    font-size: 22px;
    letter-spacing: -0.02em;
  }
  .cs-brand {
    font-family: var(--font-display);
    font-weight: 600;
    font-size: 20px;
    letter-spacing: -0.01em;
    color: var(--ink);
  }
  .cs-tag {
    margin-left: auto;
    font-size: 11px;
    letter-spacing: 0.18em;
    text-transform: uppercase;
    color: var(--ink-soft);
  }

  /* Main */
  .cs-main {
    padding: clamp(48px, 10vw, 96px) 0;
    text-align: center;
  }

  .cs-eyebrow {
    display: inline-block;
    font-size: 11px;
    letter-spacing: 0.22em;
    text-transform: uppercase;
    color: var(--olive);
    font-weight: 600;
    margin-bottom: 20px;
  }
  .cs-eyebrow::before,
  .cs-eyebrow::after {
    content: "·";
    margin: 0 10px;
    color: var(--saffron);
  }

  h1.cs-title {
    font-family: var(--font-display);
    font-weight: 500;
    font-size: clamp(2.6rem, 7vw, 4.5rem);
    line-height: 1.05;
    letter-spacing: -0.02em;
    margin: 0 0 24px;
    color: var(--ink);
  }
  h1.cs-title em {
    font-style: italic;
    color: var(--terracotta);
  }

  .cs-sub {
    font-family: var(--font-display);
    font-style: italic;
    font-size: clamp(1.1rem, 2vw, 1.35rem);
    line-height: 1.55;
    color: var(--ink-soft);
    margin: 0 auto 40px;
    max-width: 52ch;
  }

  /* Form */
  .cs-form {
    display: flex;
    flex-direction: column;
    gap: 12px;
    max-width: 440px;
    margin: 0 auto;
  }
  @media (min-width: 540px) {
    .cs-form { flex-direction: row; }
  }

  .cs-input {
    flex: 1;
    padding: 14px 18px;
    border: 1px solid var(--line);
    background: var(--white, #fff);
    border-radius: 999px;
    font-family: var(--font-body);
    font-size: 15px;
    color: var(--ink);
    outline: none;
    transition: border-color .2s, box-shadow .2s;
  }
  .cs-input::placeholder { color: #9aa098; }
  .cs-input:focus {
    border-color: var(--olive);
    box-shadow: 0 0 0 3px rgba(58, 90, 64, 0.12);
  }

  .cs-btn {
    padding: 14px 26px;
    border: none;
    background: var(--olive);
    color: var(--cream);
    border-radius: 999px;
    font-family: var(--font-body);
    font-size: 14px;
    font-weight: 600;
    letter-spacing: 0.02em;
    cursor: pointer;
    transition: background .2s, transform .15s;
  }
  .cs-btn:hover { background: var(--olive-dark); }
  .cs-btn:active { transform: scale(0.98); }

  .cs-msg {
    max-width: 440px;
    margin: 16px auto 0;
    padding: 10px 16px;
    border-radius: 8px;
    font-size: 13.5px;
    line-height: 1.5;
    text-align: left;
  }
  .cs-msg.success {
    background: rgba(58, 90, 64, 0.08);
    color: var(--olive-dark);
    border-left: 3px solid var(--olive);
  }
  .cs-msg.error {
    background: rgba(200, 85, 61, 0.08);
    color: var(--terracotta);
    border-left: 3px solid var(--terracotta);
  }

  .cs-fine {
    margin-top: 18px;
    font-size: 12px;
    color: var(--ink-soft);
  }

  /* Amazigh ornament */
  .cs-ornament {
    display: block;
    margin: 48px auto 0;
    width: 56px;
    height: 56px;
    color: var(--saffron);
    opacity: 0.7;
  }

  /* Footer */
  .cs-foot {
    margin-top: auto;
    padding-top: 32px;
    border-top: 1px solid var(--line);
    display: flex;
    flex-wrap: wrap;
    justify-content: space-between;
    gap: 12px;
    font-size: 12px;
    color: var(--ink-soft);
  }
  .cs-foot a {
    color: var(--ink-soft);
    text-decoration: none;
    border-bottom: 1px solid transparent;
    transition: color .2s, border-color .2s;
  }
  .cs-foot a:hover { color: var(--olive); border-bottom-color: var(--olive); }
  .cs-foot-links { display: flex; gap: 16px; flex-wrap: wrap; }

  @media (prefers-reduced-motion: no-preference) {
    .cs-main > * { animation: fadeUp .6s both ease-out; }
    .cs-eyebrow { animation-delay: .05s; }
    h1.cs-title { animation-delay: .15s; }
    .cs-sub { animation-delay: .25s; }
    .cs-form { animation-delay: .35s; }
    .cs-msg { animation-delay: .4s; }
    .cs-fine { animation-delay: .45s; }
    .cs-ornament { animation-delay: .55s; }
  }
  @keyframes fadeUp {
    from { opacity: 0; transform: translateY(10px); }
    to   { opacity: 1; transform: none; }
  }
</style>
</head>
<body>

<div class="cs-wrap">

  <header class="cs-head">
    <span class="cs-logo-mark">G</span>
    <span class="cs-brand"><?= e(SITE_NAME) ?></span>
    <span class="cs-tag">Azrou · Moyen Atlas</span>
  </header>

  <main class="cs-main">
    <span class="cs-eyebrow">Coopérative Al Amal</span>

    <h1 class="cs-title">La boutique ouvre <em>bientôt.</em></h1>

    <p class="cs-sub">
      Une coopérative féminine du Moyen Atlas, des produits naturels transmis de mère en fille.
      Inscrivez-vous pour être prévenu(e) le jour de l'ouverture.
    </p>

    <?php if ($msg): ?>
      <div class="cs-msg <?= e($msg['type']) ?>"><?= e($msg['text']) ?></div>
    <?php endif; ?>

    <form method="post" class="cs-form" novalidate>
      <?= csrf_field() ?>
      <input class="cs-input" type="email" name="email" placeholder="votre@email.com" required autocomplete="email" aria-label="Adresse email">
      <button class="cs-btn" type="submit">M'inscrire</button>
    </form>

    <p class="cs-fine">Aucun spam · désinscription en un clic.</p>

    <!-- Amazigh ornament (decorative) -->
    <svg class="cs-ornament" viewBox="0 0 56 56" aria-hidden="true">
      <g fill="none" stroke="currentColor" stroke-width="1.2">
        <path d="M28 4 L52 28 L28 52 L4 28 Z"/>
        <path d="M28 14 L42 28 L28 42 L14 28 Z"/>
        <circle cx="28" cy="28" r="2" fill="currentColor"/>
      </g>
    </svg>
  </main>

  <footer class="cs-foot">
    <span>© <?= date('Y') ?> <?= e(SITE_NAME) ?></span>
    <div class="cs-foot-links">
      <a href="mailto:<?= e(CONTACT_EMAIL) ?>">Contact</a>
      <a href="confidentialite">Confidentialité</a>
      <a href="mentions-legales">Mentions légales</a>
    </div>
  </footer>

</div>

</body>
</html>
