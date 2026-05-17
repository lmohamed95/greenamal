<?php
require_once __DIR__ . '/includes/helpers.php';
require_once __DIR__ . '/includes/image.php';
require_once __DIR__ . '/includes/product_card.php';

$page_title = 'Boutique';
$nav        = 'shop';
$page_desc  = 'Boutique GreenAmal · tous nos produits naturels du Maroc : huiles essentielles, plantes, cosmétiques, couscous artisanal. Filtre par catégorie, prix et note.';
$body_class = 'gd-2026';
$extra_css  = ['/assets/css/home.css'];

$selected_cat = $_GET['cat'] ?? null;
$search       = trim($_GET['q'] ?? '');
$sort         = $_GET['sort'] ?? 'recent';

$where  = ["p.status = 'active'"];
$params = [];
if ($selected_cat) {
    $where[]  = 'c.slug = ?';
    $params[] = $selected_cat;
}
if ($search !== '') {
    $where[]  = '(p.name LIKE ? OR p.tags LIKE ?)';
    $params[] = '%' . $search . '%';
    $params[] = '%' . $search . '%';
}

$order = match($sort) {
    'price_asc'  => 'p.price ASC',
    'price_desc' => 'p.price DESC',
    'rating'     => 'p.rating_avg DESC',
    'sales'      => 'p.sales_count DESC',
    'best'       => 'p.is_featured DESC, p.sales_count DESC',
    'new'        => 'p.created_at DESC, p.id DESC',
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

$total        = count($products);
$categories   = db_all("SELECT c.*, (SELECT COUNT(*) FROM products WHERE category_id = c.id AND status='active') AS pcount FROM categories c ORDER BY c.display_order ASC");
$total_active = (int) db_value("SELECT COUNT(*) FROM products WHERE status='active'");

$cat_name = null;
$cat_row  = null;
if ($selected_cat) {
    $cat_row = db_one('SELECT name, description, image_url FROM categories WHERE slug = ?', [$selected_cat]);
    if ($cat_row) {
        $cat_name   = $cat_row['name'];
        $page_title = $cat_row['name'];
        $page_desc  = $cat_row['description'] ?: "Découvrez notre sélection de {$cat_row['name']} · produits naturels du Maroc, certifiés ONSSA.";
        if (!empty($cat_row['image_url'])) $og_image = $cat_row['image_url'];
    }
}
if (!empty($_GET['sort'])) $noindex = true;

$jsonld = [seo_breadcrumb_jsonld(array_filter([
    ['Accueil', '/'],
    ['Boutique', '/boutique'],
    $selected_cat ? [$cat_name ?? 'Catégorie', '/boutique?cat=' . $selected_cat] : null,
]))];

// Category rail theming (matches index.php)
$CAT_ICONS = [
    'huiles-essentielles' => '🌿',
    'huiles-vegetales'    => '🫒',
    'eau-florale'         => '🌸',
    'pam'                 => '🪴',
    'couscous'            => '🌾',
    'farine'              => '🌾',
    'poudres'             => '🧂',
    'savons'              => '🧼',
    'packs'               => '🎁',
    'divers'              => '✨',
];

require __DIR__ . '/includes/header.php';
?>

<!-- Sticky category rail -->
<div class="cat-rail">
  <div class="cat-rail-scroll">
    <a href="/boutique" class="cat-chip<?= !$selected_cat ? ' active' : '' ?>">
      <span class="chip-icon">✦</span>
      Tout
      <span class="count"><?= $total_active ?></span>
    </a>
    <?php foreach ($categories as $cat): ?>
      <a href="/boutique?cat=<?= e($cat['slug']) ?>" class="cat-chip<?= $selected_cat === $cat['slug'] ? ' active' : '' ?>">
        <span class="chip-icon"><?= $CAT_ICONS[$cat['slug']] ?? '🌿' ?></span>
        <?= e($cat['name']) ?>
        <span class="count"><?= (int) $cat['pcount'] ?></span>
      </a>
    <?php endforeach; ?>
  </div>
</div>

<section class="shop-head">
  <div class="container">
    <div class="crumbs">
      <a href="/">Accueil</a><span class="sep">/</span>
      <?php if ($selected_cat): ?>
        <a href="/boutique">Boutique</a><span class="sep">/</span><span><?= e($cat_name) ?></span>
      <?php else: ?>
        <span>Boutique</span>
      <?php endif; ?>
    </div>
    <?php if ($selected_cat): ?>
      <h1><?= e($cat_name) ?></h1>
    <?php else: ?>
      <h1>Tous nos <em style="font-style:italic;color:var(--terra-500);font-weight:400;">produits</em> naturels.</h1>
    <?php endif; ?>
    <div class="shop-meta">
      <div class="count"><?= $total ?> produit<?= $total > 1 ? 's' : '' ?></div>
      <?php if ($selected_cat || $search !== ''): ?>
        <div class="active-filters">
          <?php if ($selected_cat): ?>
            <a href="/boutique<?= $search !== '' ? '?q=' . urlencode($search) : '' ?>" class="active-pill"><?= e($cat_name) ?> <span class="x">×</span></a>
          <?php endif; ?>
          <?php if ($search !== ''): ?>
            <a href="/boutique<?= $selected_cat ? '?cat=' . urlencode($selected_cat) : '' ?>" class="active-pill">« <?= e($search) ?> » <span class="x">×</span></a>
          <?php endif; ?>
        </div>
      <?php endif; ?>
    </div>
  </div>
</section>

<div class="container">
  <div class="shop-layout">
    <!-- Sidebar filters -->
    <aside class="filters">
      <form method="get" id="shopFilters">
        <?php if ($search !== ''): ?><input type="hidden" name="q" value="<?= e($search) ?>"><?php endif; ?>
        <?php if (!empty($_GET['sort']) && $sort !== 'recent'): ?><input type="hidden" name="sort" value="<?= e($sort) ?>"><?php endif; ?>

        <div class="filter-group">
          <h4>Catégories</h4>
          <div class="filter-cats">
            <a href="/boutique<?= $search !== '' ? '?q=' . urlencode($search) : '' ?>" class="<?= !$selected_cat ? 'active' : '' ?>">
              Tous les produits <span class="ct"><?= $total_active ?></span>
            </a>
            <?php foreach ($categories as $cat): ?>
              <a href="/boutique?cat=<?= e($cat['slug']) ?><?= $search !== '' ? '&q=' . urlencode($search) : '' ?>" class="<?= $selected_cat === $cat['slug'] ? 'active' : '' ?>">
                <?= e($cat['name']) ?> <span class="ct"><?= (int) $cat['pcount'] ?></span>
              </a>
            <?php endforeach; ?>
          </div>
        </div>

        <div class="filter-group">
          <h4>Disponibilité</h4>
          <label class="checkbox-row"><input type="checkbox" checked> En stock</label>
          <label class="checkbox-row"><input type="checkbox"> Best-sellers</label>
          <label class="checkbox-row"><input type="checkbox"> Nouveautés</label>
          <label class="checkbox-row"><input type="checkbox"> Promotions</label>
        </div>

        <div class="filter-group">
          <h4>Certifications</h4>
          <label class="checkbox-row"><input type="checkbox"> ONSSA</label>
          <label class="checkbox-row"><input type="checkbox"> Bio</label>
          <label class="checkbox-row"><input type="checkbox"> Commerce équitable</label>
        </div>
      </form>
    </aside>

    <!-- Product grid -->
    <div>
      <div class="shop-toolbar">
        <form method="get" class="search-input" style="margin:0;">
          <?php if ($selected_cat): ?><input type="hidden" name="cat" value="<?= e($selected_cat) ?>"><?php endif; ?>
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5"/></svg>
          <input type="search" name="q" value="<?= e($search) ?>" placeholder="Rechercher un produit…">
        </form>

        <form method="get" class="sort-select" style="margin:0;">
          <?php if ($selected_cat): ?><input type="hidden" name="cat" value="<?= e($selected_cat) ?>"><?php endif; ?>
          <?php if ($search !== ''): ?><input type="hidden" name="q" value="<?= e($search) ?>"><?php endif; ?>
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 6h18M6 12h12M9 18h6"/></svg>
          <span style="color:var(--ink-3h);">Trier&nbsp;:</span>
          <select name="sort" onchange="this.form.submit()">
            <option value="recent"     <?= $sort === 'recent'     ? 'selected' : '' ?>>Plus récents</option>
            <option value="best"       <?= $sort === 'best'       ? 'selected' : '' ?>>Best-sellers</option>
            <option value="price_asc"  <?= $sort === 'price_asc'  ? 'selected' : '' ?>>Prix croissant</option>
            <option value="price_desc" <?= $sort === 'price_desc' ? 'selected' : '' ?>>Prix décroissant</option>
            <option value="rating"     <?= $sort === 'rating'     ? 'selected' : '' ?>>Mieux notés</option>
            <option value="sales"      <?= $sort === 'sales'      ? 'selected' : '' ?>>Plus vendus</option>
          </select>
        </form>
      </div>

      <?php if ($total === 0): ?>
        <div class="empty-state" style="padding:80px 20px; background:var(--paper); border-radius:var(--r-lg-h); border:1px solid var(--line-2h);">
          <h3 style="font-family:var(--font-display-h); font-size:1.5rem; margin:0 0 8px;">Aucun produit trouvé</h3>
          <p style="margin:0;">Essayez d'élargir votre recherche ou de retirer un filtre.</p>
        </div>
      <?php else: ?>
        <div class="product-grid">
          <?php foreach ($products as $p) echo home_product_card($p); ?>
        </div>
      <?php endif; ?>
    </div>
  </div>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
