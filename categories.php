<?php
require_once __DIR__ . '/includes/helpers.php';
require_once __DIR__ . '/includes/image.php';

$page_title = 'Catégories';
$page_desc  = 'Explorez nos univers de produits naturels du Maroc · huiles essentielles, plantes aromatiques, cosmétiques, couscous artisanal, savons et plus.';
$nav        = 'categories';
$og_image   = '/assets/img/categories/huiles-vegetales.jpg';
$jsonld     = [seo_breadcrumb_jsonld([
    ['Accueil', '/'],
    ['Catégories', '/categories'],
])];

$categories = db_all("
    SELECT c.*, (SELECT COUNT(*) FROM products WHERE category_id = c.id AND status='active') AS product_count
    FROM categories c
    ORDER BY c.display_order ASC
");

require __DIR__ . '/includes/header.php';
?>

<div class="container breadcrumb">
  <a href="/">Accueil</a><span>/</span><span>Catégories</span>
</div>

<section class="about-hero">
  <div class="container">
    <span class="eyebrow">Nos univers</span>
    <h1>Sept familles de produits, <em style="color: var(--terracotta); font-style: italic;">une seule promesse</em>.</h1>
    <p>De la cueillette à la mise en flacon, chaque catégorie raconte un savoir-faire transmis de mères en filles dans la coopérative Al Amal.</p>
  </div>
</section>

<section class="section section-cream" style="padding-top: 30px;">
  <div class="container">
    <div class="categories-grid">
      <?php foreach ($categories as $i => $cat): ?>
        <a href="boutique?cat=<?= e($cat['slug']) ?>" class="category-banner<?= $i === 0 ? ' category-banner-feature' : '' ?>">
          <div class="category-banner-image">
            <?= picture_tag($cat['image_url'], $cat['name'] . ' · produits naturels GreenAmal', [
                'lazy'          => $i > 0,
                'fetchpriority' => $i === 0 ? 'high' : 'auto',
                'sizes'         => $i === 0 ? '(max-width: 720px) 100vw, 60vw' : '(max-width: 720px) 100vw, 33vw',
                'width'         => 1600,
                'height'        => $i === 0 ? 1000 : 900,
            ]) ?>
          </div>
          <div class="category-banner-content">
            <span class="category-count"><?= (int) $cat['product_count'] ?> produit<?= $cat['product_count'] > 1 ? 's' : '' ?></span>
            <h2><?= e($cat['name']) ?></h2>
            <?php if (!empty($cat['description'])): ?>
              <p><?= e($cat['description']) ?></p>
            <?php endif; ?>
            <span class="category-cta">
              Voir la collection
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
            </span>
          </div>
        </a>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<section class="section">
  <div class="container">
    <div style="text-align: center; max-width: 640px; margin: 0 auto;">
      <span class="eyebrow">Tout le catalogue</span>
      <h2 style="margin-bottom: 16px;">Vous cherchez un produit en particulier ?</h2>
      <p style="color: var(--ink-soft); margin-bottom: 24px;">Parcourez la boutique avec des filtres avancés : prix, catégorie, recherche, tri.</p>
      <a href="boutique" class="btn btn-primary btn-lg">
        Aller à la boutique
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
      </a>
    </div>
  </div>
</section>

<style>
  .categories-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 22px;
  }
  .categories-grid .category-banner-feature {
    grid-column: span 3;
    grid-template-columns: 1.2fr 1fr;
  }

  .category-banner {
    display: grid;
    grid-template-columns: 1fr;
    background: var(--white);
    border-radius: var(--radius-lg);
    overflow: hidden;
    transition: all .35s var(--ease);
    border: 1px solid var(--line);
    text-decoration: none;
    color: inherit;
  }
  .category-banner:hover {
    transform: translateY(-4px);
    box-shadow: var(--shadow-lg);
    border-color: var(--olive-soft);
  }
  .category-banner-image {
    aspect-ratio: 16/10;
    overflow: hidden;
    background: var(--sand);
  }
  .category-banner-feature .category-banner-image {
    aspect-ratio: auto;
    height: 100%;
    min-height: 320px;
  }
  .category-banner-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform .6s var(--ease);
  }
  .category-banner:hover .category-banner-image img { transform: scale(1.06); }
  .category-banner-content {
    padding: 24px 26px 28px;
    display: flex;
    flex-direction: column;
    gap: 8px;
  }
  .category-banner-feature .category-banner-content {
    padding: 40px;
    justify-content: center;
  }
  .category-count {
    font-size: 0.72rem;
    letter-spacing: 0.14em;
    text-transform: uppercase;
    color: var(--terracotta);
    font-weight: 600;
  }
  .category-banner h2 {
    font-size: 1.5rem;
    color: var(--ink);
    margin-bottom: 4px;
  }
  .category-banner-feature h2 {
    font-size: 2.4rem;
    line-height: 1.1;
  }
  .category-banner p {
    color: var(--ink-soft);
    font-size: 0.92rem;
    margin-bottom: 4px;
  }
  .category-banner-feature p {
    font-size: 1.05rem;
    margin-top: 4px;
    margin-bottom: 12px;
  }
  .category-cta {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    margin-top: 6px;
    font-weight: 600;
    font-size: 0.88rem;
    color: var(--olive);
    transition: gap .25s var(--ease);
  }
  .category-banner:hover .category-cta { gap: 12px; color: var(--terracotta); }

  @media (max-width: 1024px) {
    .categories-grid { grid-template-columns: repeat(2, 1fr); }
    .categories-grid .category-banner-feature { grid-column: span 2; grid-template-columns: 1fr 1fr; }
  }
  @media (max-width: 720px) {
    .categories-grid { grid-template-columns: 1fr; gap: 16px; }
    .categories-grid .category-banner-feature {
      grid-column: span 1;
      grid-template-columns: 1fr;
    }
    .category-banner-feature .category-banner-image { min-height: 220px; }
    .category-banner-content { padding: 20px 22px 24px; }
    .category-banner-feature .category-banner-content { padding: 24px; }
    .category-banner-feature h2 { font-size: 1.7rem; }
  }
</style>

<?php require __DIR__ . '/includes/footer.php'; ?>
