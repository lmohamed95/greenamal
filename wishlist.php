<?php
require_once __DIR__ . '/includes/helpers.php';
customer_require_login('login.php');

$user = customer_user();

$items = db_all("
  SELECT w.product_id, p.slug, p.name, p.image_main, p.price, p.stock, c.name AS category_name
  FROM wishlists w
  JOIN products p ON p.id = w.product_id
  LEFT JOIN categories c ON c.id = p.category_id
  WHERE w.customer_id = ? AND p.status = 'active'
  ORDER BY w.created_at DESC
", [$user['id']]);

$page_title = 'Mes favoris';
$noindex    = true;
require __DIR__ . '/includes/header.php';
?>

<section class="container" style="padding:32px 16px;max-width:1080px;">
  <div class="breadcrumb"><a href="/">Accueil</a><span>/</span><a href="account.php">Mon compte</a><span>/</span><span>Favoris</span></div>
  <h1 style="margin:24px 0;">Mes favoris</h1>

  <?php if (!$items): ?>
    <div class="empty-state">
      <h2>Votre liste est vide</h2>
      <p>Cliquez sur le bouton <svg class="icon-inline" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20.84 4.61a5.5 5.5 0 00-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 00-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 000-7.78z"/></svg> d'un produit pour l'ajouter à vos favoris.</p>
      <a href="shop.php" class="btn btn-primary">Voir la boutique</a>
    </div>
  <?php else: ?>
    <div class="product-grid">
      <?php foreach ($items as $p): ?>
        <a href="product.php?slug=<?= e($p['slug']) ?>" class="product-card">
          <div class="product-card-img">
            <img src="<?= e($p['image_main']) ?>" alt="<?= e($p['name']) ?>" loading="lazy">
          </div>
          <div class="product-card-body">
            <div class="product-card-cat"><?= e($p['category_name'] ?? '') ?></div>
            <h3 class="product-card-name"><?= e($p['name']) ?></h3>
            <div class="product-card-price"><?= e(price((float) $p['price'])) ?></div>
            <span class="btn-view">
              Voir
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
            </span>
          </div>
        </a>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
