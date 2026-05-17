<?php
require_once __DIR__ . '/includes/helpers.php';
require_once __DIR__ . '/includes/image.php';

$page_title = 'Catégories';
$page_desc  = 'Explorez nos univers de produits naturels du Maroc · huiles essentielles, plantes aromatiques, cosmétiques, couscous artisanal, savons et plus.';
$nav        = 'categories';
$og_image   = '/assets/img/categories/huiles-vegetales.jpg';
$body_class = 'gd-2026';
$extra_css  = ['/assets/css/home.css'];
$jsonld     = [seo_breadcrumb_jsonld([
    ['Accueil', '/'],
    ['Catégories', '/categories'],
])];

$categories = db_all("
    SELECT c.*, (SELECT COUNT(*) FROM products WHERE category_id = c.id AND status='active') AS product_count
    FROM categories c
    ORDER BY c.display_order ASC
");

// Pre-compute "from" prices and themes
$CAT_THEME = [
    'huiles-essentielles' => ['c1' => '#EBE3D0', 'c2' => '#D9CDA8', 'icon' => '🌿'],
    'huiles-vegetales'    => ['c1' => '#F5E4C2', 'c2' => '#E5C779', 'icon' => '🫒'],
    'eau-florale'         => ['c1' => '#F4D9CE', 'c2' => '#E8B89D', 'icon' => '🌸'],
    'pam'                 => ['c1' => '#E4D5BC', 'c2' => '#CFB58A', 'icon' => '🪴'],
    'couscous'            => ['c1' => '#F0E1B7', 'c2' => '#E0C383', 'icon' => '🌾'],
    'farine'              => ['c1' => '#EEDFB9', 'c2' => '#D8B976', 'icon' => '🌾'],
    'poudres'             => ['c1' => '#E3D2B0', 'c2' => '#C9AC75', 'icon' => '🧂'],
    'savons'              => ['c1' => '#E6D9C3', 'c2' => '#C4A877', 'icon' => '🧼'],
    'packs'               => ['c1' => '#F4D9CE', 'c2' => '#D9A98F', 'icon' => '🎁'],
    'divers'              => ['c1' => '#E2D4B6', 'c2' => '#BBA374', 'icon' => '✨'],
];

$total_products    = (int) db_value("SELECT COUNT(*) FROM products WHERE status='active'");
$total_categories  = count($categories);

require __DIR__ . '/includes/header.php';
?>

<!-- Category rail -->
<div class="cat-rail">
  <div class="cat-rail-scroll">
    <a href="/boutique" class="cat-chip">
      <span class="chip-icon">✦</span>
      Tout
      <span class="count"><?= $total_products ?></span>
    </a>
    <?php foreach ($categories as $cat):
      $theme = $CAT_THEME[$cat['slug']] ?? ['icon' => '🌿'];
    ?>
      <a href="/boutique?cat=<?= e($cat['slug']) ?>" class="cat-chip">
        <span class="chip-icon"><?= $theme['icon'] ?></span>
        <?= e($cat['name']) ?>
        <span class="count"><?= (int) $cat['product_count'] ?></span>
      </a>
    <?php endforeach; ?>
  </div>
</div>

<section class="cat-hero">
  <svg class="cat-hero-leaves" viewBox="0 0 240 180" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
    <path d="M118 168 C 118 100, 150 35, 230 12 C 238 78, 210 138, 152 162 C 138 168, 126 169, 118 168 Z" fill="#2F8C3A"/>
    <path d="M118 168 C 118 100, 86 35, 6 12 C -2 78, 26 138, 84 162 C 98 168, 110 169, 118 168 Z" fill="#2F8C3A"/>
  </svg>
  <div class="container cat-hero-inner">
    <div class="crumbs"><a href="/">Accueil</a><span class="sep">/</span><span>Catégories</span></div>
    <span class="h-eyebrow">Nos univers</span>
    <h1><?= $total_categories ?> familles de produits, <em>une seule promesse</em>.</h1>
    <p>De la cueillette à la mise en flacon, chaque catégorie raconte un savoir-faire transmis de mères en filles dans la coopérative Al Amal.</p>
    <div class="cat-hero-stats">
      <div class="cat-hero-stat"><div class="n"><?= $total_categories ?></div><div class="l">CATÉGORIES</div></div>
      <div class="cat-hero-stat"><div class="n"><?= $total_products ?></div><div class="l">PRODUITS</div></div>
      <div class="cat-hero-stat"><div class="n">45</div><div class="l">PRODUCTRICES</div></div>
    </div>
  </div>
</section>

<section class="cat-list">
  <div class="container">
    <div class="cat-list-grid">
      <?php foreach ($categories as $cat):
        $theme = $CAT_THEME[$cat['slug']] ?? ['c1' => '#F0E6D0', 'c2' => '#D8C9A4', 'icon' => '🌿'];
        $from  = (float) db_value("SELECT MIN(price) FROM products WHERE category_id = ? AND status='active'", [$cat['id']]);
      ?>
        <a href="/boutique?cat=<?= e($cat['slug']) ?>" class="cat-card">
          <div class="cat-card-media" style="--c1:<?= e($theme['c1']) ?>;--c2:<?= e($theme['c2']) ?>;">
            <?php if (!empty($cat['image_url'])): ?>
              <?= picture_tag($cat['image_url'], $cat['name'] . ' · GreenAmal', [
                  'lazy'   => true,
                  'sizes'  => '(max-width: 720px) 100vw, 33vw',
                  'width'  => 800,
                  'height' => 500,
              ]) ?>
            <?php else: ?>
              <span class="emoji"><?= $theme['icon'] ?></span>
            <?php endif; ?>
          </div>
          <div class="cat-card-body">
            <span class="ct"><?= (int) $cat['product_count'] ?> produit<?= $cat['product_count'] > 1 ? 's' : '' ?></span>
            <h3><?= e($cat['name']) ?></h3>
            <?php if (!empty($cat['description'])): ?>
              <p><?= e($cat['description']) ?></p>
            <?php endif; ?>
            <div class="cat-card-foot">
              <span class="link">Voir la collection
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
              </span>
              <?php if ($from > 0): ?>
                <span class="from">à partir de <b><?= number_format($from, 0, ',', ' ') ?> DH</b></span>
              <?php endif; ?>
            </div>
          </div>
        </a>
      <?php endforeach; ?>
    </div>

    <div style="text-align:center; margin-top:40px;">
      <a href="/boutique" class="h-btn h-btn-primary h-btn-lg">
        Tous nos produits
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
      </a>
    </div>
  </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
