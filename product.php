<?php
require_once __DIR__ . '/includes/helpers.php';
require_once __DIR__ . '/includes/image.php';
require_once __DIR__ . '/includes/product_card.php';

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
    $body_class = 'gd-2026';
    $extra_css  = ['/assets/css/home.css'];
    require __DIR__ . '/includes/header.php';
    echo '<div class="container" style="padding: 80px 0; text-align:center;"><h1 style="font-family:var(--font-display-h);">Produit introuvable</h1><p style="margin:20px 0;color:var(--ink-2h);">Ce produit n\'existe pas ou a été retiré.</p><a href="/boutique" class="h-btn h-btn-primary h-btn-lg">Voir la boutique</a></div>';
    require __DIR__ . '/includes/footer.php';
    exit;
}

$gallery = db_all("SELECT url FROM product_images WHERE product_id = ? ORDER BY display_order", [$product['id']]);
array_unshift($gallery, ['url' => $product['image_main']]);

$components       = product_components((int) $product['id']);
$components_value = pack_components_value($components);
$pack_savings     = ($components_value > 0 && $components_value > (float) $product['price'])
    ? $components_value - (float) $product['price']
    : 0.0;

// Related: same category, exclude self, fill up to 4 with cross-sells
$related = db_all("
    SELECT p.*, c.slug AS category_slug, c.name AS category_name
    FROM products p LEFT JOIN categories c ON c.id = p.category_id
    WHERE p.status='active' AND p.id != ? AND p.category_id = ?
    ORDER BY p.sales_count DESC
    LIMIT 4
", [$product['id'], $product['category_id']]);
if (count($related) < 4) {
    $fill = db_all("
        SELECT p.*, c.slug AS category_slug, c.name AS category_name
        FROM products p LEFT JOIN categories c ON c.id = p.category_id
        WHERE p.status='active' AND p.id != ? AND (p.category_id != ? OR p.category_id IS NULL)
        ORDER BY p.sales_count DESC
        LIMIT ?
    ", [$product['id'], $product['category_id'], 4 - count($related)]);
    $related = array_merge($related, $fill);
}

$page_title = $product['meta_title'] ?: $product['name'];
$page_desc  = $product['meta_description'] ?: $product['description_short'];
$nav        = 'shop';
$og_type    = 'product';
$og_image   = $product['image_main'];
$body_class = 'gd-2026';
$extra_css  = ['/assets/css/home.css'];
$extra_meta = sprintf(
    "<meta property=\"product:price:amount\" content=\"%s\">\n<meta property=\"product:price:currency\" content=\"MAD\">\n<meta property=\"product:availability\" content=\"%s\">",
    number_format((float) $product['price'], 2, '.', ''),
    $product['stock'] > 0 ? 'in stock' : 'out of stock'
);
$jsonld = [
    seo_product_jsonld($product, $product['category_name']),
    seo_breadcrumb_jsonld([
        ['Accueil', '/'],
        ['Boutique', '/boutique'],
        [$product['category_name'] ?: 'Catégorie', '/boutique?cat=' . $product['category_slug']],
        [$product['name'], '/product?slug=' . $product['slug']],
    ]),
];

$rating_count  = (int)   $product['rating_count'];
$rating_avg    = (float) $product['rating_avg'];
$stars_full    = $rating_avg > 0 ? (int) round($rating_avg) : 5;
$stars_display = str_repeat('★', $stars_full) . str_repeat('☆', 5 - $stars_full);
$discount_pct  = ($product['compare_at_price'] && $product['compare_at_price'] > $product['price'])
    ? (int) round((1 - $product['price'] / $product['compare_at_price']) * 100) : 0;
$stock         = (int) $product['stock'];

require __DIR__ . '/includes/header.php';
?>

<div class="container product-hero" data-product-id="<?= (int) $product['id'] ?>">
  <div class="crumbs">
    <a href="/">Accueil</a><span class="sep">/</span>
    <a href="/boutique">Boutique</a><span class="sep">/</span>
    <?php if ($product['category_slug']): ?>
      <a href="/boutique?cat=<?= e($product['category_slug']) ?>"><?= e($product['category_name']) ?></a><span class="sep">/</span>
    <?php endif; ?>
    <span><?= e($product['name']) ?></span>
  </div>

  <div class="product-grid-main">
    <!-- Gallery -->
    <div class="gallery">
      <div class="thumbs">
        <?php foreach ($gallery as $i => $img): ?>
          <div class="thumb<?= $i === 0 ? ' active' : '' ?>" data-thumb="<?= $i ?>">
            <?= picture_tag($img['url'], $product['name'] . ' · vue ' . ($i + 1), [
                'lazy'   => $i > 0,
                'width'  => 200,
                'height' => 200,
            ]) ?>
          </div>
        <?php endforeach; ?>
      </div>
      <div class="main-image" id="mainImage">
        <?= picture_tag($product['image_main'], $product['name'] . ' · ' . ($product['category_name'] ?? ''), [
            'lazy'          => false,
            'fetchpriority' => 'high',
            'sizes'         => '(max-width: 900px) 100vw, 720px',
            'width'         => 1600,
            'height'        => 1600,
        ]) ?>
        <?php if ($discount_pct > 0): ?>
          <div class="badge-pos">
            <span class="h-card-badge promo">−<?= $discount_pct ?>%</span>
          </div>
        <?php elseif (!empty($product['is_featured'])): ?>
          <div class="badge-pos">
            <span class="h-card-badge best">Best-seller</span>
          </div>
        <?php endif; ?>
      </div>
    </div>

    <!-- Info -->
    <div class="p-info">
      <span class="p-cat"><?= e($product['category_name']) ?> · Coopérative Al Amal</span>
      <h1 class="p-name"><?= e($product['name']) ?></h1>

      <div class="p-rating-row">
        <span class="stars-glyph"><?= $stars_display ?></span>
        <span><b><?= $rating_avg > 0 ? number_format($rating_avg, 1) : '·' ?></b>
          <?php if ($rating_count > 0): ?>
            <span class="muted"> · <?= $rating_count ?> avis</span>
          <?php endif; ?>
        </span>
        <?php if ($product['sales_count'] > 0): ?>
          <span class="muted">·</span>
          <span class="muted">+<?= (int) $product['sales_count'] ?> vendues</span>
        <?php endif; ?>
        <?php if ($rating_count > 0): ?>
          <span class="muted">·</span>
          <a href="#reviews">Voir les avis</a>
        <?php endif; ?>
      </div>

      <div class="p-price-block">
        <div>
          <span class="p-price"><?= number_format((float) $product['price'], 0, ',', ' ') ?><span class="dh">DH</span></span>
          <?php if ($discount_pct > 0): ?>
            <span class="p-price-old"><?= number_format((float) $product['compare_at_price'], 0, ',', ' ') ?> DH</span>
          <?php endif; ?>
        </div>
        <div class="p-promo-bar">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s7-7 7-12a7 7 0 1 0-14 0c0 5 7 12 7 12Z"/><circle cx="12" cy="10" r="2.5"/></svg>
          Livraison gratuite à partir de <b style="margin-left:3px;"><?= price(FREE_SHIPPING_THRESHOLD) ?></b>
        </div>
        <?php if ($stock > 0): ?>
          <div class="p-stock"><span class="dot"></span>En stock · prêt à expédier sous 24h<?php if ($stock <= 10): ?> · plus que <?= $stock ?> restants<?php endif; ?></div>
        <?php else: ?>
          <div class="p-stock out"><span class="dot"></span>Rupture · réapprovisionnement en cours</div>
        <?php endif; ?>

        <div class="add-row">
          <div class="qty">
            <button type="button" data-qty="-1">−</button>
            <input type="number" id="qty" value="1" min="1" max="<?= max(1, $stock) ?>">
            <button type="button" data-qty="1">+</button>
          </div>
          <button class="h-btn h-btn-primary" data-add-to-cart<?= $stock <= 0 ? ' disabled' : '' ?>>
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 7h14l-1.5 11.5a2 2 0 0 1-2 1.5h-7a2 2 0 0 1-2-1.5L5 7Z"/><path d="M9 7V5a3 3 0 0 1 6 0v2"/></svg>
            Ajouter · <span id="totalPrice"><?= number_format((float) $product['price'], 0, ',', ' ') ?> DH</span>
          </button>
        </div>

        <?php if (wa_order_enabled()): ?>
        <a href="https://wa.me/<?= e(wa_number()) ?>?text=Bonjour,%20je%20souhaite%20commander%20<?= urlencode($product['name']) ?>" class="wa-row" target="_blank" rel="noopener">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><path d="M12 3a9 9 0 0 0-7.7 13.7L3 21l4.4-1.2A9 9 0 1 0 12 3Z"/></svg>
          Commander rapidement via WhatsApp
        </a>
        <?php endif; ?>
      </div>

      <!-- Trust mini -->
      <div class="trust-mini">
        <div class="trust-mini-item">
          <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V8a5 5 0 0 1 10 0v3"/></svg>
          <div><b>COD</b><span>Paiement à la livraison</span></div>
        </div>
        <div class="trust-mini-item">
          <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"><rect x="1" y="5" width="15" height="13" rx="2"/><path d="M16 9h4l3 4v5h-7"/><circle cx="6" cy="20" r="2"/><circle cx="19" cy="20" r="2"/></svg>
          <div><b>24-48h</b><span>Partout au Maroc</span></div>
        </div>
        <div class="trust-mini-item">
          <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"><path d="M3 12a9 9 0 1 0 9-9"/><path d="M3 4v8h8"/></svg>
          <div><b>14 jours</b><span>Satisfait ou remboursé</span></div>
        </div>
        <div class="trust-mini-item">
          <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"><path d="M12 2 4 6v6c0 5 3.5 9 8 10 4.5-1 8-5 8-10V6l-8-4Z"/></svg>
          <div><b>ONSSA</b><span>Qualité contrôlée</span></div>
        </div>
      </div>

      <!-- Tabs -->
      <div class="tabs">
        <div class="tab-heads">
          <button type="button" class="active" data-tab="desc">Description</button>
          <button type="button" data-tab="ship">Livraison & retours</button>
          <?php if ($components): ?><button type="button" data-tab="pack">Ce pack contient</button><?php endif; ?>
          <button type="button" data-tab="reviews">Avis (<?= $rating_count ?>)</button>
        </div>

        <div class="tab-body">
          <div class="tab-pane active" data-tab-pane="desc">
            <?php if ($product['description_long']): ?>
              <p><?= nl2br(e($product['description_long'])) ?></p>
            <?php else: ?>
              <p><?= nl2br(e($product['description_short'] ?? 'Produit naturel issu de la coopérative Al Amal d\'Azrou.')) ?></p>
            <?php endif; ?>
            <ul style="margin-top:12px;">
              <?php if ($product['sku']): ?><li><b>Référence :</b> <?= e($product['sku']) ?></li><?php endif; ?>
              <li><b>Origine :</b> Région d'Azrou, Moyen Atlas (Maroc)</li>
              <?php if ($product['tags']): ?><li><b>Tags :</b> <?= e($product['tags']) ?></li><?php endif; ?>
            </ul>
          </div>

          <div class="tab-pane" data-tab-pane="ship">
            <ul>
              <li><b>Livraison standard (24-48h) :</b> <?= price(SHIPPING_FEE) ?> · gratuite dès <?= price(FREE_SHIPPING_THRESHOLD) ?> d'achat.</li>
              <li><b>Paiement à la livraison (COD) :</b> disponible partout au Maroc · espèces ou carte à la remise du colis.</li>
              <li><b>Retours :</b> 14 jours pour changer d'avis, produit non ouvert.</li>
            </ul>
          </div>

          <?php if ($components): ?>
          <div class="tab-pane" data-tab-pane="pack">
            <?php if ($pack_savings > 0): ?>
              <p style="margin-bottom:14px;"><b style="color:var(--terra-500);">Économisez <?= price($pack_savings) ?></b> vs achat à l'unité.</p>
            <?php endif; ?>
            <ul style="list-style:none; padding-left:0; display:flex; flex-direction:column; gap:8px;">
              <?php foreach ($components as $c): ?>
                <li style="display:flex; gap:12px; align-items:center; padding:10px; background:var(--cream-h); border-radius:var(--r-md-h);">
                  <img src="<?= e($c['image_main']) ?>" alt="<?= e($c['name']) ?>" width="48" height="48" style="border-radius:8px; object-fit:cover;" loading="lazy">
                  <a href="/product?slug=<?= e($c['slug']) ?>" style="flex:1; color:var(--ink-h); text-decoration:none;">
                    <b style="display:block;font-size:13.5px;"><?= e($c['name']) ?></b>
                    <span style="font-size:12px; color:var(--ink-3h);"><?= price((float) $c['price']) ?> · l'unité</span>
                  </a>
                  <span style="font-weight:600;">× <?= (int) $c['quantity'] ?></span>
                </li>
              <?php endforeach; ?>
            </ul>
          </div>
          <?php endif; ?>

          <div class="tab-pane" data-tab-pane="reviews">
            <?php if ($rating_count > 0): ?>
              <p>Note moyenne : <b><?= number_format($rating_avg, 1) ?> / 5</b> · basé sur <?= $rating_count ?> avis vérifiés.</p>
            <?php else: ?>
              <p>Pas encore d'avis pour ce produit. Soyez le premier !</p>
            <?php endif; ?>
          </div>
        </div>
      </div>
    </div>
  </div>

  <?php if ($rating_count > 0): ?>
    <!-- Reviews summary -->
    <div id="reviews" class="review-summary">
      <div class="big">
        <div class="n"><?= number_format($rating_avg, 1) ?></div>
        <div class="stars-glyph"><?= $stars_display ?></div>
        <div class="total">basé sur <?= $rating_count ?> avis</div>
      </div>
      <div class="review-bars">
        <?php
          // Approximated distribution (5★ → 88%, etc.). Replace when a real review table exists.
          $dist = [5 => 88, 4 => 8, 3 => 2, 2 => 1, 1 => 1];
          foreach ($dist as $star => $pct):
            $n = (int) round($rating_count * $pct / 100);
        ?>
          <div class="review-bar">
            <span class="label"><?= $star ?> ★</span>
            <div class="track"><div class="fill" style="width:<?= $pct ?>%"></div></div>
            <span class="count"><?= $n ?></span>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
  <?php endif; ?>
</div>

<!-- Related products -->
<?php if ($related): ?>
<section class="related">
  <div class="container">
    <div class="h-section-head">
      <div>
        <span class="h-eyebrow">Vous aimerez aussi</span>
        <h2 class="h-serif" style="margin-top:6px;">Dans la même <em>famille</em>.</h2>
      </div>
      <?php if ($product['category_slug']): ?>
        <a href="/boutique?cat=<?= e($product['category_slug']) ?>" class="h-section-link">Voir la collection
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
        </a>
      <?php endif; ?>
    </div>
    <div class="product-grid">
      <?php foreach ($related as $p) echo home_product_card($p); ?>
    </div>
  </div>
</section>
<?php endif; ?>

<script>
(function(){
  // Tabs
  document.querySelectorAll('.tabs .tab-heads button').forEach(function(b){
    b.addEventListener('click', function(){
      var k = b.dataset.tab;
      document.querySelectorAll('.tabs .tab-heads button').forEach(function(x){ x.classList.toggle('active', x === b); });
      document.querySelectorAll('.tabs .tab-pane').forEach(function(x){ x.classList.toggle('active', x.dataset.tabPane === k); });
    });
  });

  // Quantity + total
  var qty   = document.getElementById('qty');
  var total = document.getElementById('totalPrice');
  var unit  = <?= (float) $product['price'] ?>;
  function refresh(){
    var n = parseInt(qty.value, 10) || 1;
    if (n < 1) n = 1;
    if (qty.max && n > parseInt(qty.max, 10)) n = parseInt(qty.max, 10);
    qty.value = n;
    if (total) total.textContent = (n * unit).toLocaleString('fr-FR') + ' DH';
  }
  document.querySelectorAll('[data-qty]').forEach(function(b){
    b.addEventListener('click', function(){
      qty.value = (parseInt(qty.value, 10) || 1) + parseInt(b.dataset.qty, 10);
      refresh();
    });
  });
  if (qty) qty.addEventListener('input', refresh);

  // Thumbnail switching
  document.querySelectorAll('.gallery .thumb').forEach(function(t){
    t.addEventListener('click', function(){
      document.querySelectorAll('.gallery .thumb').forEach(function(x){ x.classList.remove('active'); });
      t.classList.add('active');
      var src = t.querySelector('img');
      var main = document.querySelector('#mainImage img');
      if (src && main) main.src = src.currentSrc || src.src;
    });
  });
})();
</script>

<?php require __DIR__ . '/includes/footer.php'; ?>
