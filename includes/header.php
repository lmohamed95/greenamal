<?php
require_once __DIR__ . '/helpers.php';
coming_soon_guard();

/*
 * Page-level SEO variables (set in each page BEFORE requiring this file):
 *   $page_title    string  · used in <title>, og:title, twitter:title
 *   $page_desc     string  · used in <meta description>, og:description, twitter:description
 *   $nav           string  · active nav slug (home, shop, categories, about)
 *   $og_image      string  · absolute or root-relative URL of the share image
 *   $og_type       string  · 'website' (default) or 'product' or 'article'
 *   $canonical    ?string  · override canonical URL (defaults to current request)
 *   $noindex       bool    · true to emit <meta robots="noindex">
 *   $jsonld        array   · array of associative arrays to emit as JSON-LD blocks
 *   $extra_meta    string  · raw extra meta tags (e.g. product:price)
 */

$page_title  = $page_title ?? SITE_NAME;
$page_desc   = $page_desc  ?? 'Produits naturels du Maroc · coopérative féminine d\'Azrou. Huiles essentielles, plantes, cosmétiques, couscous artisanal. Certifié ONSSA.';
$nav         = $nav        ?? '';
$og_image    = $og_image   ?? '/assets/img/og-default.jpg';
$og_type     = $og_type    ?? 'website';
$canonical   = $canonical  ?? null;
$noindex     = $noindex    ?? false;
$jsonld      = $jsonld     ?? [];
$extra_meta  = $extra_meta ?? '';

$canonical_url = seo_canonical($canonical);
$og_image_abs  = seo_abs($og_image);
$full_title    = $page_title === SITE_NAME ? SITE_NAME : "{$page_title} · " . SITE_NAME;
?><!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="csrf-token" content="<?= e(csrf_token()) ?>">

<title><?= e($full_title) ?></title>
<meta name="description" content="<?= e($page_desc) ?>">

<?php if ($noindex): ?>
<meta name="robots" content="noindex, nofollow">
<?php else: ?>
<meta name="robots" content="index, follow, max-image-preview:large">
<?php endif; ?>

<link rel="canonical" href="<?= e($canonical_url) ?>">

<!-- Open Graph -->
<meta property="og:type"        content="<?= e($og_type) ?>">
<meta property="og:title"       content="<?= e($full_title) ?>">
<meta property="og:description" content="<?= e($page_desc) ?>">
<meta property="og:url"         content="<?= e($canonical_url) ?>">
<meta property="og:image"       content="<?= e($og_image_abs) ?>">
<meta property="og:image:alt"   content="<?= e($page_title) ?> · <?= e(SITE_NAME) ?>">
<meta property="og:site_name"   content="<?= e(SITE_NAME) ?>">
<meta property="og:locale"      content="fr_MA">

<!-- Twitter -->
<meta name="twitter:card"        content="summary_large_image">
<meta name="twitter:title"       content="<?= e($full_title) ?>">
<meta name="twitter:description" content="<?= e($page_desc) ?>">
<meta name="twitter:image"       content="<?= e($og_image_abs) ?>">

<?= $extra_meta ?>

<!-- Favicon (placeholder · replace when a real favicon is added) -->
<link rel="icon" type="image/svg+xml" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><circle cx='50' cy='50' r='48' fill='%233A5A40'/><text x='50' y='66' text-anchor='middle' font-family='serif' font-weight='600' font-size='52' fill='%23FAF6F0'>G</text></svg>">

<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,500;0,600;0,700;1,500&family=Fraunces:ital,opsz,wght@0,9..144,400;0,9..144,500;0,9..144,600;1,9..144,400;1,9..144,500&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="<?= e(asset('/assets/css/styles.css')) ?>">
<?php foreach (($extra_css ?? []) as $css): ?>
<link rel="stylesheet" href="<?= e(asset($css)) ?>">
<?php endforeach; ?>

<?php foreach ($jsonld as $block): if (empty($block)) continue; ?>
<script type="application/ld+json"><?= seo_jsonld($block) ?></script>
<?php endforeach; ?>
</head>
<body class="<?= e($body_class ?? '') ?>" data-shipping-threshold="<?= (int) FREE_SHIPPING_THRESHOLD ?>">

<div class="promo-bar">
  <div class="container">
    <strong>Livraison gratuite</strong> à partir de <?= price(FREE_SHIPPING_THRESHOLD) ?>
  </div>
</div>

