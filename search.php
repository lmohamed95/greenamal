<?php
require_once __DIR__ . '/includes/helpers.php';
require_once __DIR__ . '/includes/product_card.php';

$q = trim((string) ($_GET['q'] ?? ''));
$results = [];
if ($q !== '' && mb_strlen($q) >= 2) {
    $like = '%' . $q . '%';
    $results = db_all(
        "SELECT p.id, p.slug, p.name, p.price, p.compare_at_price, p.image_main, p.description_short,
                p.stock, p.is_featured, p.rating_avg, p.rating_count, p.created_at,
                c.name AS category_name
         FROM products p
         LEFT JOIN categories c ON c.id = p.category_id
         WHERE p.status = 'active'
           AND (p.name LIKE ? OR p.description_short LIKE ? OR p.description_long LIKE ? OR p.tags LIKE ? OR p.sku LIKE ?)
         ORDER BY (p.name LIKE ?) DESC, p.sales_count DESC
         LIMIT 50",
        [$like, $like, $like, $like, $like, $q . '%']
    );
}

$page_title = $q !== '' ? "Recherche : « {$q} »" : 'Recherche';
$page_desc  = 'Recherchez parmi plus de 80 produits naturels du Maroc · huiles, plantes, cosmétiques, couscous artisanal.';
$noindex    = $q === '';
$body_class = 'gd-2026';
$extra_css  = ['/assets/css/home.css'];
require __DIR__ . '/includes/header.php';
?>

<section class="static-page">
  <div class="container">
    <div class="crumbs"><a href="/">Accueil</a><span class="sep">/</span><span>Recherche</span></div>

    <div class="static-head">
      <span class="h-eyebrow">Trouvez votre produit</span>
      <h1 class="h-serif">Rechercher</h1>
    </div>

    <form method="get" class="search-box" role="search">
      <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
      <input type="search" name="q" value="<?= e($q) ?>" placeholder="Rechercher un produit, une catégorie…" autofocus required minlength="2">
      <button type="submit" class="h-btn h-btn-primary">Rechercher</button>
    </form>

    <?php if ($q === ''): ?>
      <div class="search-empty">
        <p>Tapez un mot-clé&nbsp;: <em>argan, couscous, lavande, savon, henné…</em></p>
      </div>
    <?php elseif (count($results) === 0): ?>
      <div class="cart-empty">
        <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.4"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
        <h2 class="h-serif">Aucun résultat pour « <?= e($q) ?> »</h2>
        <p>Essayez un autre mot-clé ou parcourez nos <a href="/categories" class="auth-link">catégories</a>.</p>
      </div>
    <?php else: ?>
      <p class="muted" style="margin: 12px 0 18px;"><?= count($results) ?> résultat<?= count($results) > 1 ? 's' : '' ?> pour « <?= e($q) ?> »</p>
      <div class="product-grid">
        <?php foreach ($results as $p) echo home_product_card($p); ?>
      </div>
    <?php endif; ?>
  </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
