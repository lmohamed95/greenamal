<?php
require_once __DIR__ . '/includes/helpers.php';
require_once __DIR__ . '/includes/image.php';

$slug = $_GET['slug'] ?? '';
$product = db_one("
    SELECT p.*, c.slug AS category_slug, c.name AS category_name
    FROM products p
    LEFT JOIN categories c ON c.id = p.category_id
    WHERE p.slug = ? AND p.status='active'
    LIMIT 1
", [$slug]);

if (!$product) {
    http_response_code(404);
    $page_title = 'Produit introuvable';
    require __DIR__ . '/includes/header.php';
    echo '<div class="container" style="padding: 80px 0; text-align:center;"><h1>Produit introuvable</h1><p style="margin:20px 0;">Ce produit n\'existe pas ou a été retiré.</p><a href="shop.php" class="btn btn-primary">Voir la boutique</a></div>';
    require __DIR__ . '/includes/footer.php';
    exit;
}

$gallery = db_all("SELECT url FROM product_images WHERE product_id = ? ORDER BY display_order", [$product['id']]);
array_unshift($gallery, ['url' => $product['image_main']]);

// Pack composition (if any)
$components = product_components((int) $product['id']);
$components_value = pack_components_value($components);
$pack_savings = ($components_value > 0 && $components_value > (float) $product['price'])
    ? $components_value - (float) $product['price']
    : 0.0;

$cross_sells = db_all("
    SELECT p.*, c.name AS category_name
    FROM products p
    LEFT JOIN categories c ON c.id = p.category_id
    WHERE p.status='active' AND p.id != ?
    ORDER BY p.sales_count DESC
    LIMIT 4
", [$product['id']]);

$page_title = $product['meta_title'] ?: $product['name'];
$page_desc  = $product['meta_description'] ?: $product['description_short'];
$nav        = 'shop';
$og_type    = 'product';
$og_image   = $product['image_main'];
$extra_meta = sprintf(
    "<meta property=\"product:price:amount\" content=\"%s\">\n<meta property=\"product:price:currency\" content=\"MAD\">\n<meta property=\"product:availability\" content=\"%s\">",
    number_format((float) $product['price'], 2, '.', ''),
    $product['stock'] > 0 ? 'in stock' : 'out of stock'
);
$jsonld = [
    seo_product_jsonld($product, $product['category_name']),
    seo_breadcrumb_jsonld([
        ['Accueil', '/index.php'],
        ['Boutique', '/shop.php'],
        [$product['category_name'] ?: 'Catégorie', '/shop.php?cat=' . $product['category_slug']],
        [$product['name'], '/product.php?slug=' . $product['slug']],
    ]),
];

require __DIR__ . '/includes/header.php';
?>

<div class="container breadcrumb">
  <a href="index.php">Accueil</a><span>/</span>
  <a href="shop.php">Boutique</a><span>/</span>
  <?php if ($product['category_slug']): ?>
    <a href="shop.php?cat=<?= e($product['category_slug']) ?>"><?= e($product['category_name']) ?></a><span>/</span>
  <?php endif; ?>
  <span><?= e($product['name']) ?></span>
</div>

<section style="padding-bottom: 40px;">
  <div class="container">
    <div class="pdp" data-product-id="<?= (int) $product['id'] ?>">
      <div class="pdp-gallery">
        <div class="pdp-main-image">
          <?= picture_tag($product['image_main'], $product['name'] . ' · ' . ($product['category_name'] ?? ''), [
              'lazy'          => false,
              'fetchpriority' => 'high',
              'sizes'         => '(max-width: 900px) 100vw, 720px',
              'width'         => 1600,
              'height'        => 1600,
          ]) ?>
        </div>
        <div class="pdp-thumbs">
          <?php foreach ($gallery as $i => $img): ?>
            <div class="pdp-thumb<?= $i === 0 ? ' active' : '' ?>">
              <?= picture_tag($img['url'], $product['name'] . ' · vue ' . ($i + 1), [
                  'lazy'   => $i > 0,
                  'width'  => 200,
                  'height' => 200,
              ]) ?>
            </div>
          <?php endforeach; ?>
        </div>
      </div>

      <div class="pdp-info">
        <span class="product-cat"><?= e($product['category_name']) ?> · Coopérative Al Amal</span>
        <h1><?= e($product['name']) ?></h1>

        <div class="pdp-rating-row">
          <span class="stars">★★★★★</span>
          <span style="color: var(--ink-soft);"><?= number_format($product['rating_avg'], 1) ?> / 5</span>
          <a href="#reviews"><?= (int) $product['rating_count'] ?> avis vérifiés</a>
          <span style="color: var(--ink-mute);">·</span>
          <span style="color: var(--ink-soft);">+<?= (int) $product['sales_count'] ?> vendues</span>
        </div>

        <div class="pdp-price-row">
          <span class="pdp-price"><?= price($product['price']) ?></span>
          <?php if ($product['compare_at_price'] && $product['compare_at_price'] > $product['price']): ?>
            <span class="pdp-price-was"><?= price($product['compare_at_price']) ?></span>
            <span class="pdp-savings">Économisez <?= price($product['compare_at_price'] - $product['price']) ?></span>
          <?php endif; ?>
        </div>
        <p class="pdp-tax">TTC · Livraison calculée au paiement</p>

        <p class="pdp-description"><?= nl2br(e($product['description_short'])) ?></p>

        <?php [$stock_label, $stock_color] = stock_level((int) $product['stock'], (int) $product['low_stock_threshold']); ?>
        <div class="pdp-stock" style="color: var(--<?= $stock_color === 'success' ? 'success' : ($stock_color === 'warning' ? 'warning' : 'danger') ?>);">
          <span class="dot" style="background: var(--<?= $stock_color === 'success' ? 'success' : ($stock_color === 'warning' ? 'warning' : 'danger') ?>);"></span>
          <?= $stock_color === 'success' ? 'En stock · expédié sous 24h' : $stock_label ?>
        </div>

        <?php if ($product['stock'] <= 10 && $product['stock'] > 0): ?>
          <div class="pdp-urgency"><svg class="icon-inline" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M8.5 14.5A2.5 2.5 0 0011 12c0-1.38-.5-2-1-3-1.072-2.143-.224-4.054 2-6 .5 2.5 2 4.9 4 6.5 2 1.6 3 3.5 3 5.5a7 7 0 11-14 0c0-1.153.433-2.294 1-3a2.5 2.5 0 002.5 2.5z"/></svg>Plus que <strong><?= (int) $product['stock'] ?> en stock</strong> · commandez vite !</div>
        <?php endif; ?>

        <div class="pdp-actions">
          <div class="qty-selector">
            <button>−</button>
            <input type="text" value="1">
            <button>+</button>
          </div>
          <button class="btn btn-primary btn-lg" data-add-to-cart>
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 002 1.61h9.72a2 2 0 002-1.61L23 6H6"/></svg>
            Ajouter · <?= price($product['price']) ?>
          </button>
          <button class="btn-wishlist" aria-label="Favoris" data-wishlist data-product-id="<?= (int) $product['id'] ?>">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20.84 4.61a5.5 5.5 0 00-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 00-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 000-7.78z"/></svg>
          </button>
        </div>

        <!-- Mobile sticky add-to-cart -->
        <div class="pdp-sticky-mobile" aria-hidden="true">
          <div class="pdp-sticky-info">
            <strong class="pdp-sticky-price"><?= price($product['price']) ?></strong>
            <span class="pdp-sticky-name"><?= e($product['name']) ?></span>
          </div>
          <button class="btn btn-primary" data-add-to-cart>
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 002 1.61h9.72a2 2 0 002-1.61L23 6H6"/></svg>
            Ajouter
          </button>
        </div>

        <div class="pdp-trust">
          <div class="pdp-trust-item"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 7l9-4 9 4v10l-9 4-9-4V7z"/></svg> Livraison 24/48h</div>
          <div class="pdp-trust-item"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M9 12l2 2 4-4"/></svg> Certifié ONSSA</div>
          <div class="pdp-trust-item"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="6" width="20" height="12" rx="2"/><circle cx="12" cy="12" r="3"/></svg> Paiement à la livraison</div>
          <div class="pdp-trust-item"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 12a9 9 0 1018 0 9 9 0 00-18 0z"/><path d="M12 7v5l3 3"/></svg> Retour 14 jours</div>
        </div>

        <div class="pdp-meta">
          <div><strong>Référence :</strong> <?= e($product['sku']) ?></div>
          <div><strong>Catégorie :</strong> <?= e($product['category_name']) ?></div>
          <div><strong>Origine :</strong> Région d'Azrou, Maroc</div>
          <?php if ($product['tags']): ?>
            <div><strong>Tags :</strong> <?= e($product['tags']) ?></div>
          <?php endif; ?>
        </div>
      </div>
    </div>

    <?php if ($components): ?>
      <section class="pdp-pack">
        <div class="pdp-pack-head">
          <h2>Ce pack contient</h2>
          <?php if ($pack_savings > 0): ?>
            <span class="pdp-pack-savings">Économisez <?= price($pack_savings) ?> vs achat à l'unité</span>
          <?php endif; ?>
        </div>
        <ul class="pdp-pack-list">
          <?php foreach ($components as $c): ?>
            <li class="pdp-pack-item">
              <a href="product.php?slug=<?= e($c['slug']) ?>" class="pdp-pack-link">
                <div class="pdp-pack-thumb"><img src="<?= e($c['image_main']) ?>" alt="<?= e($c['name']) ?>" loading="lazy"></div>
                <div class="pdp-pack-info">
                  <strong><?= e($c['name']) ?></strong>
                  <span><?= e(price((float) $c['price'])) ?> · l'unité</span>
                </div>
                <span class="pdp-pack-qty">×&nbsp;<?= (int) $c['quantity'] ?></span>
              </a>
            </li>
          <?php endforeach; ?>
        </ul>
        <?php if ($components_value > 0): ?>
          <div class="pdp-pack-total">
            <span>Valeur à l'unité&nbsp;:&nbsp;<strong><?= price($components_value) ?></strong></span>
            <span class="pdp-pack-total-pack">Prix du pack&nbsp;:&nbsp;<strong><?= price((float) $product['price']) ?></strong></span>
          </div>
        <?php endif; ?>
      </section>
    <?php endif; ?>

    <!-- Tabs -->
    <div class="pdp-tabs">
      <div class="tabs-nav">
        <button class="tab-btn active" data-tab="tab-desc">Description</button>
        <button class="tab-btn" data-tab="tab-shipping">Livraison & retours</button>
        <button class="tab-btn" data-tab="tab-reviews">Avis (<?= (int) $product['rating_count'] ?>)</button>
      </div>

      <div class="tab-pane active" id="tab-desc">
        <p><?= nl2br(e($product['description_long'])) ?></p>
      </div>

      <div class="tab-pane" id="tab-shipping">
        <ul>
          <li><strong>Livraison standard (24-48h) :</strong> <?= price(SHIPPING_FEE) ?> · Gratuite dès <?= price(FREE_SHIPPING_THRESHOLD) ?></li>
          <li><strong>Paiement à la livraison (COD) :</strong> Disponible partout au Maroc</li>
          <li><strong>Retours :</strong> 14 jours pour changer d'avis, produit non ouvert</li>
        </ul>
      </div>

      <div class="tab-pane" id="tab-reviews">
        <p style="color: var(--ink-soft);">Note moyenne : <?= number_format($product['rating_avg'], 1) ?> / 5 · basé sur <?= (int) $product['rating_count'] ?> avis vérifiés.</p>
      </div>
    </div>

    <!-- Cross-sell -->
    <section style="margin-top: 80px;">
      <div class="section-head left">
        <div class="head-text"><span class="eyebrow">Vous pourriez aussi aimer</span><h2>Souvent achetés ensemble</h2></div>
      </div>
      <div class="product-grid">
        <?php foreach ($cross_sells as $p): ?>
          <article class="product-card" data-product-id="<?= (int) $p['id'] ?>">
            <div class="product-image">
              <?= picture_tag($p['image_main'], $p['name'], [
                  'lazy'   => true,
                  'sizes'  => '(max-width: 720px) 50vw, 25vw',
                  'width'  => 800,
                  'height' => 800,
              ]) ?>
              <a href="product.php?slug=<?= e($p['slug']) ?>" class="add-to-cart-overlay" data-add-to-cart>
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 002 1.61h9.72a2 2 0 002-1.61L23 6H6"/></svg>
                Ajouter
              </a>
            </div>
            <div class="product-info">
              <span class="product-cat"><?= e($p['category_name']) ?></span>
              <h4 class="product-name"><?= e($p['name']) ?></h4>
              <div class="product-rating"><span class="stars">★★★★★</span><span>(<?= (int) $p['rating_count'] ?>)</span></div>
              <div class="product-price"><span class="price-now"><?= price($p['price']) ?></span></div>
              <a href="product.php?slug=<?= e($p['slug']) ?>" class="btn-view">
                Voir
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
              </a>
            </div>
          </article>
        <?php endforeach; ?>
      </div>
    </section>
  </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
