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
<link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,500;0,600;0,700;1,500&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="/assets/css/styles.css">

<?php foreach ($jsonld as $block): if (empty($block)) continue; ?>
<script type="application/ld+json"><?= seo_jsonld($block) ?></script>
<?php endforeach; ?>
</head>
<body data-shipping-threshold="<?= (int) FREE_SHIPPING_THRESHOLD ?>">

<div class="promo-bar">
  <div class="container">
    <strong>−25% sur votre première commande</strong> avec le code <code>first25</code> · Livraison gratuite dès <?= price(FREE_SHIPPING_THRESHOLD) ?>
  </div>
</div>

<header class="site-header">
  <div class="header-inner">
    <a href="/index.php" class="logo">
      <span class="logo-mark">G</span>
      <?= e(SITE_NAME) ?>
    </a>
    <nav class="main-nav">
      <a href="/index.php" class="<?= nav_active($nav, 'home') ?>">Accueil</a>
      <a href="/shop.php" class="<?= nav_active($nav, 'shop') ?>">Boutique</a>
      <a href="/categories.php" class="<?= nav_active($nav, 'categories') ?>">Catégories</a>
      <a href="/about.php" class="<?= nav_active($nav, 'about') ?>">Notre histoire</a>
      <a href="/contact.php" class="<?= nav_active($nav, 'contact') ?>">Contact</a>
    </nav>
    <div class="header-actions">
      <button class="icon-btn menu-toggle" aria-label="Menu">
        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="18" x2="21" y2="18"/></svg>
      </button>
      <a href="/search.php" class="icon-btn" aria-label="Recherche">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
      </a>
      <a href="<?= customer_logged_in() ? '/account.php' : '/login.php' ?>" class="icon-btn" aria-label="<?= customer_logged_in() ? 'Mon compte' : 'Se connecter' ?>" title="<?= customer_logged_in() ? 'Mon compte' : 'Se connecter' ?>">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
      </a>
      <a href="/cart.php" class="icon-btn" aria-label="Panier">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 2L3 6v14a2 2 0 002 2h14a2 2 0 002-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 01-8 0"/></svg>
        <span class="badge cart-badge"><?= cart_count() ?></span>
      </a>
    </div>
  </div>
</header>
