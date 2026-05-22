<?php
require_once __DIR__ . '/../includes/auth.php';
admin_require_login();

$page_title = 'Catégories';
$current = 'categories';

// Handle delete (only if no products) — POST + CSRF only.
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete'])) {
    csrf_verify();
    $del_id = (int) $_POST['delete'];
    $pcount = (int) db_value('SELECT COUNT(*) FROM products WHERE category_id = ?', [$del_id]);
    if ($pcount === 0) {
        db_query('DELETE FROM categories WHERE id = ?', [$del_id]);
        redirect('categories.php?deleted=1');
    } else {
        redirect('categories.php?error=has_products');
    }
}

$categories = db_all("
    SELECT c.*, (SELECT COUNT(*) FROM products WHERE category_id = c.id) AS product_count
    FROM categories c
    ORDER BY c.display_order ASC
");

require __DIR__ . '/_includes/header.php';
?>

<div class="page">
  <div class="breadcrumb-admin"><a href="index.php">Tableau de bord</a><span>/</span><span>Catégories</span></div>

  <div class="page-head">
    <div>
      <h1>Catégories</h1>
      <p><?= count($categories) ?> catégories dans le catalogue. L'image bannière est utilisée sur la page Catégories du site et dans les filtres.</p>
    </div>
    <div class="page-actions">
      <a href="category-edit.php" class="btn btn-primary">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
        Nouvelle catégorie
      </a>
    </div>
  </div>

  <?php if (!empty($_GET['deleted'])): ?>
    <div style="background: var(--success-bg); color: var(--success); padding: 12px 18px; border-radius: var(--radius-sm); margin-bottom: 18px; font-size: 0.88rem;">✓ Catégorie supprimée.</div>
  <?php endif; ?>
  <?php if (!empty($_GET['error']) && $_GET['error'] === 'has_products'): ?>
    <div style="background: var(--danger-bg); color: var(--danger); padding: 12px 18px; border-radius: var(--radius-sm); margin-bottom: 18px; font-size: 0.88rem;"><svg class="icon-inline" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10.29 3.86 1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>Impossible de supprimer : cette catégorie contient des produits. Réassignez-les d'abord.</div>
  <?php endif; ?>

  <div class="categories-admin-grid">
    <?php foreach ($categories as $cat): ?>
      <div class="cat-admin-card">
        <div class="cat-admin-image">
          <?php if (!empty($cat['image_url'])): ?>
            <img src="<?= e($cat['image_url']) ?>" alt="<?= e($cat['name']) ?>" loading="lazy">
          <?php else: ?>
            <div class="cat-admin-image-empty">
              <svg width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>
              <span>Pas d'image</span>
            </div>
          <?php endif; ?>
          <span class="cat-admin-order">#<?= (int) $cat['display_order'] ?></span>
        </div>
        <div class="cat-admin-body">
          <h3><?= e($cat['name']) ?></h3>
          <p class="cat-admin-slug">/shop.php?cat=<?= e($cat['slug']) ?></p>
          <?php if (!empty($cat['description'])): ?>
            <p class="cat-admin-desc"><?= e($cat['description']) ?></p>
          <?php endif; ?>
          <div class="cat-admin-meta">
            <span class="badge-status status-info"><?= (int) $cat['product_count'] ?> produit<?= $cat['product_count'] > 1 ? 's' : '' ?></span>
          </div>
        </div>
        <div class="cat-admin-actions">
          <a href="category-edit.php?id=<?= (int) $cat['id'] ?>" class="btn btn-outline btn-sm">
            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
            Modifier
          </a>
          <a href="../shop.php?cat=<?= e($cat['slug']) ?>" target="_blank" class="btn btn-ghost btn-sm">
            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 13v6a2 2 0 01-2 2H5a2 2 0 01-2-2V8a2 2 0 012-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>
            Voir
          </a>
          <?php if ((int) $cat['product_count'] === 0): ?>
            <form method="post" action="categories.php" onsubmit="return confirm('Supprimer cette catégorie ?')" style="margin: 0 0 0 auto;">
              <?= csrf_field() ?>
              <input type="hidden" name="delete" value="<?= (int) $cat['id'] ?>">
              <button type="submit" class="btn btn-ghost btn-sm" style="color: var(--danger);">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-2 14a2 2 0 01-2 2H9a2 2 0 01-2-2L5 6"/></svg>
              </button>
            </form>
          <?php endif; ?>
        </div>
      </div>
    <?php endforeach; ?>
  </div>
</div>

<style>
  .categories-admin-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 18px;
  }
  @media (max-width: 1280px) { .categories-admin-grid { grid-template-columns: repeat(2, 1fr); } }
  @media (max-width: 720px) { .categories-admin-grid { grid-template-columns: 1fr; } }

  .cat-admin-card {
    background: var(--surface);
    border: 1px solid var(--line);
    border-radius: var(--radius);
    overflow: hidden;
    display: flex;
    flex-direction: column;
    transition: all .2s var(--ease);
  }
  .cat-admin-card:hover {
    border-color: var(--olive-soft);
    box-shadow: var(--shadow);
  }
  .cat-admin-image {
    aspect-ratio: 16/9;
    background: var(--sand);
    overflow: hidden;
    position: relative;
  }
  .cat-admin-image img { width: 100%; height: 100%; object-fit: cover; }
  .cat-admin-image-empty {
    width: 100%;
    height: 100%;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 8px;
    color: var(--ink-mute);
  }
  .cat-admin-image-empty span { font-size: 0.78rem; }
  .cat-admin-order {
    position: absolute;
    top: 10px;
    left: 10px;
    background: rgba(31,36,33,0.7);
    color: var(--cream);
    padding: 3px 9px;
    border-radius: 999px;
    font-size: 0.72rem;
    font-weight: 600;
    backdrop-filter: blur(4px);
  }
  .cat-admin-body {
    padding: 16px 18px 12px;
    flex: 1;
    display: flex;
    flex-direction: column;
    gap: 6px;
  }
  .cat-admin-body h3 {
    font-family: var(--font-body);
    font-size: 1rem;
    font-weight: 600;
    margin: 0;
  }
  .cat-admin-slug {
    font-family: ui-monospace, 'SF Mono', Menlo, monospace;
    font-size: 0.72rem;
    color: var(--ink-mute);
    margin: 0;
  }
  .cat-admin-desc {
    font-size: 0.85rem;
    color: var(--ink-soft);
    margin: 4px 0 0;
    line-height: 1.4;
  }
  .cat-admin-meta { margin-top: 8px; }
  .cat-admin-actions {
    display: flex;
    gap: 6px;
    align-items: center;
    padding: 12px 18px;
    border-top: 1px solid var(--line-soft);
    background: var(--surface-2);
  }
</style>

<?php require __DIR__ . '/_includes/footer.php'; ?>
