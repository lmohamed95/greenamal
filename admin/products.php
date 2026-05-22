<?php
require_once __DIR__ . '/../includes/auth.php';
admin_require_login();

// =====================================================================
// Bulk actions (POST)
// =====================================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['bulk_action'])) {
    csrf_verify();
    $action = (string) $_POST['bulk_action'];
    $ids = array_values(array_filter(array_map('intval', (array) ($_POST['ids'] ?? []))));

    if ($ids && in_array($action, ['active', 'draft', 'archived', 'delete', 'feature', 'unfeature'], true)) {
        $placeholders = implode(',', array_fill(0, count($ids), '?'));

        switch ($action) {
            case 'delete':
                db_query("DELETE FROM product_images WHERE product_id IN ($placeholders)", $ids);
                db_query("DELETE FROM reviews WHERE product_id IN ($placeholders)", $ids);
                db_query("UPDATE order_items SET product_id = NULL WHERE product_id IN ($placeholders)", $ids);
                db_query("DELETE FROM products WHERE id IN ($placeholders)", $ids);
                $_SESSION['flash'] = ['type' => 'success', 'msg' => count($ids) . ' produit(s) supprimé(s).'];
                break;
            case 'feature':
                db_query("UPDATE products SET is_featured = 1 WHERE id IN ($placeholders)", $ids);
                $_SESSION['flash'] = ['type' => 'success', 'msg' => count($ids) . ' produit(s) mis en avant.'];
                break;
            case 'unfeature':
                db_query("UPDATE products SET is_featured = 0 WHERE id IN ($placeholders)", $ids);
                $_SESSION['flash'] = ['type' => 'success', 'msg' => count($ids) . ' produit(s) retiré(s) de la mise en avant.'];
                break;
            default: // active | draft | archived
                $params = array_merge([$action], $ids);
                db_query("UPDATE products SET status = ? WHERE id IN ($placeholders)", $params);
                $labels = ['active' => 'activé(s)', 'draft' => 'mis en brouillon', 'archived' => 'archivé(s)'];
                $_SESSION['flash'] = ['type' => 'success', 'msg' => count($ids) . ' produit(s) ' . $labels[$action] . '.'];
        }
    } else {
        $_SESSION['flash'] = ['type' => 'error', 'msg' => 'Aucun produit sélectionné ou action invalide.'];
    }

    // Preserve filters on redirect
    $qs = http_build_query(array_intersect_key($_POST, array_flip(['status', 'cat', 'q'])));
    redirect('products.php' . ($qs ? '?' . $qs : ''));
}

$page_title = 'Produits';
$current = 'products';

$status_filter = $_GET['status'] ?? 'all';
$cat_filter = $_GET['cat'] ?? '';
$q_filter = trim((string) ($_GET['q'] ?? ''));

$where = [];
$params = [];
$order_by = 'p.created_at DESC';
if ($status_filter !== 'all') {
    if ($status_filter === 'low_stock') {
        $where[] = "p.stock <= p.low_stock_threshold AND p.stock > 0";
    } elseif ($status_filter === 'out_of_stock') {
        $where[] = "p.stock = 0";
    } elseif ($status_filter === 'featured') {
        $where[] = "p.is_featured = 1";
    } elseif ($status_filter === 'bestseller') {
        $where[] = "p.sales_count > 0";
        $order_by = 'p.sales_count DESC';
    } elseif ($status_filter === 'promo') {
        $where[] = "p.compare_at_price > p.price";
    } else {
        $where[] = "p.status = ?";
        $params[] = $status_filter;
    }
}
if ($cat_filter !== '') {
    $where[] = "c.slug = ?";
    $params[] = $cat_filter;
}
if ($q_filter !== '') {
    $where[] = "(p.name LIKE ? OR p.sku LIKE ?)";
    $like = '%' . $q_filter . '%';
    $params[] = $like;
    $params[] = $like;
}
$whereSql = empty($where) ? '' : 'WHERE ' . implode(' AND ', $where);

