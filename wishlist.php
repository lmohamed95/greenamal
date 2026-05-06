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
      <p>Cliquez sur le ❤️ d'un produit pour l'ajouter à vos favoris.</p>
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
          </div>
        </a>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
