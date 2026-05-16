<?php
require_once __DIR__ . '/includes/helpers.php';

$q = trim((string) ($_GET['q'] ?? ''));
$results = [];
if ($q !== '' && mb_strlen($q) >= 2) {
    $like = '%' . $q . '%';
    $results = db_all(
        "SELECT p.id, p.slug, p.name, p.price, p.image_main, p.description_short, p.stock, c.name AS category_name
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
require __DIR__ . '/includes/header.php';
?>

<section class="container" style="padding:32px 16px;">
  <div class="breadcrumb"><a href="/">Accueil</a><span>/</span><span>Recherche</span></div>

  <form method="get" class="search-form" role="search">
    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
    <input type="search" name="q" value="<?= e($q) ?>" placeholder="Rechercher un produit, une catégorie…" autofocus required minlength="2">
    <button type="submit" class="btn btn-primary">Rechercher</button>
  </form>

  <?php if ($q === ''): ?>
    <div style="padding:48px 0;text-align:center;color:var(--ink-soft);">
      <p>Tapez un mot-clé : <em>argan, couscous, lavande, savon, henné…</em></p>
    </div>
  <?php elseif (count($results) === 0): ?>
    <div style="padding:48px 0;text-align:center;">
      <h2>Aucun résultat pour « <?= e($q) ?> »</h2>
      <p style="color:var(--ink-soft);">Essayez un autre mot-clé ou parcourez nos <a href="categories">catégories</a>.</p>
    </div>
  <?php else: ?>
    <h1 style="font-size:1.4rem;margin:24px 0;"><?= count($results) ?> résultat<?= count($results) > 1 ? 's' : '' ?> pour « <?= e($q) ?> »</h1>
    <div class="product-grid">
      <?php foreach ($results as $p): ?>
        <a href="product?slug=<?= e($p['slug']) ?>" class="product-card">
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
