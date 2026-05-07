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

// Pseudo-random edition number (stable per visitor session) for the "limited edition" feel
$edition_no = str_pad((string) (crc32(session_id() ?: (string) time()) % 999), 3, '0', STR_PAD_LEFT);
?><!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title><?= e($page_title) ?> · <?= e(SITE_NAME) ?></title>
<meta name="description" content="<?= e($page_desc) ?>">
<meta name="robots" content="noindex,follow">
<meta name="theme-color" content="#1A1814">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght,SOFT@9..144,300..700,0..100&family=Geist:wght@300;400;500;600&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
<style>
  /* ─── Atlas Atelier · Coming Soon ──────────────────────────────────── */
  :root {
    --ink:        #1A1814;
    --ink-2:      #2A2620;
    --paper:      #F0E6D0;
    --paper-2:    #E6D9BA;
    --paper-3:    #DCC99E;
    --clay:       #B05A36;
    --clay-deep:  #8A3F22;
    --saffron:    #D89A3A;
    --sage:       #5C6A3F;
    --indigo:     #2A3F66;
    --rule:       rgba(26,24,20,.16);
    --rule-soft:  rgba(26,24,20,.08);

    --font-display: 'Fraunces', 'Times New Roman', serif;
    --font-body:    'Geist', ui-sans-serif, system-ui, -apple-system, sans-serif;
    --font-mono:    'JetBrains Mono', ui-monospace, SFMono-Regular, monospace;
  }

  *, *::before, *::after { box-sizing: border-box; }
  html, body { margin: 0; padding: 0; }
  html { scroll-behavior: smooth; }

  body {
    font-family: var(--font-body);
    background: var(--paper);
    color: var(--ink);
    min-height: 100vh;
    overflow-x: hidden;
    font-feature-settings: "ss01", "ss02";
    -webkit-font-smoothing: antialiased;
  }

  /* Paper grain (SVG noise) */
  body::before {
    content: "";
    position: fixed; inset: 0;
    pointer-events: none;
    z-index: 1;
    opacity: .55;
    mix-blend-mode: multiply;
    background-image: url("data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' width='240' height='240'><filter id='n'><feTurbulence type='fractalNoise' baseFrequency='0.92' numOctaves='2' seed='7'/><feColorMatrix values='0 0 0 0 0.10  0 0 0 0 0.09  0 0 0 0 0.08  0 0 0 0.10 0'/></filter><rect width='100%25' height='100%25' filter='url(%23n)'/></svg>");
  }

  /* Vignette */
  body::after {
    content: "";
    position: fixed; inset: 0;
    pointer-events: none;
    z-index: 2;
    background:
      radial-gradient(ellipse at 50% 0%, transparent 40%, rgba(26,24,20,.10) 100%),
      radial-gradient(ellipse at 50% 100%, transparent 40%, rgba(26,24,20,.18) 100%);
  }

  .stage {
    position: relative;
    z-index: 5;
    min-height: 100vh;
    display: grid;
    grid-template-rows: auto 1fr auto;
  }

  /* ─── Masthead ─────────────────────────────────────────────────────── */
  .masthead {
    display: grid;
    grid-template-columns: 1fr auto 1fr;
    align-items: center;
    gap: 24px;
    padding: 22px clamp(20px, 4vw, 56px);
    border-bottom: 1px solid var(--rule);
    font-family: var(--font-mono);
    font-size: 11px;
    letter-spacing: .14em;
    text-transform: uppercase;
    color: var(--ink-2);
    animation: fade-down .9s .05s both ease-out;
  }
  .mast-l { display: flex; gap: 14px; align-items: center; }
  .mast-r { display: flex; gap: 14px; align-items: center; justify-content: flex-end; }
  .mast-c {
    font-family: var(--font-display);
    font-variation-settings: "opsz" 144, "SOFT" 100, "wght" 500;
    font-style: italic;
    font-size: 18px;
    letter-spacing: 0;
    text-transform: none;
    color: var(--ink);
  }
  .mast-dot {
    width: 5px; height: 5px; border-radius: 50%;
    background: var(--clay);
    box-shadow: 0 0 0 4px rgba(176,90,54,.10);
    animation: pulse 2.4s ease-in-out infinite;
  }
  @keyframes pulse { 0%, 100% { opacity: 1; } 50% { opacity: .35; } }

  /* ─── Hero ─────────────────────────────────────────────────────────── */
  .hero {
    position: relative;
    padding: clamp(28px, 5vw, 64px) clamp(20px, 4vw, 56px) clamp(40px, 6vw, 80px);
    display: grid;
    grid-template-columns: 1.3fr 1fr;
    gap: clamp(32px, 5vw, 80px);
    align-items: stretch;
  }
  @media (max-width: 880px) {
    .hero { grid-template-columns: 1fr; gap: 40px; }
  }

  /* Edition stamp top-left of left column */
  .edition {
    display: inline-flex;
    align-items: center;
    gap: 10px;
    font-family: var(--font-mono);
    font-size: 10.5px;
    letter-spacing: .2em;
    text-transform: uppercase;
    color: var(--ink-2);
    padding: 7px 12px 7px 10px;
    border: 1px solid var(--rule);
    border-radius: 999px;
    background: rgba(240,230,208,.4);
    backdrop-filter: blur(2px);
    margin-bottom: clamp(28px, 4vw, 40px);
    animation: fade-up 1s .15s both ease-out;
  }
  .edition svg { width: 12px; height: 12px; color: var(--clay); }

  /* Headline */
  h1.lede {
    font-family: var(--font-display);
    font-variation-settings: "opsz" 144, "SOFT" 100, "wght" 320;
    font-size: clamp(3rem, 9.5vw, 9rem);
    line-height: .92;
    letter-spacing: -0.025em;
    margin: 0 0 clamp(28px, 4vw, 44px);
    color: var(--ink);
    position: relative;
  }
  h1.lede .l1 { display: block; animation: fade-rise 1.1s .25s both cubic-bezier(.2,.7,.2,1); }
  h1.lede .l2 { display: block; padding-left: clamp(40px, 8vw, 120px); animation: fade-rise 1.1s .42s both cubic-bezier(.2,.7,.2,1); }
  h1.lede .l3 { display: block; animation: fade-rise 1.1s .58s both cubic-bezier(.2,.7,.2,1); }
  h1.lede em {
    font-style: italic;
    font-variation-settings: "opsz" 144, "SOFT" 100, "wght" 360;
    color: var(--clay);
  }
  h1.lede .amp {
    font-style: italic;
    font-variation-settings: "opsz" 144, "SOFT" 100, "wght" 360;
    color: var(--saffron);
    padding: 0 .12em;
  }

  /* Decorative numeral / ghost */
  .numeral {
    position: absolute;
    top: -12px;
    right: clamp(-10px, 2vw, 20px);
    font-family: var(--font-display);
    font-variation-settings: "opsz" 144, "SOFT" 100, "wght" 280;
    font-size: clamp(6rem, 14vw, 13rem);
    line-height: .8;
    color: transparent;
    -webkit-text-stroke: 1px rgba(26,24,20,.18);
    pointer-events: none;
    user-select: none;
    animation: fade-in 1.5s .8s both ease-out;
  }

  /* Sublede */
  .sublede {
    display: grid;
    grid-template-columns: auto 1fr;
    gap: 18px;
    max-width: 580px;
    align-items: start;
    padding-top: 4px;
    animation: fade-up 1s .7s both ease-out;
  }
  .rule-v {
    width: 1px;
    align-self: stretch;
    background: var(--rule);
    position: relative;
  }
  .rule-v::before {
    content: "";
    position: absolute;
    top: 0; left: -2px;
    width: 5px; height: 5px;
    border-radius: 50%;
    background: var(--clay);
  }
  .sublede p {
    font-family: var(--font-display);
    font-variation-settings: "opsz" 14, "SOFT" 50, "wght" 380;
    font-size: clamp(1.05rem, 1.6vw, 1.25rem);
    line-height: 1.5;
    color: var(--ink-2);
    margin: 0;
  }
  .sublede p + p { margin-top: 14px; }

  /* ─── Right column: card with form ─────────────────────────────────── */
  .panel {
    position: relative;
    background:
      linear-gradient(180deg, rgba(232,220,192,0) 0%, rgba(220,201,158,.45) 100%),
      var(--paper-2);
    border: 1px solid var(--rule);
    border-radius: 2px;
    padding: clamp(28px, 3.4vw, 44px);
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    min-height: 480px;
    box-shadow:
      0 1px 0 rgba(255,250,235,.5) inset,
      0 30px 60px -30px rgba(26,24,20,.18);
    animation: fade-up 1s .35s both ease-out;
  }
  /* Deckled edge top */
  .panel::before {
    content: "";
    position: absolute;
    top: -1px; left: -1px; right: -1px;
    height: 4px;
    background-image:
      radial-gradient(circle at 6px 0, transparent 2.5px, var(--paper-2) 2.6px),
      radial-gradient(circle at 6px 0, transparent 2.5px, var(--paper-2) 2.6px);
    background-size: 12px 4px, 12px 4px;
    background-position: 0 0, 6px 0;
    pointer-events: none;
  }

  /* Wax-seal monogram */
  .seal {
    position: absolute;
    top: -28px; right: clamp(20px, 3vw, 36px);
    width: 64px; height: 64px;
    border-radius: 50%;
    background:
      radial-gradient(circle at 35% 30%, #C8693E 0%, var(--clay) 45%, var(--clay-deep) 100%);
    color: var(--paper);
    font-family: var(--font-display);
    font-variation-settings: "opsz" 144, "SOFT" 0, "wght" 600;
    font-style: italic;
    font-size: 30px;
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow:
      inset 0 -3px 6px rgba(0,0,0,.25),
      inset 0 2px 3px rgba(255,255,255,.18),
      0 8px 20px rgba(138,63,34,.32);
    transform: rotate(-8deg);
    animation: fade-in 1s .9s both ease-out, breathe 4s 1.5s ease-in-out infinite;
  }
  .seal::before {
    content: "";
    position: absolute;
    inset: 4px;
    border-radius: 50%;
    border: 1px dashed rgba(240,230,208,.5);
  }
  @keyframes breathe {
    0%, 100% { transform: rotate(-8deg) scale(1); }
    50%      { transform: rotate(-8deg) scale(1.04); }
  }

  .panel-eyebrow {
    font-family: var(--font-mono);
    font-size: 10.5px;
    letter-spacing: .22em;
    text-transform: uppercase;
    color: var(--ink-2);
    margin-bottom: 18px;
    display: flex;
    align-items: center;
    gap: 10px;
  }
  .panel-eyebrow::before, .panel-eyebrow::after {
    content: "";
    height: 1px;
    background: var(--rule);
    flex: 1;
  }
  .panel-eyebrow::before { flex: 0 0 18px; }

  .panel h2 {
    font-family: var(--font-display);
    font-variation-settings: "opsz" 60, "SOFT" 90, "wght" 380;
    font-size: clamp(1.6rem, 2.6vw, 2.1rem);
    line-height: 1.15;
    letter-spacing: -0.015em;
    margin: 0 0 12px;
    color: var(--ink);
  }
  .panel h2 em {
    font-style: italic;
    color: var(--clay);
    font-variation-settings: "opsz" 60, "SOFT" 100, "wght" 400;
  }
  .panel-text {
    font-size: 14.5px;
    line-height: 1.6;
    color: var(--ink-2);
    margin: 0 0 28px;
    max-width: 36ch;
  }

  /* Form */
  .cs-form {
    display: flex;
    flex-direction: column;
    gap: 14px;
    margin-bottom: 18px;
  }
  .field {
    position: relative;
  }
  .field label {
    display: block;
    font-family: var(--font-mono);
    font-size: 10px;
    letter-spacing: .2em;
    text-transform: uppercase;
    color: var(--ink-2);
    margin-bottom: 8px;
  }
  .field input[type="email"] {
    width: 100%;
    border: none;
    background: transparent;
    border-bottom: 1px solid var(--ink);
    padding: 10px 0 10px;
    font-family: var(--font-display);
    font-variation-settings: "opsz" 14, "SOFT" 50, "wght" 400;
    font-size: 1.05rem;
    color: var(--ink);
    outline: none;
    transition: border-color .25s;
  }
  .field input[type="email"]::placeholder {
    color: rgba(26,24,20,.35);
    font-style: italic;
  }
  .field::after {
    content: "";
    position: absolute;
    left: 0; bottom: 0;
    height: 2px; width: 0;
    background: var(--clay);
    transition: width .35s cubic-bezier(.2,.7,.2,1);
  }
  .field:focus-within::after { width: 100%; }

  .submit {
    display: inline-flex;
    align-items: center;
    justify-content: space-between;
    gap: 16px;
    width: 100%;
    padding: 16px 20px;
    border: 1px solid var(--ink);
    background: var(--ink);
    color: var(--paper);
    font-family: var(--font-mono);
    font-size: 11px;
    letter-spacing: .24em;
    text-transform: uppercase;
    cursor: pointer;
    border-radius: 0;
    position: relative;
    overflow: hidden;
    transition: color .35s;
  }
  .submit::before {
    content: "";
    position: absolute;
    inset: 0;
    background: var(--clay);
    transform: translateY(101%);
    transition: transform .4s cubic-bezier(.2,.7,.2,1);
    z-index: 0;
  }
  .submit:hover::before { transform: translateY(0); }
  .submit > * { position: relative; z-index: 1; }
  .submit .arrow {
    display: inline-flex;
    align-items: center;
    transition: transform .35s;
  }
  .submit:hover .arrow { transform: translateX(4px); }

  .privacy-note {
    font-family: var(--font-mono);
    font-size: 10px;
    letter-spacing: .12em;
    text-transform: uppercase;
    color: rgba(26,24,20,.5);
    line-height: 1.5;
  }
  .privacy-note a { color: var(--ink-2); text-decoration: underline; text-underline-offset: 3px; }

  /* Message */
  .cs-msg {
    margin-bottom: 18px;
    padding: 12px 16px;
    font-size: 13.5px;
    line-height: 1.5;
    border-left: 2px solid;
    background: rgba(255,250,235,.45);
    font-family: var(--font-display);
    font-variation-settings: "opsz" 14, "SOFT" 50, "wght" 380;
  }
  .cs-msg.success { border-color: var(--sage); color: var(--ink); }
  .cs-msg.error   { border-color: var(--clay-deep); color: var(--clay-deep); }

  /* ─── Berber ornament (left column, decorative) ────────────────────── */
  .ornament {
    position: absolute;
    left: clamp(20px, 4vw, 56px);
    bottom: clamp(40px, 6vw, 80px);
    width: 92px; height: 92px;
    color: var(--ink);
    opacity: .42;
    animation: fade-in 1.6s 1s both ease-out, drift 18s ease-in-out infinite;
    pointer-events: none;
  }
  @keyframes drift {
    0%, 100% { transform: translateY(0) rotate(0deg); }
    50%      { transform: translateY(-6px) rotate(2deg); }
  }
  @media (max-width: 880px) { .ornament { display: none; } }

  /* Mountain silhouette ambient layer */
  .mountains {
    position: absolute;
    left: 0; right: 0; bottom: 0;
    height: 38%;
    pointer-events: none;
    opacity: .14;
    z-index: 0;
  }

  /* ─── Ticker ───────────────────────────────────────────────────────── */
  .ticker {
    position: relative;
    border-top: 1px solid var(--rule);
    border-bottom: 1px solid var(--rule);
    background:
      repeating-linear-gradient(45deg, transparent 0 8px, rgba(26,24,20,.025) 8px 9px);
    overflow: hidden;
    height: 56px;
    display: flex;
    align-items: center;
  }
  .ticker-track {
    display: flex;
    gap: 56px;
    white-space: nowrap;
    animation: ticker 42s linear infinite;
    padding-right: 56px;
  }
  .ticker-item {
    display: inline-flex;
    align-items: center;
    gap: 18px;
    font-family: var(--font-display);
    font-variation-settings: "opsz" 60, "SOFT" 100, "wght" 360;
    font-style: italic;
    font-size: 1.5rem;
    color: var(--ink-2);
    letter-spacing: -.01em;
  }
  .ticker-item .star {
    color: var(--clay);
    font-style: normal;
    font-family: var(--font-display);
    font-variation-settings: "opsz" 60, "wght" 700;
  }
  @keyframes ticker {
    from { transform: translateX(0); }
    to   { transform: translateX(-50%); }
  }
  .ticker:hover .ticker-track { animation-play-state: paused; }

  /* ─── Footer ───────────────────────────────────────────────────────── */
  .colophon {
    display: grid;
    grid-template-columns: 1fr auto 1fr;
    align-items: center;
    gap: 24px;
    padding: 28px clamp(20px, 4vw, 56px);
    font-family: var(--font-mono);
    font-size: 10.5px;
    letter-spacing: .14em;
    text-transform: uppercase;
    color: var(--ink-2);
  }
  .col-l { display: flex; flex-direction: column; gap: 4px; }
  .col-r { display: flex; flex-direction: column; gap: 4px; align-items: flex-end; text-align: right; }
  .col-c {
    display: inline-flex;
    align-items: center;
    gap: 10px;
  }
  .col-c svg { width: 18px; height: 18px; }
  .col-r a, .col-l a {
    color: inherit;
    text-decoration: none;
    border-bottom: 1px solid transparent;
    transition: border-color .2s, color .2s;
  }
  .col-r a:hover, .col-l a:hover { color: var(--clay); border-bottom-color: var(--clay); }

  .channels {
    display: flex; gap: 12px; align-items: center;
  }
  .channels a {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 5px 10px;
    border: 1px solid var(--rule);
    border-radius: 999px;
    background: rgba(240,230,208,.4);
    transition: background .2s, border-color .2s, color .2s;
  }
  .channels a:hover {
    background: var(--ink);
    color: var(--paper);
    border-color: var(--ink);
  }
  .channels svg { width: 12px; height: 12px; }

  @media (max-width: 720px) {
    .masthead, .colophon { grid-template-columns: 1fr; text-align: center; }
    .mast-l, .mast-r, .col-l, .col-r { justify-content: center; align-items: center; text-align: center; }
    .col-r { align-items: center; }
  }

  /* ─── Animations ───────────────────────────────────────────────────── */
  @keyframes fade-down  { from { opacity: 0; transform: translateY(-8px); } to { opacity: 1; transform: none; } }
  @keyframes fade-up    { from { opacity: 0; transform: translateY(14px); } to { opacity: 1; transform: none; } }
  @keyframes fade-rise  { from { opacity: 0; transform: translateY(28px); } to { opacity: 1; transform: none; } }
  @keyframes fade-in    { from { opacity: 0; } to { opacity: 1; } }

  @media (prefers-reduced-motion: reduce) {
    *, *::before, *::after {
      animation: none !important;
      transition: none !important;
    }
  }
</style>
</head>
<body>

<!-- Mountain silhouette (Atlas) -->
<svg class="mountains" viewBox="0 0 1440 320" preserveAspectRatio="none" aria-hidden="true">
  <path d="M0,260 L120,180 L210,220 L320,140 L430,200 L540,120 L640,170 L760,90 L880,160 L980,110 L1100,180 L1220,130 L1330,200 L1440,160 L1440,320 L0,320 Z" fill="#1A1814"/>
</svg>

<div class="stage">

  <!-- ─── Masthead ─── -->
  <header class="masthead">
    <div class="mast-l">
      <span class="mast-dot" aria-hidden="true"></span>
      <span>Coopérative Al Amal</span>
    </div>
    <div class="mast-c">GreenAmal</div>
    <div class="mast-r">
      <span>Azrou · Moyen Atlas</span>
      <span>·</span>
      <span><?= date('d.m.Y') ?></span>
    </div>
  </header>

  <!-- ─── Hero ─── -->
  <section class="hero">

    <!-- Left column · headline -->
    <div class="hero-l">
      <span class="edition">
        <svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><circle cx="12" cy="12" r="6"/></svg>
        Vol. 01 · Édition d'ouverture · N°<?= e($edition_no) ?>
      </span>

      <h1 class="lede">
        <span class="numeral" aria-hidden="true">01</span>
        <span class="l1">La maison</span>
        <span class="l2">ouvre <em>ses</em></span>
        <span class="l3">portes <span class="amp">&amp;</span> bientôt.</span>
      </h1>

      <div class="sublede">
        <span class="rule-v" aria-hidden="true"></span>
        <div>
          <p>Une coopérative de femmes du Moyen Atlas, des gestes transmis depuis trois générations, et une boutique qui ouvrira ses portes dans quelques semaines.</p>
          <p style="font-family: var(--font-body); font-size: 13px; color: var(--ink-2); letter-spacing: .02em;">Huile d'argan pressée à froid · eau de rose de Kelaat M'Gouna · ghassoul du Moyen Atlas · couscous d'orge artisanal · cosmétiques naturels — chaque pièce numérotée, chaque lot tracé.</p>
        </div>
      </div>
    </div>

    <!-- Right column · panel with form -->
    <aside class="panel">
      <div class="seal" aria-hidden="true">G</div>

      <div>
        <div class="panel-eyebrow">Liste d'ouverture · accès anticipé</div>
        <h2>Soyez prévenu(e) <em>en premier.</em></h2>
        <p class="panel-text">Recevez le mot d'ouverture, le catalogue de lancement et un accès anticipé de 48 heures avant le grand public.</p>

        <?php if ($msg): ?>
          <div class="cs-msg <?= e($msg['type']) ?>"><?= e($msg['text']) ?></div>
        <?php endif; ?>

        <form method="post" class="cs-form" novalidate>
          <?= csrf_field() ?>
          <div class="field">
            <label for="cs-email">Adresse email</label>
            <input id="cs-email" type="email" name="email" placeholder="prenom@exemple.com" required autocomplete="email">
          </div>

          <button class="submit" type="submit">
            <span>M'inscrire à la liste</span>
            <span class="arrow" aria-hidden="true">
              <svg width="22" height="10" viewBox="0 0 22 10" fill="none">
                <path d="M0 5 H20 M16 1 L20 5 L16 9" stroke="currentColor" stroke-width="1.4"/>
              </svg>
            </span>
          </button>
        </form>
      </div>

      <div class="privacy-note">
        Aucun spam · désinscription en un clic · vos données restent confidentielles ·
        <a href="privacy.php">notre politique</a>
      </div>
    </aside>

    <!-- Berber ornament (decorative) -->
    <svg class="ornament" viewBox="0 0 92 92" aria-hidden="true">
      <g fill="none" stroke="currentColor" stroke-width="1.1">
        <path d="M46 4 L88 46 L46 88 L4 46 Z"/>
        <path d="M46 14 L78 46 L46 78 L14 46 Z"/>
        <path d="M46 26 L66 46 L46 66 L26 46 Z"/>
        <path d="M46 4 L46 88 M4 46 L88 46"/>
        <path d="M14 14 L78 78 M78 14 L14 78"/>
        <circle cx="46" cy="46" r="3" fill="currentColor"/>
        <circle cx="46" cy="14" r="1.5" fill="currentColor"/>
        <circle cx="46" cy="78" r="1.5" fill="currentColor"/>
        <circle cx="14" cy="46" r="1.5" fill="currentColor"/>
        <circle cx="78" cy="46" r="1.5" fill="currentColor"/>
      </g>
    </svg>
  </section>

  <!-- ─── Ticker ─── -->
  <div class="ticker" aria-hidden="true">
    <div class="ticker-track">
      <span class="ticker-item">Huile d'argan <span class="star">✦</span></span>
      <span class="ticker-item">Eau de rose <span class="star">✦</span></span>
      <span class="ticker-item">Ghassoul du Moyen Atlas <span class="star">✦</span></span>
      <span class="ticker-item">Couscous d'orge <span class="star">✦</span></span>
      <span class="ticker-item">Savon noir <span class="star">✦</span></span>
      <span class="ticker-item">Eau de fleur d'oranger <span class="star">✦</span></span>
      <span class="ticker-item">Henné de Zagora <span class="star">✦</span></span>
      <span class="ticker-item">Miel de thym <span class="star">✦</span></span>
      <!-- duplicated for seamless loop -->
      <span class="ticker-item">Huile d'argan <span class="star">✦</span></span>
      <span class="ticker-item">Eau de rose <span class="star">✦</span></span>
      <span class="ticker-item">Ghassoul du Moyen Atlas <span class="star">✦</span></span>
      <span class="ticker-item">Couscous d'orge <span class="star">✦</span></span>
      <span class="ticker-item">Savon noir <span class="star">✦</span></span>
      <span class="ticker-item">Eau de fleur d'oranger <span class="star">✦</span></span>
      <span class="ticker-item">Henné de Zagora <span class="star">✦</span></span>
      <span class="ticker-item">Miel de thym <span class="star">✦</span></span>
    </div>
  </div>

  <!-- ─── Colophon ─── -->
  <footer class="colophon">
    <div class="col-l">
      <span>© <?= date('Y') ?> <?= e(SITE_NAME) ?> · Tous droits réservés</span>
      <span>
        <a href="cgv.php">CGV</a> · <a href="privacy.php">Confidentialité</a> · <a href="mentions.php">Mentions légales</a>
      </span>
    </div>

    <div class="col-c channels">
      <a href="mailto:<?= e(CONTACT_EMAIL) ?>" aria-label="Email">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
        Email
      </a>
      <a href="https://wa.me/<?= e(WHATSAPP_NUMBER) ?>" aria-label="WhatsApp">
        <svg viewBox="0 0 24 24" fill="currentColor"><path d="M20.5 3.5A11.4 11.4 0 0012 0a11.5 11.5 0 00-9.7 17.6L0 24l6.6-1.7a11.5 11.5 0 005.4 1.4h.1A11.5 11.5 0 0024 12.2c0-3.1-1.2-6-3.5-8.7zM12 21.5h-.1a9.6 9.6 0 01-4.9-1.3l-.3-.2-3.6.9.9-3.5-.2-.4a9.5 9.5 0 117.4 5.5z"/></svg>
        WhatsApp
      </a>
    </div>

    <div class="col-r">
      <span>Édition limitée · Lancement 2026</span>
      <span>Imprimé numériquement à Azrou ↟</span>
    </div>
  </footer>

</div>
</body>
</html>
