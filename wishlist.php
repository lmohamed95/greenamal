<?php
require_once __DIR__ . '/includes/helpers.php';
require_once __DIR__ . '/includes/product_card.php';
customer_require_login('login');

$user = customer_user();

$items = db_all("
  SELECT w.product_id, p.id, p.slug, p.name, p.image_main, p.price, p.compare_at_price, p.stock,
         p.is_featured, p.rating_avg, p.rating_count, p.created_at, c.name AS category_name
  FROM wishlists w
  JOIN products p ON p.id = w.product_id
  LEFT JOIN categories c ON c.id = p.category_id
  WHERE w.customer_id = ? AND p.status = 'active'
  ORDER BY w.created_at DESC
", [$user['id']]);

$page_title = 'Mes favoris';
$noindex    = true;
$body_class = 'gd-2026';
$extra_css  = ['/assets/css/home.css'];
require __DIR__ . '/includes/header.php';
?>

<section class="static-page">
  <div class="container">
    <div class="crumbs">
      <a href="/">Accueil</a><span class="sep">/</span>
      <a href="/mon-compte">Mon compte</a><span class="sep">/</span>
      <span>Favoris</span>
    </div>

    <div class="static-head">
      <span class="h-eyebrow">Vos coups de cœur</span>
      <h1 class="h-serif">Mes <em>favoris</em>.</h1>
    </div>

    <?php if (!$items): ?>
      <div class="cart-empty">
        <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.4"><path d="M12 21s-7-4.5-7-10a4 4 0 0 1 7-2.6A4 4 0 0 1 19 11c0 5.5-7 10-7 10Z"/></svg>
        <h2 class="h-serif">Votre liste est vide</h2>
        <p>Cliquez sur le cœur d'un produit pour l'ajouter à vos favoris.</p>
        <a href="/boutique" class="h-btn h-btn-primary h-btn-lg">
          Voir la boutique
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
        </a>
      </div>
    <?php else: ?>
      <div class="product-grid">
        <?php foreach ($items as $p) echo home_product_card($p); ?>
      </div>
    <?php endif; ?>
  </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
