<?php
require_once __DIR__ . '/../includes/auth.php';
admin_require_login();

$page_title = 'Clients';
$current = 'customers';

$segment = $_GET['segment'] ?? 'all';
$search = trim($_GET['q'] ?? '');

$where = [];
$params = [];
if ($segment !== 'all') {
    $where[] = 'segment = ?';
    $params[] = $segment;
}
if ($search !== '') {
    $where[] = '(email LIKE ? OR CONCAT(first_name, " ", last_name) LIKE ?)';
    $params[] = "%$search%";
    $params[] = "%$search%";
}
$whereSql = empty($where) ? '' : 'WHERE ' . implode(' AND ', $where);

$customers = db_all("SELECT * FROM customers $whereSql ORDER BY lifetime_value DESC LIMIT 50", $params);

$counts = db_one("
    SELECT
      COUNT(*) AS total,
      SUM(segment='vip') AS vip,
      SUM(segment='new') AS new,
      SUM(segment='inactive') AS inactive,
      SUM(newsletter_subscribed=1) AS newsletter
    FROM customers
");

$total_ltv = (float) db_value("SELECT COALESCE(SUM(lifetime_value),0) FROM customers");
$avg_ltv = (int) $counts['total'] > 0 ? $total_ltv / (int) $counts['total'] : 0;

require __DIR__ . '/_includes/header.php';
?>

<div class="page">
  <div class="breadcrumb-admin"><a href="index.php">Tableau de bord</a><span>/</span><span>Clients</span></div>
  <div class="page-head">
    <div>
      <h1>Clients</h1>
      <p><?= (int) $counts['total'] ?> clients enregistrés</p>
    </div>
    <div class="page-actions">
      <button class="btn btn-outline">Exporter CSV</button>
    </div>
  </div>

  <div class="kpi-grid">
    <div class="kpi"><div class="kpi-label">Total clients</div><div class="kpi-value"><?= (int) $counts['total'] ?></div></div>
    <div class="kpi"><div class="kpi-label">Clients VIP</div><div class="kpi-value"><?= (int) $counts['vip'] ?></div></div>
    <div class="kpi"><div class="kpi-label">Newsletter</div><div class="kpi-value"><?= (int) $counts['newsletter'] ?></div></div>
    <div class="kpi"><div class="kpi-label">LTV moyenne</div><div class="kpi-value"><?= price($avg_ltv) ?></div></div>
  </div>

  <form method="get" class="toolbar">
    <div class="toolbar-tabs">
      <a href="?segment=all" class="toolbar-tab<?= $segment === 'all' ? ' active' : '' ?>">Tous <span class="count"><?= (int) $counts['total'] ?></span></a>
      <a href="?segment=vip" class="toolbar-tab<?= $segment === 'vip' ? ' active' : '' ?>">VIP <span class="count"><?= (int) $counts['vip'] ?></span></a>
      <a href="?segment=new" class="toolbar-tab<?= $segment === 'new' ? ' active' : '' ?>">Nouveaux <span class="count"><?= (int) $counts['new'] ?></span></a>
      <a href="?segment=inactive" class="toolbar-tab<?= $segment === 'inactive' ? ' active' : '' ?>">Inactifs <span class="count"><?= (int) $counts['inactive'] ?></span></a>
    </div>
    <div class="search-mini">
      <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
      <input class="field-input" name="q" value="<?= e($search) ?>" placeholder="Nom, email...">
    </div>
    <input type="hidden" name="segment" value="<?= e($segment) ?>">
    <button class="btn btn-outline btn-sm">Rechercher</button>
  </form>

  <div class="card">
    <table class="data-table">
      <thead><tr>
        <th class="check-col"><input type="checkbox"></th>
        <th>Client</th><th>Email</th><th>Ville</th><th>Commandes</th><th>LTV</th><th>Dernière commande</th><th>Segment</th>
      </tr></thead>
      <tbody>
        <?php if (empty($customers)): ?>
          <tr><td colspan="8" style="text-align: center; padding: 60px; color: var(--ink-mute);">Aucun client</td></tr>
        <?php else: foreach ($customers as $c):
          [$seg_lbl, $seg_cls] = customer_segment_label($c['segment']);
          $name = trim(($c['first_name'] ?? '') . ' ' . ($c['last_name'] ?? ''));
        ?>
          <tr>
            <td><input type="checkbox"></td>
            <td><div class="cell-customer"><span class="cell-customer-avatar"><?= e(initials($name)) ?></span><strong><?= e($name) ?></strong></div></td>
            <td class="cell-mute"><?= e($c['email']) ?></td>
            <td><?= e($c['city'] ?? '—') ?></td>
            <td class="cell-num"><?= (int) $c['total_orders'] ?></td>
            <td class="cell-num"><strong><?= price($c['lifetime_value']) ?></strong></td>
            <td class="cell-mute"><?= $c['last_order_at'] ? time_ago($c['last_order_at']) : '—' ?></td>
            <td><span class="badge-status <?= e($seg_cls) ?>"><?= e($seg_lbl) ?></span></td>
          </tr>
        <?php endforeach; endif; ?>
      </tbody>
    </table>
  </div>
</div>

<?php require __DIR__ . '/_includes/footer.php'; ?>