<header class="site-header">
  <div class="header-inner">
    <button class="icon-btn menu-toggle" aria-label="Menu">
      <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="18" x2="21" y2="18"/></svg>
    </button>
    <a href="/" class="logo" aria-label="<?= e(SITE_NAME) ?>">
      <img src="/assets/img/logo.svg" alt="<?= e(SITE_NAME) ?>" class="logo-img">
    </a>
    <nav class="main-nav">
      <a href="/" class="<?= nav_active($nav, 'home') ?>">Accueil</a>
      <a href="/boutique" class="<?= nav_active($nav, 'shop') ?>">Boutique</a>
      <a href="/categories" class="<?= nav_active($nav, 'categories') ?>">Catégories</a>
      <a href="/notre-histoire" class="<?= nav_active($nav, 'about') ?>">Notre histoire</a>
      <a href="/contact" class="<?= nav_active($nav, 'contact') ?>">Contact</a>
    </nav>
    <div class="header-actions">
      <button type="button" class="icon-btn header-icon-search" id="searchToggle" aria-label="Recherche" aria-expanded="false" aria-controls="headerSearch">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
      </button>
      <a href="<?= customer_logged_in() ? '/account' : '/login' ?>" class="icon-btn header-icon-account" aria-label="<?= customer_logged_in() ? 'Mon compte' : 'Se connecter' ?>" title="<?= customer_logged_in() ? 'Mon compte' : 'Se connecter' ?>">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
      </a>
      <a href="/panier" class="icon-btn" aria-label="Panier">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 2L3 6v14a2 2 0 002 2h14a2 2 0 002-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 01-8 0"/></svg>
        <span class="badge cart-badge"><?= cart_count() ?></span>
      </a>
    </div>
  </div>

  <!-- Search dropdown · slides down from the bottom of the header on click of
       the search icon. Submits to the same /recherche page so result rendering
       stays server-side. -->
  <div class="header-search" id="headerSearch" aria-hidden="true">
    <div class="header-search-inner">
      <form method="get" action="/recherche" class="header-search-form" role="search">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
        <input type="search" name="q" placeholder="Rechercher un produit, une catégorie…" autocomplete="off" minlength="2" required>
        <button type="submit" class="header-search-submit">Rechercher</button>
        <button type="button" class="header-search-close" id="searchClose" aria-label="Fermer">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
        </button>
      </form>
    </div>
  </div>
</header>

<!-- Mobile slide-out menu (rendered hidden, opened on mobile by the hamburger) -->
<div class="mobile-drawer-backdrop" id="mobile-drawer-backdrop"></div>
<aside class="mobile-drawer" id="mobile-drawer" aria-label="Menu" aria-hidden="true">
  <div class="mobile-drawer-head">
    <a href="/" class="mobile-drawer-logo" aria-label="<?= e(SITE_NAME) ?>">
      <img src="/assets/img/logo.svg" alt="<?= e(SITE_NAME) ?>">
    </a>
    <button class="mobile-drawer-close" aria-label="Fermer">
      <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
    </button>
  </div>
  <nav class="mobile-drawer-nav">
    <a href="/" class="<?= nav_active($nav, 'home') ?>">
      <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M3 11 12 4l9 7"/><path d="M5 10v9h14v-9"/></svg>
      Accueil
    </a>
    <a href="/boutique" class="<?= nav_active($nav, 'shop') ?>">
      <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M3 9h18v11a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1V9Z"/><path d="M8 9V6a4 4 0 0 1 8 0v3"/></svg>
      Boutique
    </a>
    <a href="/categories" class="<?= nav_active($nav, 'categories') ?>">
      <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>
      Catégories
    </a>
    <a href="/notre-histoire" class="<?= nav_active($nav, 'about') ?>">
      <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2Z"/></svg>
      Notre histoire
    </a>
    <a href="/contact" class="<?= nav_active($nav, 'contact') ?>">
      <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M21 11.5a8.4 8.4 0 0 1-9 8.4 8.5 8.5 0 0 1-3.7-.9L3 21l1.9-5.7a8.5 8.5 0 0 1-.9-3.8 8.4 8.4 0 0 1 8.5-8.5 8.4 8.4 0 0 1 8.5 8.5Z"/></svg>
      Contact
    </a>
  </nav>
  <div class="mobile-drawer-section">
    <div class="mobile-drawer-section-title">Mon compte</div>
    <a href="/recherche" class="mobile-drawer-link" data-search-trigger>
      <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
      Rechercher un produit
    </a>
    <a href="<?= customer_logged_in() ? '/account' : '/login' ?>" class="mobile-drawer-link">
      <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
      <?= customer_logged_in() ? 'Mon compte' : 'Se connecter' ?>
    </a>
    <a href="/favoris" class="mobile-drawer-link">
      <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M12 21s-7-4.5-7-10a4 4 0 0 1 7-2.6A4 4 0 0 1 19 11c0 5.5-7 10-7 10Z"/></svg>
      Favoris
    </a>
  </div>
  <div class="mobile-drawer-foot">
    <a href="/contact" class="mobile-drawer-contact-cta">
      <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M22 16.9v3a2 2 0 0 1-2.2 2 19.8 19.8 0 0 1-8.6-3.1 19.5 19.5 0 0 1-6-6A19.8 19.8 0 0 1 2.1 4.2 2 2 0 0 1 4.1 2h3a2 2 0 0 1 2 1.7l.5 2.5a2 2 0 0 1-.6 1.9l-1.3 1.3a16 16 0 0 0 6 6l1.3-1.3a2 2 0 0 1 1.9-.6l2.5.5a2 2 0 0 1 1.7 2Z"/></svg>
      Nous contacter
    </a>
  </div>
</aside>
