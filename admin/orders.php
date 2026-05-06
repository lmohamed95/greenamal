<?php
require_once __DIR__ . '/../includes/auth.php';
admin_require_login();

$page_title = 'Commandes';
$current = 'orders';

$status_filter = $_GET['status'] ?? 'all';
$search = trim($_GET['q'] ?? '');

$where = [];
$params = [];
if ($status_filter !== 'all') {
    $where[] = 'o.status = ?';
    $params[] = $status_filter;
}
if ($search !== '') {
    $where[] = '(o.order_number LIKE ? OR c.email LIKE ? OR CONCAT(c.first_name, " ", c.last_name) LIKE ?)';
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
}
$whereSql = empty($where) ? '' : 'WHERE ' . implode(' AND ', $where);

$orders = db_all("
    SELECT o.*, c.first_name, c.last_name, c.email, (SELECT COUNT(*) FROM order_items WHERE order_id = o.id) AS items_count
    FROM orders o
    LEFT JOIN customers c ON c.id = o.customer_id
    $whereSql
    ORDER BY o.created_at DESC
    LIMIT 50
", $params);

$counts = db_one("
    SELECT
      COUNT(*) AS total,
      SUM(status='pending') AS pending,
      SUM(status='processing') AS processing,
      SUM(status='shipped') AS shipped,
      SUM(status='delivered') AS delivered,
      SUM(status='cancelled') AS cancelled
    FROM orders
");

require __DIR__ . '/_includes/header.php';
?>

<div class="page">
  <div class="breadcrumb-admin"><a href="index.php">Tableau de bord</a><span>/</span><span>Commandes</span></div>
  <div class="page-head">
    <div>
      <h1>Commandes</h1>
      <p><?= (int) $counts['total'] ?> commandes au total · <?= (int) $counts['pending'] ?> en attente de traitement</p>
    </div>
    <div class="page-actions">
      <button class="btn btn-outline">Exporter CSV</button>
    </div>
  </div>

  <form method="get" class="toolbar">
    <div class="toolbar-tabs">
      <a href="?status=all" class="toolbar-tab<?= $status_filter === 'all' ? ' active' : '' ?>">Toutes <span class="count"><?= (int) $counts['total'] ?></span></a>
      <a href="?status=pending" class="toolbar-tab<?= $status_filter === 'pending' ? ' active' : '' ?>">En attente <span class="count"><?= (int) $counts['pending'] ?></span></a>
      <a href="?status=processing" class="toolbar-tab<?= $status_filter === 'processing' ? ' active' : '' ?>">Préparation <span class="count"><?= (int) $counts['processing'] ?></span></a>
      <a href="?status=shipped" class="toolbar-tab<?= $status_filter === 'shipped' ? ' active' : '' ?>">Expédiées <span class="count"><?= (int) $counts['shipped'] ?></span></a>
      <a href="?status=delivered" class="toolbar-tab<?= $status_filter === 'delivered' ? ' active' : '' ?>">Livrées <span class="count"><?= (int) $counts['delivered'] ?></span></a>
      <a href="?status=cancelled" class="toolbar-tab<?= $status_filter === 'cancelled' ? ' active' : '' ?>">Annulées <span class="count"><?= (int) $counts['cancelled'] ?></span></a>
    </div>
    <div class="search-mini">
      <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
      <input class="field-input" name="q" value="<?= e($search) ?>" placeholder="N° commande, client...">
    </div>
    <input type="hidden" name="status" value="<?= e($status_filter) ?>">
    <button class="btn btn-outline btn-sm">Rechercher</button>
  </form>

  <div class="card">
    <table class="data-table">
      <thead>
        <tr>
          <th class="check-col"><input type="checkbox"></th>
          <th>Commande</th><th>Date</th><th>Client</th><th>Articles</th><th>Paiement</th><th>Statut</th><th>Total</th><th class="actions-col"></th>
        </tr>
      </thead>
      <tbody>
        <?php if (empty($orders)): ?>
          <tr><td colspan="9" style="text-align: center; padding: 60px; color: var(--ink-mute);">Aucune commande</td></tr>
        <?php else: foreach ($orders as $o):
          [$status_lbl, $status_cls] = order_status_label($o['status']);
          [$pay_lbl, $pay_cls] = payment_method_label($o['payment_method']);
          $name = trim(($o['first_name'] ?? '') . ' ' . ($o['last_name'] ?? '')) ?: 'Anonyme';
        ?>
          <tr>
            <td><input type="checkbox"></td>
            <td><a href="order-detail.php?id=<?= (int) $o['id'] ?>" class="cell-mono" style="color: var(--olive); font-weight: 500;">#<?= e($o['order_number']) ?></a></td>
            <td class="cell-mute"><?= time_ago($o['created_at']) ?></td>
            <td><div class="cell-customer"><span class="cell-customer-avatar"><?= e(initials($name)) ?></span><span><strong style="font-size: 0.85rem;"><?= e($name) ?></strong><div class="cell-mute"><?= e($o['email'] ?? '—') ?></div></span></div></td>
            <td class="cell-num"><?= (int) $o['items_count'] ?></td>
            <td><span class="badge-status <?= e($pay_cls) ?>"><?= e($pay_lbl) ?></span></td>
            <td><span class="badge-status <?= e($status_cls) ?>"><?= e($status_lbl) ?></span></td>
            <td class="cell-num"><?= price($o['total']) ?></td>
            <td><a href="order-detail.php?id=<?= (int) $o['id'] ?>" class="topbar-btn"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="1"/><circle cx="19" cy="12" r="1"/><circle cx="5" cy="12" r="1"/></svg></a></td>
          </tr>
        <?php endforeach; endif; ?>
      </tbody>
    </table>
  </div>
</div>

<?php require __DIR__ . '/_includes/footer.php'; ?>
