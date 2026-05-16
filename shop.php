<?php
require_once __DIR__ . '/includes/helpers.php';
require_once __DIR__ . '/includes/image.php';

$page_title = 'Boutique';
$nav        = 'shop';
$page_desc  = 'Boutique GreenAmal · tous nos produits naturels du Maroc : huiles essentielles, plantes, cosmétiques, couscous artisanal. Filtre par catégorie, prix et note.';

$selected_cat = $_GET['cat'] ?? null;
$search       = trim($_GET['q'] ?? '');
$sort         = $_GET['sort'] ?? 'recent';

$where = ["p.status = 'active'"];
$params = [];
if ($selected_cat) {
    $where[] = 'c.slug = ?';
    $params[] = $selected_cat;
}
if ($search !== '') {
    $where[] = '(p.name LIKE ? OR p.tags LIKE ?)';
    $params[] = '%' . $search . '%';
    $params[] = '%' . $search . '%';
}
$order = match($sort) {
    'price_asc'  => 'p.price ASC',
    'price_desc' => 'p.price DESC',
    'rating'     => 'p.rating_avg DESC',
    'sales'      => 'p.sales_count DESC',
    default      => 'p.created_at DESC',
};

$whereSql = 'WHERE ' . implode(' AND ', $where);

$products = db_all("
    SELECT p.*, c.slug AS category_slug, c.name AS category_name
    FROM products p
    LEFT JOIN categories c ON c.id = p.category_id
    $whereSql
    ORDER BY $order
", $params);

$total = count($products);
$categories = db_all("SELECT c.*, (SELECT COUNT(*) FROM products WHERE category_id = c.id AND status='active') AS pcount FROM categories c ORDER BY c.display_order ASC");
$total_active = (int) db_value("SELECT COUNT(*) FROM products WHERE status='active'");

// Dynamic SEO when filtered by category
if ($selected_cat) {
    $cat_row = db_one('SELECT name, description, image_url FROM categories WHERE slug = ?', [$selected_cat]);
    if ($cat_row) {
        $page_title = $cat_row['name'];
        $page_desc  = $cat_row['description'] ?: "Découvrez notre sélection de {$cat_row['name']} · produits naturels du Maroc, certifiés ONSSA.";
        if (!empty($cat_row['image_url'])) $og_image = $cat_row['image_url'];
    }
}

// Sorted listings → noindex (only canonical shop page should rank)
if (!empty($_GET['sort'])) {
    $noindex = true;
}

$jsonld = [seo_breadcrumb_jsonld(array_filter([
    ['Accueil', '/'],
    ['Boutique', '/shop'],
    $selected_cat ? [$cat_row['name'] ?? 'Catégorie', '/shop?cat=' . $selected_cat] : null,
]))];

require __DIR__ . '/includes/header.php';
?>

<div class="container breadcrumb">
  <a href="/">Accueil</a><span>/</span><span>Boutique</span>
  <?php if ($selected_cat): ?>
    <?php $cat_name = db_value('SELECT name FROM categories WHERE slug = ?', [$selected_cat]); ?>
    <span>/</span><span><?= e($cat_name) ?></span>
  <?php endif; ?>
</div>

<section style="padding-bottom: 24px;">
  <div class="container">
    <div style="text-align: center; max-width: 720px; margin: 0 auto 40px;">
      <span class="eyebrow">Notre boutique</span>
      <h1 style="margin-bottom: 12px;"><?= $selected_cat ? e($cat_name) : 'Tous nos produits naturels' ?></h1>
      <p style="color: var(--ink-soft);"><?= $total ?> produits authentiques, sélectionnés avec soin par la coopérative Al Amal.</p>
    </div>
  </div>
</section>

<section style="padding-bottom: 80px;">
  <div class="container">
    <div class="shop-layout">
      <aside class="shop-sidebar">
        <form method="get">
          <div class="filter-group">
            <h4>Catégories</h4>
            <label>
              <input type="radio" name="cat" value="" <?= !$selected_cat ? 'checked' : '' ?> onchange="this.form.submit()">
              Tous <span class="count"><?= $total_active ?></span>
            </label>
            <?php foreach ($categories as $cat): ?>
              <label>
                <input type="radio" name="cat" value="<?= e($cat['slug']) ?>" <?= $selected_cat === $cat['slug'] ? 'checked' : '' ?> onchange="this.form.submit()">
                <?= e($cat['name']) ?> <span class="count"><?= (int) $cat['pcount'] ?></span>
              </label>
            <?php endforeach; ?>
          </div>

          <div class="filter-group">
            <h4>Recherche</h4>
            <input type="text" name="q" value="<?= e($search) ?>" placeholder="Nom du produit..." class="field-input" style="width:100%; padding:8px 12px; border:1px solid var(--line); border-radius:var(--radius-sm);">
          </div>

          <button type="submit" class="btn btn-dark btn-block">Appliquer</button>
        </form>
      </aside>

      <div>
        <div class="shop-toolbar">
          <span class="results">Affichage de <strong>1–<?= $total ?></strong> sur <?= $total ?> résultats</span>
          <form method="get" style="display: inline;">
            <?php if ($selected_cat): ?><input type="hidden" name="cat" value="<?= e($selected_cat) ?>"><?php endif; ?>
            <?php if ($search): ?><input type="hidden" name="q" value="<?= e($search) ?>"><?php endif; ?>
            <select name="sort" onchange="this.form.submit()">
              <option value="recent" <?= $sort === 'recent' ? 'selected' : '' ?>>Trier : plus récents</option>
              <option value="price_asc" <?= $sort === 'price_asc' ? 'selected' : '' ?>>Prix croissant</option>
              <option value="price_desc" <?= $sort === 'price_desc' ? 'selected' : '' ?>>Prix décroissant</option>
              <option value="rating" <?= $sort === 'rating' ? 'selected' : '' ?>>Mieux notés</option>
              <option value="sales" <?= $sort === 'sales' ? 'selected' : '' ?>>Plus vendus</option>
            </select>
          </form>
        </div>

        <?php if ($total === 0): ?>
          <div style="text-align: center; padding: 80px 20px; background: var(--white); border-radius: var(--radius); border: 1px solid var(--line);">
            <h3>Aucun produit trouvé</h3>
            <p style="color: var(--ink-soft); margin-top: 8px;">Essayez d'élargir votre recherche.</p>
          </div>
        <?php else: ?>
          <div class="product-grid">
            <?php foreach ($products as $p): ?>
              <article class="product-card" data-product-id="<?= (int) $p['id'] ?>">
                <div class="product-image">
                  <?php if ($p['compare_at_price'] && $p['compare_at_price'] > $p['price']): ?>
                    <div class="product-tags">
                      <span class="product-tag sale">−<?= (int) round((1 - $p['price'] / $p['compare_at_price']) * 100) ?>%</span>
                    </div>
                  <?php endif; ?>
                  <div class="product-actions-quick">
                    <button class="quick-btn" aria-label="Favoris"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20.84 4.61a5.5 5.5 0 00-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 00-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 000-7.78z"/></svg></button>
                  </div>
                  <?= picture_tag($p['image_main'], $p['name'], [
                      'lazy'   => true,
                      'sizes'  => '(max-width: 720px) 50vw, 25vw',
                      'width'  => 800,
                      'height' => 800,
                  ]) ?>
                  <a href="product?slug=<?= e($p['slug']) ?>" class="add-to-cart-overlay" data-add-to-cart>
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 002 1.61h9.72a2 2 0 002-1.61L23 6H6"/></svg>
                    Ajouter au panier
                  </a>
                </div>
                <div class="product-info">
                  <span class="product-cat"><?= e($p['category_name']) ?></span>
                  <a href="product?slug=<?= e($p['slug']) ?>"><h4 class="product-name"><?= e($p['name']) ?></h4></a>
                  <div class="product-rating">
                    <span class="stars">★★★★★</span>
                    <span>(<?= (int) $p['rating_count'] ?>)</span>
                  </div>
                  <div class="product-price">
                    <span class="price-now"><?= price($p['price']) ?></span>
                    <?php if ($p['compare_at_price'] && $p['compare_at_price'] > $p['price']): ?>
                      <span class="price-was"><?= price($p['compare_at_price']) ?></span>
                    <?php endif; ?>
                  </div>
                  <a href="product?slug=<?= e($p['slug']) ?>" class="btn-view">
                    Voir
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
                  </a>
                </div>
              </article>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