$products = db_all("
    SELECT p.*, c.name AS category_name
    FROM products p
    LEFT JOIN categories c ON c.id = p.category_id
    $whereSql
    ORDER BY $order_by
    LIMIT 50
", $params);

$counts = db_one("
    SELECT
      COUNT(*) AS total,
      SUM(status='active') AS active,
      SUM(status='draft') AS draft,
      SUM(stock <= low_stock_threshold AND stock > 0) AS low_stock,
      SUM(stock = 0) AS out_of_stock,
      SUM(is_featured = 1) AS featured,
      SUM(sales_count > 0) AS bestseller,
      SUM(compare_at_price > price) AS promo
    FROM products
");
$categories = db_all("SELECT * FROM categories ORDER BY display_order");

$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);

require __DIR__ . '/_includes/header.php';
?>

<div class="page">
  <div class="breadcrumb-admin"><a href="index.php">Tableau de bord</a><span>/</span><span>Produits</span></div>
  <div class="page-head">
    <div>
      <h1>Produits</h1>
      <p><?= (int) $counts['total'] ?> produits · <?= (int) $counts['active'] ?> actifs · <?= (int) $counts['draft'] ?> brouillons</p>
    </div>
    <div class="page-actions">
      <a href="products-export.php" class="btn btn-ghost" title="Télécharger tous les produits en CSV (ouvrable dans Excel)">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
        Exporter CSV
      </a>
      <a href="products-import.php" class="btn btn-ghost" title="Importer un CSV pour mettre à jour les prix">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
        Importer prix
      </a>
      <a href="product-edit.php" class="btn btn-primary">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
        Nouveau produit
      </a>
    </div>
  </div>

  <?php if ($flash): ?>
    <div class="flash flash-<?= e($flash['type']) ?>"><?= e($flash['msg']) ?></div>
  <?php endif; ?>

  <?php
    // Build a querystring that preserves the current cat+q across tab switches
    $tab_qs = function (string $status) use ($cat_filter, $q_filter): string {
        $p = ['status' => $status];
        if ($cat_filter !== '') $p['cat'] = $cat_filter;
        if ($q_filter !== '') $p['q'] = $q_filter;
        return '?' . http_build_query($p);
    };
  ?>
  <form method="get" class="toolbar">
    <div class="toolbar-tabs">
      <a href="<?= e($tab_qs('all')) ?>" class="toolbar-tab<?= $status_filter === 'all' ? ' active' : '' ?>">Tous <span class="count"><?= (int) $counts['total'] ?></span></a>
      <a href="<?= e($tab_qs('active')) ?>" class="toolbar-tab<?= $status_filter === 'active' ? ' active' : '' ?>">Actifs <span class="count"><?= (int) $counts['active'] ?></span></a>
      <a href="<?= e($tab_qs('draft')) ?>" class="toolbar-tab<?= $status_filter === 'draft' ? ' active' : '' ?>">Brouillons <span class="count"><?= (int) $counts['draft'] ?></span></a>
      <a href="<?= e($tab_qs('featured')) ?>" class="toolbar-tab<?= $status_filter === 'featured' ? ' active' : '' ?>">★ Mis en avant <span class="count"><?= (int) $counts['featured'] ?></span></a>
      <a href="<?= e($tab_qs('bestseller')) ?>" class="toolbar-tab<?= $status_filter === 'bestseller' ? ' active' : '' ?>">Best-sellers <span class="count"><?= (int) $counts['bestseller'] ?></span></a>
      <a href="<?= e($tab_qs('promo')) ?>" class="toolbar-tab<?= $status_filter === 'promo' ? ' active' : '' ?>">En promo <span class="count"><?= (int) $counts['promo'] ?></span></a>
      <a href="<?= e($tab_qs('low_stock')) ?>" class="toolbar-tab<?= $status_filter === 'low_stock' ? ' active' : '' ?>">Stock bas <span class="count"><?= (int) $counts['low_stock'] ?></span></a>
      <a href="<?= e($tab_qs('out_of_stock')) ?>" class="toolbar-tab<?= $status_filter === 'out_of_stock' ? ' active' : '' ?>">Rupture <span class="count"><?= (int) $counts['out_of_stock'] ?></span></a>
    </div>
    <input type="search" name="q" class="field-input" placeholder="Rechercher (nom ou SKU)…" value="<?= e($q_filter) ?>">
    <select name="cat" class="field-select" onchange="this.form.submit()">
      <option value="">Toutes catégories</option>
      <?php foreach ($categories as $c): ?>
        <option value="<?= e($c['slug']) ?>" <?= $cat_filter === $c['slug'] ? 'selected' : '' ?>><?= e($c['name']) ?></option>
      <?php endforeach; ?>
    </select>
    <input type="hidden" name="status" value="<?= e($status_filter) ?>">
  </form>

  <form method="post" id="bulkForm">
    <?= csrf_field() ?>
    <input type="hidden" name="status" value="<?= e($status_filter) ?>">
    <input type="hidden" name="cat" value="<?= e($cat_filter) ?>">
    <input type="hidden" name="q" value="<?= e($q_filter) ?>">

    <div class="bulk-bar" id="bulkBar" hidden>
      <span class="bulk-count"><strong id="bulkCount">0</strong> sélectionné(s)</span>
      <button type="submit" name="bulk_action" value="active" class="btn btn-sm btn-secondary">Activer</button>
      <button type="submit" name="bulk_action" value="draft" class="btn btn-sm btn-ghost">Brouillon</button>
      <button type="submit" name="bulk_action" value="archived" class="btn btn-sm btn-ghost">Archiver</button>
      <button type="submit" name="bulk_action" value="feature" class="btn btn-sm btn-ghost">★ Mettre en avant</button>
      <button type="submit" name="bulk_action" value="unfeature" class="btn btn-sm btn-ghost">Retirer ★</button>
      <button type="submit" name="bulk_action" value="delete" class="btn btn-sm btn-danger" data-confirm="Supprimer définitivement les produits sélectionnés ?">Supprimer</button>
      <button type="button" class="btn btn-sm btn-ghost" id="bulkClear">Annuler</button>
    </div>

    <div class="card">
      <table class="data-table" id="productsTable">
        <thead>
          <tr>
            <th class="check-col"><input type="checkbox" id="checkAll" aria-label="Tout sélectionner"></th>
            <th>Produit</th><th>SKU</th><th>Catégorie</th><th>Prix</th><th>Stock</th><th>Ventes</th><th>Statut</th><th class="actions-col"></th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($products as $p):
            [$status_lbl, $status_cls] = product_status_label($p['status']);
            [$stock_lbl, $stock_color] = stock_level((int) $p['stock'], (int) $p['low_stock_threshold']);
          ?>
            <tr>
              <td><input type="checkbox" name="ids[]" value="<?= (int) $p['id'] ?>" class="row-check" aria-label="Sélectionner <?= e($p['name']) ?>"></td>
              <td><div class="cell-product"><img src="<?= e($p['image_main']) ?>"><span><strong><?= e($p['name']) ?></strong></span></div></td>
              <td class="cell-mono"><?= e($p['sku']) ?></td>
              <td><span class="badge-status status-neutral"><?= e($p['category_name'] ?? '-') ?></span></td>
              <td class="cell-num">
                <strong><?= price($p['price']) ?></strong>
                <?php if ($p['compare_at_price'] > 0): ?> <span class="cell-mute" style="text-decoration: line-through;"><?= price($p['compare_at_price']) ?></span><?php endif; ?>
              </td>
              <td><span style="color: var(--<?= $stock_color ?>); font-weight: 500;"><?= e($stock_lbl) ?></span></td>
              <td class="cell-num"><?= (int) $p['sales_count'] ?></td>
              <td><span class="badge-status <?= e($status_cls) ?>"><?= e($status_lbl) ?></span></td>
              <td>
                <div class="row-actions">
                  <a href="product-edit.php?id=<?= (int) $p['id'] ?>" class="topbar-btn" title="Modifier" aria-label="Modifier <?= e($p['name']) ?>">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                  </a>
                  <button type="button" class="topbar-btn row-delete" data-id="<?= (int) $p['id'] ?>" data-name="<?= e($p['name']) ?>" title="Supprimer" aria-label="Supprimer <?= e($p['name']) ?>">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-2 14a2 2 0 0 1-2 2H9a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6M14 11v6"/><path d="M9 6V4a2 2 0 0 1 2-2h2a2 2 0 0 1 2 2v2"/></svg>
                  </button>
                </div>
              </td>
            </tr>
          <?php endforeach; ?>
          <?php if (!$products): ?>
            <tr><td colspan="9" style="text-align:center; padding: 32px; color: var(--ink-mute);">Aucun produit.</td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </form>
</div>

<script>
(function () {
  const form     = document.getElementById('bulkForm');
  const bar      = document.getElementById('bulkBar');
  const countEl  = document.getElementById('bulkCount');
  const checkAll = document.getElementById('checkAll');
  const rows     = () => form.querySelectorAll('.row-check');
  const checked  = () => form.querySelectorAll('.row-check:checked');

  function refresh() {
    const n = checked().length;
    countEl.textContent = n;
    bar.hidden = n === 0;
    const total = rows().length;
    checkAll.checked = n > 0 && n === total;
    checkAll.indeterminate = n > 0 && n < total;
  }

  checkAll.addEventListener('change', () => {
    rows().forEach(cb => { cb.checked = checkAll.checked; });
    refresh();
  });

  rows().forEach(cb => cb.addEventListener('change', refresh));

  document.getElementById('bulkClear').addEventListener('click', () => {
    rows().forEach(cb => { cb.checked = false; });
    refresh();
  });

  // Confirm destructive actions
  bar.querySelectorAll('button[data-confirm]').forEach(btn => {
    btn.addEventListener('click', e => {
      if (!confirm(btn.getAttribute('data-confirm'))) e.preventDefault();
    });
  });

  // Per-row delete — reuses the bulk_action=delete server handler with a
  // single id. Builds a one-off form so we don't disturb the user's current
  // checkbox selection.
  document.querySelectorAll('.row-delete').forEach(btn => {
    btn.addEventListener('click', () => {
      const id   = btn.dataset.id;
      const name = btn.dataset.name || 'ce produit';
      if (!confirm('Supprimer définitivement « ' + name + ' » ? Cette action est irréversible.')) return;
      const csrf = document.querySelector('meta[name="csrf-token"]').content;
      const f = document.createElement('form');
      f.method = 'POST';
      f.action = 'products.php';
      const fields = { _csrf: csrf, bulk_action: 'delete', 'ids[]': id };
      Object.entries(fields).forEach(([k, v]) => {
        const i = document.createElement('input');
        i.type = 'hidden';
        i.name = k;
        i.value = v;
        f.appendChild(i);
      });
      document.body.appendChild(f);
      f.submit();
    });
  });

  refresh();
})();
</script>

<?php require __DIR__ . '/_includes/footer.php'; ?>
