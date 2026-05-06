<?php
require_once __DIR__ . '/../includes/auth.php';
admin_require_login();

$page_title = 'Tableau de bord';
$current = 'dashboard';

// ─────────────────────────────────────────────────────────
// Date range parsing (?range=today|7d|30d|custom + from/to)
// ─────────────────────────────────────────────────────────
$range = $_GET['range'] ?? '30d';
$today = date('Y-m-d');
$req_from = $_GET['from'] ?? null;
$req_to = $_GET['to'] ?? null;

if ($range === 'today') {
    $from = $today;
    $to = $today;
} elseif ($range === '7d') {
    $from = date('Y-m-d', strtotime('-6 days'));
    $to = $today;
} elseif ($range === 'custom' && $req_from && $req_to) {
    $f = strtotime($req_from);
    $t = strtotime($req_to);
    if ($f && $t && $t >= $f) {
        $from = date('Y-m-d', $f);
        $to = date('Y-m-d', $t);
    } else {
        $range = '30d';
        $from = date('Y-m-d', strtotime('-29 days'));
        $to = $today;
    }
} else {
    $range = '30d';
    $from = date('Y-m-d', strtotime('-29 days'));
    $to = $today;
}

$period_days = (int) round((strtotime($to) - strtotime($from)) / 86400) + 1;
$prev_to = date('Y-m-d', strtotime($from . ' -1 day'));
$prev_from = date('Y-m-d', strtotime($prev_to . ' -' . ($period_days - 1) . ' days'));

// ─────────────────────────────────────────────────────────
// KPI helpers
// ─────────────────────────────────────────────────────────
function _range_kpis(string $from, string $to): array {
    $row = db_one("
        SELECT
            COALESCE(SUM(CASE WHEN status NOT IN ('cancelled') THEN total ELSE 0 END), 0) AS revenue,
            COUNT(*) AS orders_n,
            SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) AS pending_n
        FROM orders
        WHERE created_at >= :from AND created_at < :to_excl
    ", [
        'from' => $from . ' 00:00:00',
        'to_excl' => date('Y-m-d 00:00:00', strtotime($to . ' +1 day')),
    ]);
    $rev = (float) ($row['revenue'] ?? 0);
    $ord = (int) ($row['orders_n'] ?? 0);
    return [
        'revenue' => $rev,
        'orders' => $ord,
        'aov' => $ord > 0 ? $rev / $ord : 0,
        'pending' => (int) ($row['pending_n'] ?? 0),
    ];
}

function _range_new_customers(string $from, string $to): int {
    return (int) db_value("
        SELECT COUNT(*) FROM customers
        WHERE created_at >= :from AND created_at < :to_excl
    ", [
        'from' => $from . ' 00:00:00',
        'to_excl' => date('Y-m-d 00:00:00', strtotime($to . ' +1 day')),
    ]);
}

function _delta(float $cur, float $prev): array {
    if ($prev <= 0) {
        return $cur > 0 ? ['Nouveau', 'up'] : ['—', 'flat'];
    }
    $pct = (($cur - $prev) / $prev) * 100;
    $cls = $pct > 0.5 ? 'up' : ($pct < -0.5 ? 'down' : 'flat');
    $arrow = $pct > 0.5 ? '↑' : ($pct < -0.5 ? '↓' : '→');
    return [$arrow . ' ' . number_format(abs($pct), 1, ',', ' ') . ' %', $cls];
}

$cur = _range_kpis($from, $to);
$prv = _range_kpis($prev_from, $prev_to);
$cur_new_customers = _range_new_customers($from, $to);
$prv_new_customers = _range_new_customers($prev_from, $prev_to);
$customers_total = (int) db_value("SELECT COUNT(*) FROM customers");

[$rev_delta, $rev_cls] = _delta($cur['revenue'], $prv['revenue']);
[$ord_delta, $ord_cls] = _delta((float) $cur['orders'], (float) $prv['orders']);
[$aov_delta, $aov_cls] = _delta($cur['aov'], $prv['aov']);
[$cust_delta, $cust_cls] = _delta((float) $cur_new_customers, (float) $prv_new_customers);

// ─────────────────────────────────────────────────────────
// Daily series (current + previous period for main chart)
// ─────────────────────────────────────────────────────────
function _daily_series(string $from, string $to): array {
    $rows = db_all("
        SELECT DATE(created_at) AS d,
               COALESCE(SUM(CASE WHEN status NOT IN ('cancelled') THEN total ELSE 0 END), 0) AS rev,
               COUNT(*) AS orders_n
        FROM orders
        WHERE created_at >= :from AND created_at < :to_excl
        GROUP BY DATE(created_at)
        ORDER BY d
    ", [
        'from' => $from . ' 00:00:00',
        'to_excl' => date('Y-m-d 00:00:00', strtotime($to . ' +1 day')),
    ]);
    $map = [];
    foreach ($rows as $r) {
        $map[$r['d']] = ['rev' => (float) $r['rev'], 'orders' => (int) $r['orders_n']];
    }
    $out = [];
    $cursor = strtotime($from);
    $end = strtotime($to);
    while ($cursor <= $end) {
        $d = date('Y-m-d', $cursor);
        $out[] = [
            'date' => $d,
            'rev' => $map[$d]['rev'] ?? 0,
            'orders' => $map[$d]['orders'] ?? 0,
        ];
        $cursor = strtotime('+1 day', $cursor);
    }
    return $out;
}

$series_cur = _daily_series($from, $to);
$series_prv = _daily_series($prev_from, $prev_to);

// ─────────────────────────────────────────────────────────
// Sparklines: always last 14 days for context
// ─────────────────────────────────────────────────────────
$spark_from = date('Y-m-d', strtotime('-13 days'));
$spark_to = $today;
$spark_orders = _daily_series($spark_from, $spark_to);

$cust_rows = db_all("
    SELECT DATE(created_at) AS d, COUNT(*) AS n
    FROM customers
    WHERE created_at >= :from
    GROUP BY DATE(created_at)
    ORDER BY d
", ['from' => $spark_from . ' 00:00:00']);
$cust_map = [];
foreach ($cust_rows as $r) $cust_map[$r['d']] = (int) $r['n'];

$spark_revenue = [];
$spark_orders_arr = [];
$spark_aov = [];
$spark_customers = [];
foreach ($spark_orders as $row) {
    $spark_revenue[] = round($row['rev'], 2);
    $spark_orders_arr[] = $row['orders'];
    $spark_aov[] = $row['orders'] > 0 ? round($row['rev'] / $row['orders'], 2) : 0;
    $spark_customers[] = $cust_map[$row['date']] ?? 0;
}

// ─────────────────────────────────────────────────────────
// Top products / recent orders / low stock
// ─────────────────────────────────────────────────────────
$top_products = db_all("SELECT id, name, image_main, sales_count, price FROM products WHERE status='active' ORDER BY sales_count DESC LIMIT 5");

$recent_orders = db_all("
    SELECT o.*, c.first_name, c.last_name, c.city
    FROM orders o
    LEFT JOIN customers c ON c.id = o.customer_id
    ORDER BY o.created_at DESC
    LIMIT 5
");

$low_stock = db_all("SELECT id, name, sku, stock FROM products WHERE status='active' AND stock <= low_stock_threshold ORDER BY stock ASC LIMIT 3");

// ─────────────────────────────────────────────────────────
// Tier 2 — depth widgets
// ─────────────────────────────────────────────────────────
$range_params = [
    'from' => $from . ' 00:00:00',
    'to_excl' => date('Y-m-d 00:00:00', strtotime($to . ' +1 day')),
];

// Revenue by category
$cat_revenue = db_all("
    SELECT COALESCE(c.name, 'Sans catégorie') AS name, COALESCE(SUM(oi.total), 0) AS revenue
    FROM order_items oi
    JOIN orders o ON o.id = oi.order_id
    LEFT JOIN products p ON p.id = oi.product_id
    LEFT JOIN categories c ON c.id = p.category_id
    WHERE o.created_at >= :from AND o.created_at < :to_excl AND o.status NOT IN ('cancelled')
    GROUP BY c.id, c.name
    HAVING revenue > 0
    ORDER BY revenue DESC
", $range_params);
$cat_labels = array_map(fn($r) => $r['name'], $cat_revenue);
$cat_values = array_map(fn($r) => round((float) $r['revenue'], 2), $cat_revenue);

// Top 10 cities
$top_cities = db_all("
    SELECT shipping_city AS city, COUNT(*) AS orders_n, COALESCE(SUM(total), 0) AS revenue
    FROM orders
    WHERE created_at >= :from AND created_at < :to_excl
      AND status NOT IN ('cancelled')
      AND shipping_city IS NOT NULL AND shipping_city <> ''
    GROUP BY shipping_city
    ORDER BY revenue DESC
    LIMIT 10
", $range_params);
$city_labels = array_map(fn($r) => $r['city'], $top_cities);
$city_values = array_map(fn($r) => round((float) $r['revenue'], 2), $top_cities);

// Payment methods breakdown
$payment_breakdown = db_all("
    SELECT payment_method AS method, COUNT(*) AS orders_n, COALESCE(SUM(total), 0) AS revenue
    FROM orders
    WHERE created_at >= :from AND created_at < :to_excl AND status NOT IN ('cancelled')
    GROUP BY payment_method
    ORDER BY revenue DESC
", $range_params);
$payment_labels = [];
$payment_values = [];
foreach ($payment_breakdown as $row) {
    [$lbl, $_cls] = payment_method_label($row['method']);
    $payment_labels[] = $lbl;
    $payment_values[] = round((float) $row['revenue'], 2);
}

// Order fulfillment funnel
$funnel_row = db_one("
    SELECT
        COUNT(*) AS created,
        SUM(CASE WHEN status NOT IN ('pending','cancelled') THEN 1 ELSE 0 END) AS confirmed,
        SUM(CASE WHEN status IN ('shipped','delivered') THEN 1 ELSE 0 END) AS shipped,
        SUM(CASE WHEN status = 'delivered' THEN 1 ELSE 0 END) AS delivered
    FROM orders
    WHERE created_at >= :from AND created_at < :to_excl
", $range_params);
$funnel_stages = [
    ['Commandes créées', (int) ($funnel_row['created'] ?? 0)],
    ['Confirmées',       (int) ($funnel_row['confirmed'] ?? 0)],
    ['Expédiées',        (int) ($funnel_row['shipped'] ?? 0)],
    ['Livrées',          (int) ($funnel_row['delivered'] ?? 0)],
];
$funnel_labels = array_column($funnel_stages, 0);
$funnel_values = array_column($funnel_stages, 1);

// Monthly revenue goal
$goal_target = 60000;
$month_start = date('Y-m-01');
$goal_so_far = (float) db_value("
    SELECT COALESCE(SUM(total),0) FROM orders
    WHERE created_at >= :from AND status NOT IN ('cancelled')
", ['from' => $month_start . ' 00:00:00']);
$goal_pct = $goal_target > 0 ? min(100, round(($goal_so_far / $goal_target) * 100, 1)) : 0;

// ─────────────────────────────────────────────────────────
// Tier 3 — polish widgets
// ─────────────────────────────────────────────────────────

// Mixed activity feed: orders + customer signups + reviews
$activity = db_all("
    (SELECT 'order' AS kind, id, order_number AS l1, CAST(total AS CHAR) AS l2, status AS l3, created_at FROM orders)
    UNION ALL
    (SELECT 'customer' AS kind, id,
            TRIM(CONCAT(COALESCE(first_name,''),' ',COALESCE(last_name,''))) AS l1,
            email AS l2,
            segment AS l3,
            created_at
     FROM customers)
    UNION ALL
    (SELECT 'review' AS kind, r.id,
            COALESCE(p.name, 'Produit supprimé') AS l1,
            CAST(r.rating AS CHAR) AS l2,
            r.status AS l3,
            r.created_at
     FROM reviews r LEFT JOIN products p ON p.id = r.product_id)
    ORDER BY created_at DESC
    LIMIT 12
");

// Top 5 customers by lifetime value
$top_customers = db_all("
    SELECT id, first_name, last_name, email, total_orders, lifetime_value, segment
    FROM customers
    WHERE total_orders > 0
    ORDER BY lifetime_value DESC
    LIMIT 5
");

// Heatmap: orders by day-of-week × hour over last 30 days
$heat_rows = db_all("
    SELECT DAYOFWEEK(created_at) AS dow, HOUR(created_at) AS hr, COUNT(*) AS n
    FROM orders
    WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
      AND status NOT IN ('cancelled')
    GROUP BY DAYOFWEEK(created_at), HOUR(created_at)
");
$heat_lookup = [];
foreach ($heat_rows as $r) {
    $heat_lookup[(int) $r['dow']][(int) $r['hr']] = (int) $r['n'];
}
// ApexCharts renders rows bottom→top, so put Sunday at the bottom (Lun stays at top visually)
$dow_render_order = [1, 7, 6, 5, 4, 3, 2]; // dim, sam, ven, jeu, mer, mar, lun → reversed for top-down read
$dow_label = [1 => 'Dim', 2 => 'Lun', 3 => 'Mar', 4 => 'Mer', 5 => 'Jeu', 6 => 'Ven', 7 => 'Sam'];
$heatmap_series = [];
foreach ($dow_render_order as $d) {
    $points = [];
    for ($h = 0; $h < 24; $h++) {
        $points[] = ['x' => sprintf('%02dh', $h), 'y' => $heat_lookup[$d][$h] ?? 0];
    }
    $heatmap_series[] = ['name' => $dow_label[$d], 'data' => $points];
}

// Anomaly detection: today vs avg of previous 7 days
$today_rev = (float) db_value("
    SELECT COALESCE(SUM(total),0) FROM orders
    WHERE DATE(created_at) = :today AND status NOT IN ('cancelled')
", ['today' => $today]);
$prev7_rev = (float) db_value("
    SELECT COALESCE(SUM(total),0) FROM orders
    WHERE DATE(created_at) >= :from AND DATE(created_at) < :today AND status NOT IN ('cancelled')
", ['from' => date('Y-m-d', strtotime('-7 days')), 'today' => $today]);
$avg7_rev = $prev7_rev / 7;

$today_orders = (int) db_value("
    SELECT COUNT(*) FROM orders WHERE DATE(created_at) = :today AND status NOT IN ('cancelled')
", ['today' => $today]);
$prev7_orders = (int) db_value("
    SELECT COUNT(*) FROM orders WHERE DATE(created_at) >= :from AND DATE(created_at) < :today AND status NOT IN ('cancelled')
", ['from' => date('Y-m-d', strtotime('-7 days')), 'today' => $today]);
$avg7_orders = $prev7_orders / 7;

function _anomaly(float $today_val, float $avg7): ?array {
    if ($avg7 < 1) return null; // not enough baseline
    $ratio = $today_val / $avg7;
    if ($ratio < 0.7) {
        $pct = (int) round((1 - $ratio) * 100);
        return ['⚠ ↓ ' . $pct . ' % aujourd\'hui', 'down'];
    }
    if ($ratio > 1.5) {
        $pct = (int) round(($ratio - 1) * 100);
        return ['🔥 ↑ ' . $pct . ' % aujourd\'hui', 'up'];
    }
    return null;
}
$rev_anomaly = _anomaly($today_rev, $avg7_rev);
$ord_anomaly = _anomaly((float) $today_orders, $avg7_orders);

// Range label for header copy
$range_meta = match($range) {
    'today'  => "Aujourd'hui",
    '7d'     => '7 derniers jours',
    'custom' => 'Du ' . date('j M', strtotime($from)) . ' au ' . date('j M Y', strtotime($to)),
    default  => '30 derniers jours',
};

// Build x-axis category labels for current period
$x_labels = array_map(fn($r) => $r['date'], $series_cur);
$rev_cur_data = array_map(fn($r) => round($r['rev'], 2), $series_cur);
$rev_prv_data = array_map(fn($r) => round($r['rev'], 2), $series_prv);
// Map previous-period dates onto current-period x positions
$prv_dates = array_map(fn($r) => $r['date'], $series_prv);

require __DIR__ . '/_includes/header.php';
?>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/litepicker/dist/css/litepicker.css">
<script src="https://cdn.jsdelivr.net/npm/apexcharts@3.45.2/dist/apexcharts.min.js" defer></script>
<script src="https://cdn.jsdelivr.net/npm/countup.js@2.8.0/dist/countUp.umd.js" defer></script>
<script src="https://cdn.jsdelivr.net/npm/litepicker/dist/litepicker.js" defer></script>

<div class="page">
  <div class="page-head">
    <div>
      <h1>Bonjour <?= e($user['first_name']) ?> 👋</h1>
      <p>Aperçu de votre boutique · <strong><?= e($range_meta) ?></strong></p>
    </div>
    <div class="page-actions">
      <div class="range-presets" role="tablist" aria-label="Période">
        <a href="?range=today" class="range-preset<?= $range === 'today' ? ' active' : '' ?>">Aujourd'hui</a>
        <a href="?range=7d" class="range-preset<?= $range === '7d' ? ' active' : '' ?>">7 jours</a>
        <a href="?range=30d" class="range-preset<?= $range === '30d' ? ' active' : '' ?>">30 jours</a>
        <button type="button" id="range-custom-btn" class="range-preset<?= $range === 'custom' ? ' active' : '' ?>">
          <?= $range === 'custom' ? e(date('j M', strtotime($from)) . ' → ' . date('j M', strtotime($to))) : 'Personnalisé' ?>
        </button>
      </div>
      <a href="product-edit.php" class="btn btn-primary">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
        Nouveau produit
      </a>
    </div>
  </div>

  <div class="kpi-grid">
    <div class="kpi">
      <div class="kpi-icon"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 000 7h5a3.5 3.5 0 010 7H6"/></svg></div>
      <div class="kpi-label">Revenus</div>
      <div class="kpi-value" data-countup data-value="<?= (float) $cur['revenue'] ?>" data-suffix=" <?= e(CURRENCY_SYMBOL) ?>"><?= price($cur['revenue']) ?></div>
      <div class="kpi-meta"><span class="kpi-trend <?= e($rev_cls) ?>"><?= e($rev_delta) ?></span><span class="kpi-meta-text">vs période précédente</span></div>
      <?php if ($rev_anomaly): ?><div class="kpi-anomaly <?= e($rev_anomaly[1]) ?>"><?= e($rev_anomaly[0]) ?></div><?php endif; ?>
      <div class="kpi-spark" data-spark="revenue"></div>
    </div>
    <div class="kpi">
      <div class="kpi-icon"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 2L3 6v14a2 2 0 002 2h14a2 2 0 002-2V6l-3-4z"/></svg></div>
      <div class="kpi-label">Commandes</div>
      <div class="kpi-value" data-countup data-value="<?= (int) $cur['orders'] ?>"><?= (int) $cur['orders'] ?></div>
      <div class="kpi-meta"><span class="kpi-trend <?= e($ord_cls) ?>"><?= e($ord_delta) ?></span><span class="kpi-meta-text"><?= (int) $cur['pending'] ?> en attente</span></div>
      <?php if ($ord_anomaly): ?><div class="kpi-anomaly <?= e($ord_anomaly[1]) ?>"><?= e($ord_anomaly[0]) ?></div><?php endif; ?>
      <div class="kpi-spark" data-spark="orders"></div>
    </div>
    <div class="kpi">
      <div class="kpi-icon"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 12h-4l-3 9L9 3l-3 9H2"/></svg></div>
      <div class="kpi-label">Panier moyen</div>
      <div class="kpi-value" data-countup data-value="<?= (float) $cur['aov'] ?>" data-suffix=" <?= e(CURRENCY_SYMBOL) ?>"><?= price($cur['aov']) ?></div>
      <div class="kpi-meta"><span class="kpi-trend <?= e($aov_cls) ?>"><?= e($aov_delta) ?></span><span class="kpi-meta-text">vs période précédente</span></div>
      <div class="kpi-spark" data-spark="aov"></div>
    </div>
    <div class="kpi">
      <div class="kpi-icon"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/></svg></div>
      <div class="kpi-label">Nouveaux clients</div>
      <div class="kpi-value" data-countup data-value="<?= (int) $cur_new_customers ?>"><?= (int) $cur_new_customers ?></div>
      <div class="kpi-meta"><span class="kpi-trend <?= e($cust_cls) ?>"><?= e($cust_delta) ?></span><span class="kpi-meta-text"><?= (int) $customers_total ?> au total</span></div>
      <div class="kpi-spark" data-spark="customers"></div>
    </div>
  </div>

  <div style="display: grid; grid-template-columns: 1.5fr 1fr; gap: 16px; margin-bottom: 24px;" class="dashboard-row">
    <div class="card">
      <div class="card-head">
        <div><h3>Évolution des revenus</h3><div class="head-meta"><?= e($range_meta) ?> · comparé à la période précédente</div></div>
      </div>
      <div class="card-body">
        <div id="revenue-chart"></div>
      </div>
    </div>

    <div class="card">
      <div class="card-head">
        <h3>Top produits</h3>
        <a href="products.php" style="font-size: 0.82rem; color: var(--olive); font-weight: 500;">Tout voir →</a>
      </div>
      <div class="card-body" style="padding: 0;">
        <?php foreach ($top_products as $p): ?>
          <div style="display: grid; grid-template-columns: auto 1fr auto; gap: 12px; padding: 14px 22px; align-items: center; border-bottom: 1px solid var(--line-soft);">
            <img src="<?= e($p['image_main']) ?>" style="width: 40px; height: 40px; border-radius: var(--radius-sm); object-fit: cover;">
            <div>
              <strong style="font-size: 0.88rem;"><?= e($p['name']) ?></strong>
              <div style="font-size: 0.75rem; color: var(--ink-mute);"><?= (int) $p['sales_count'] ?> ventes</div>
            </div>
            <strong class="cell-num"><?= price($p['sales_count'] * $p['price']) ?></strong>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
  </div>

  <div style="display: grid; grid-template-columns: 1fr 1.5fr; gap: 16px; margin-bottom: 24px;" class="dashboard-row">
    <div class="card">
      <div class="card-head">
        <h3>Revenus par catégorie</h3>
        <span class="head-meta"><?= e($range_meta) ?></span>
      </div>
      <div class="card-body">
        <?php if (empty($cat_revenue)): ?>
          <p class="empty-msg">Aucune vente sur cette période.</p>
        <?php else: ?>
          <div id="category-donut"></div>
        <?php endif; ?>
      </div>
    </div>

    <div class="card">
      <div class="card-head">
        <h3>Top villes</h3>
        <span class="head-meta"><?= count($top_cities) ?> villes</span>
      </div>
      <div class="card-body">
        <?php if (empty($top_cities)): ?>
          <p class="empty-msg">Aucune commande sur cette période.</p>
        <?php else: ?>
          <div id="cities-bar"></div>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <div style="display: grid; grid-template-columns: 1fr 1.2fr 1fr; gap: 16px; margin-bottom: 24px;" class="dashboard-row">
    <div class="card">
      <div class="card-head">
        <h3>Méthodes de paiement</h3>
        <span class="head-meta"><?= count($payment_breakdown) ?></span>
      </div>
      <div class="card-body">
        <?php if (empty($payment_breakdown)): ?>
          <p class="empty-msg">Aucune commande sur cette période.</p>
        <?php else: ?>
          <div id="payment-donut"></div>
        <?php endif; ?>
      </div>
    </div>

    <div class="card">
      <div class="card-head">
        <h3>Tunnel de commande</h3>
        <span class="head-meta">création → livraison</span>
      </div>
      <div class="card-body">
        <?php if (($funnel_values[0] ?? 0) === 0): ?>
          <p class="empty-msg">Aucune commande sur cette période.</p>
        <?php else: ?>
          <div id="order-funnel"></div>
        <?php endif; ?>
      </div>
    </div>

    <div class="card">
      <div class="card-head">
        <h3>Objectif du mois</h3>
        <span class="head-meta"><?= date('F Y') ?></span>
      </div>
      <div class="card-body" style="text-align: center; padding-top: 8px;">
        <div id="goal-radial"></div>
        <div class="goal-stats">
          <strong><?= price($goal_so_far) ?></strong>
          <span> / <?= price($goal_target) ?></span>
        </div>
      </div>
    </div>
  </div>

  <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 24px;" class="dashboard-row">
    <div class="card">
      <div class="card-head">
        <h3>Activité récente</h3>
        <span class="head-meta"><?= count($activity) ?> événements</span>
      </div>
      <div class="card-body" style="padding: 0;">
        <?php if (empty($activity)): ?>
          <p class="empty-msg">Aucune activité récente.</p>
        <?php else: ?>
          <ul class="activity-list">
            <?php foreach ($activity as $ev):
              $kind = $ev['kind'];
              $ago = time_ago($ev['created_at']);
              $title = '';
              $meta_html = '';
              $href = null;
              if ($kind === 'order') {
                $title = 'Commande #' . $ev['l1'];
                [$lbl, $cls] = order_status_label($ev['l3']);
                $meta_html = price((float) $ev['l2']) . ' · <span class="badge-status ' . e($cls) . '">' . e($lbl) . '</span>';
                $href = 'order-detail.php?id=' . (int) $ev['id'];
              } elseif ($kind === 'customer') {
                $name = trim($ev['l1']) ?: $ev['l2'];
                $title = 'Nouveau client : ' . $name;
                $meta_html = e($ev['l2']);
              } else { // review
                $title = 'Avis sur ' . $ev['l1'];
                $r = max(0, min(5, (int) $ev['l2']));
                $meta_html = '<span class="rating-stars">' . str_repeat('★', $r) . str_repeat('☆', 5 - $r) . '</span>';
              }
            ?>
              <li class="activity-item">
                <span class="activity-icon activity-<?= e($kind) ?>">
                  <?php if ($kind === 'order'): ?>
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 2L3 6v14a2 2 0 002 2h14a2 2 0 002-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 01-8 0"/></svg>
                  <?php elseif ($kind === 'customer'): ?>
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M16 21v-2a4 4 0 00-4-4H6a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 11h-6M19 8v6"/></svg>
                  <?php else: ?>
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
                  <?php endif; ?>
                </span>
                <div class="activity-body">
                  <strong><?php if ($href): ?><a href="<?= e($href) ?>"><?= e($title) ?></a><?php else: ?><?= e($title) ?><?php endif; ?></strong>
                  <div class="activity-meta"><?= $meta_html ?></div>
                </div>
                <span class="activity-time"><?= e($ago) ?></span>
              </li>
            <?php endforeach; ?>
          </ul>
        <?php endif; ?>
      </div>
    </div>

    <div class="card">
      <div class="card-head">
        <h3>Top 5 clients</h3>
        <span class="head-meta">par valeur LTV</span>
      </div>
      <div class="card-body" style="padding: 0;">
        <?php if (empty($top_customers)): ?>
          <p class="empty-msg">Aucun client avec commandes.</p>
        <?php else: ?>
          <?php
          $seg_classes = ['vip' => 'status-success', 'regular' => 'status-info', 'new' => 'status-neutral', 'inactive' => 'status-warning'];
          foreach ($top_customers as $i => $c):
            $name = trim(($c['first_name'] ?? '') . ' ' . ($c['last_name'] ?? '')) ?: ($c['email'] ?? 'Anonyme');
            $seg_cls = $seg_classes[$c['segment']] ?? 'status-neutral';
          ?>
            <div class="leaderboard-row">
              <span class="leaderboard-rank">#<?= $i + 1 ?></span>
              <span class="cell-customer-avatar"><?= e(initials($name)) ?></span>
              <div class="leaderboard-info">
                <strong><?= e($name) ?></strong>
                <div class="cell-mute"><?= (int) $c['total_orders'] ?> cmd · <span class="badge-status <?= e($seg_cls) ?>"><?= e(ucfirst($c['segment'])) ?></span></div>
              </div>
              <strong class="cell-num"><?= price((float) $c['lifetime_value']) ?></strong>
            </div>
          <?php endforeach; ?>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <div class="card" style="margin-bottom: 24px;">
    <div class="card-head">
      <h3>Quand les commandes arrivent</h3>
      <span class="head-meta">jour × heure · 30 derniers jours</span>
    </div>
    <div class="card-body">
      <div id="hour-heatmap"></div>
    </div>
  </div>

  <div style="display: grid; grid-template-columns: 1.5fr 1fr; gap: 16px;" class="dashboard-row">
    <div class="card">
      <div class="card-head">
        <h3>Commandes récentes</h3>
        <a href="orders.php" style="font-size: 0.82rem; color: var(--olive); font-weight: 500;">Tout voir →</a>
      </div>
      <table class="data-table">
        <thead><tr><th>Commande</th><th>Client</th><th>Statut</th><th>Total</th><th></th></tr></thead>
        <tbody>
          <?php foreach ($recent_orders as $o):
            [$lbl, $cls] = order_status_label($o['status']);
            $name = trim(($o['first_name'] ?? '') . ' ' . ($o['last_name'] ?? '')) ?: 'Anonyme';
          ?>
            <tr>
              <td><span class="cell-mono">#<?= e($o['order_number']) ?></span></td>
              <td><div class="cell-customer"><span class="cell-customer-avatar"><?= e(initials($name)) ?></span><span><strong style="font-size: 0.85rem;"><?= e($name) ?></strong><div class="cell-mute"><?= e($o['city'] ?? '—') ?></div></span></div></td>
              <td><span class="badge-status <?= e($cls) ?>"><?= e($lbl) ?></span></td>
              <td class="cell-num"><?= price($o['total']) ?></td>
              <td><a href="order-detail.php?id=<?= (int) $o['id'] ?>" style="color: var(--olive); font-weight: 500; font-size: 0.82rem;">Voir →</a></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>

    <div class="card">
      <div class="card-head">
        <h3>⚠ Stocks bas</h3>
        <span class="head-meta"><?= count($low_stock) ?> produits</span>
      </div>
      <div class="card-body" style="padding: 0;">
        <?php if (empty($low_stock)): ?>
          <div style="padding: 30px; text-align: center; color: var(--ink-mute); font-size: 0.88rem;">Aucune alerte 🎉</div>
        <?php else: ?>
          <?php foreach ($low_stock as $p): ?>
            <div style="padding: 12px 22px; border-bottom: 1px solid var(--line-soft); display: flex; justify-content: space-between; align-items: center;">
              <div>
                <strong style="font-size: 0.85rem;"><?= e($p['name']) ?></strong>
                <div class="cell-mute">SKU: <?= e($p['sku']) ?></div>
              </div>
              <span class="badge-status <?= $p['stock'] <= 3 ? 'status-danger' : 'status-warning' ?>"><?= (int) $p['stock'] ?> restants</span>
            </div>
          <?php endforeach; ?>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <style>
    @media (max-width: 1024px) { .dashboard-row { grid-template-columns: 1fr !important; } }
  </style>
</div>

<script id="dashboard-data" type="application/json"><?= json_encode([
    'currency' => CURRENCY_SYMBOL,
    'range'    => $range,
    'from'     => $from,
    'to'       => $to,
    'main'     => [
        'xLabels' => $x_labels,
        'cur'     => $rev_cur_data,
        'prv'     => $rev_prv_data,
        'prvDates'=> $prv_dates,
    ],
    'spark'    => [
        'revenue'   => $spark_revenue,
        'orders'    => $spark_orders_arr,
        'aov'       => $spark_aov,
        'customers' => $spark_customers,
    ],
    'cat'      => ['labels' => $cat_labels,     'values' => $cat_values],
    'cities'   => ['labels' => $city_labels,    'values' => $city_values],
    'pay'      => ['labels' => $payment_labels, 'values' => $payment_values],
    'funnel'   => ['labels' => $funnel_labels,  'values' => $funnel_values],
    'goal'     => ['target' => $goal_target,    'soFar'  => round($goal_so_far, 2), 'pct' => $goal_pct],
    'heatmap'  => $heatmap_series,
], JSON_UNESCAPED_UNICODE) ?></script>

<script>
(function(){
  const data = JSON.parse(document.getElementById('dashboard-data').textContent);
  const fmtCurrency = (v) => new Intl.NumberFormat('fr-FR', { maximumFractionDigits: 0 }).format(Math.round(v)) + ' ' + data.currency;
  const fmtDate = (iso) => {
    const d = new Date(iso + 'T00:00:00');
    return d.toLocaleDateString('fr-FR', { day: 'numeric', month: 'short' });
  };

  // Wait for ApexCharts/CountUp/Litepicker to load (script tags are deferred)
  function whenReady(fn) {
    if (window.ApexCharts && window.countUp && window.Litepicker) return fn();
    setTimeout(() => whenReady(fn), 30);
  }

  whenReady(function(){
    // ── Skeleton-loader patch on ApexCharts.render ─────────
    (function patchApex(){
      if (window.__apexSkelPatched) return;
      window.__apexSkelPatched = true;
      const proto = window.ApexCharts.prototype;
      const origRender = proto.render;
      proto.render = function() {
        const host = this.el;
        if (host && !host.querySelector('.chart-skel-overlay')) {
          const skel = document.createElement('div');
          skel.className = 'chart-skel-overlay';
          host.appendChild(skel);
          if (getComputedStyle(host).position === 'static') host.style.position = 'relative';
          const r = origRender.apply(this, arguments);
          Promise.resolve(r).finally(() => setTimeout(() => skel.remove(), 60));
          return r;
        }
        return origRender.apply(this, arguments);
      };
    })();

    // ── CountUp on KPI values ──────────────────────────────
    document.querySelectorAll('[data-countup]').forEach((el) => {
      const target = parseFloat(el.dataset.value || '0');
      const suffix = el.dataset.suffix || '';
      const decimals = (target % 1 !== 0) ? 0 : 0; // keep integer display, currency already rounded
      const c = new countUp.CountUp(el, target, {
        duration: 1.4,
        separator: ' ',
        decimal: ',',
        decimalPlaces: decimals,
        suffix: suffix,
      });
      if (!c.error) c.start();
    });

    // ── Main area chart: current vs previous period ────────
    const mainEl = document.getElementById('revenue-chart');
    if (mainEl) {
      const mainOpts = {
        chart: {
          type: 'area',
          height: 320,
          fontFamily: 'Inter, sans-serif',
          toolbar: { show: false },
          zoom: { enabled: false },
          animations: { enabled: true, easing: 'easeOutCubic', speed: 600 },
        },
        series: [
          { name: 'Période actuelle', data: data.main.cur },
          { name: 'Période précédente', data: data.main.prv },
        ],
        colors: ['#3A5A40', '#E0A458'],
        stroke: { curve: 'smooth', width: [3, 2], dashArray: [0, 5] },
        fill: {
          type: 'gradient',
          gradient: {
            shadeIntensity: 1,
            opacityFrom: [0.35, 0.05],
            opacityTo: [0.02, 0],
            stops: [0, 100],
          },
        },
        dataLabels: { enabled: false },
        markers: { size: 0, hover: { size: 5 } },
        grid: {
          borderColor: '#EFF1ED',
          strokeDashArray: 4,
          padding: { left: 10, right: 10 },
          xaxis: { lines: { show: false } },
          yaxis: { lines: { show: true } },
        },
        xaxis: {
          categories: data.main.xLabels,
          labels: {
            formatter: (v) => v ? fmtDate(v) : '',
            style: { colors: '#8A8E8C', fontSize: '11px' },
          },
          axisBorder: { show: false },
          axisTicks: { show: false },
          tooltip: { enabled: false },
        },
        yaxis: {
          labels: {
            formatter: (v) => fmtCurrency(v),
            style: { colors: '#8A8E8C', fontSize: '11px' },
          },
        },
        legend: {
          position: 'top',
          horizontalAlign: 'right',
          fontSize: '12px',
          fontWeight: 500,
          markers: { width: 10, height: 10, radius: 3 },
          labels: { colors: '#4A4E4C' },
        },
        tooltip: {
          shared: true,
          intersect: false,
          custom: function({ series, dataPointIndex, w }) {
            const curDate = data.main.xLabels[dataPointIndex] || '';
            const prvDate = data.main.prvDates[dataPointIndex] || '';
            const curVal = series[0][dataPointIndex] ?? 0;
            const prvVal = series[1][dataPointIndex] ?? 0;
            return (
              '<div style="padding:10px 12px;font-family:Inter,sans-serif;font-size:12px;background:#1F2421;color:#FAF6F0;border-radius:6px;">'
              + '<div style="font-weight:600;margin-bottom:6px;">' + fmtDate(curDate) + '</div>'
              + '<div style="display:flex;align-items:center;gap:8px;margin-bottom:3px;"><span style="width:8px;height:8px;border-radius:50%;background:#3A5A40;"></span><span style="opacity:.75;">Actuelle</span><strong style="margin-left:auto;">' + fmtCurrency(curVal) + '</strong></div>'
              + '<div style="display:flex;align-items:center;gap:8px;"><span style="width:8px;height:8px;border-radius:50%;background:#E0A458;"></span><span style="opacity:.75;">' + (prvDate ? fmtDate(prvDate) : 'Précédente') + '</span><strong style="margin-left:auto;">' + fmtCurrency(prvVal) + '</strong></div>'
              + '</div>'
            );
          },
        },
      };
      new ApexCharts(mainEl, mainOpts).render();
    }

    // ── Sparklines per KPI card ────────────────────────────
    const sparkColor = {
      revenue:   '#3A5A40',
      orders:    '#3A5A40',
      aov:       '#E0A458',
      customers: '#6B8A6E',
    };
    document.querySelectorAll('[data-spark]').forEach((el) => {
      const key = el.dataset.spark;
      const series = data.spark[key] || [];
      if (!series.length) return;
      new ApexCharts(el, {
        chart: { type: 'area', height: 50, sparkline: { enabled: true }, animations: { enabled: true, speed: 500 } },
        series: [{ data: series }],
        stroke: { curve: 'smooth', width: 2 },
        colors: [sparkColor[key] || '#3A5A40'],
        fill: { type: 'gradient', gradient: { opacityFrom: 0.4, opacityTo: 0, stops: [0, 100] } },
        tooltip: {
          fixed: { enabled: false },
          x: { show: false },
          y: {
            formatter: (v) => (key === 'revenue' || key === 'aov') ? fmtCurrency(v) : new Intl.NumberFormat('fr-FR').format(v),
            title: { formatter: () => '' },
          },
          marker: { show: false },
        },
      }).render();
    });

    // ── Brand palette (donuts / categorical) ───────────────
    const palette = ['#3A5A40', '#E0A458', '#C8553D', '#6B8A6E', '#A8412C', '#8AA38B', '#D4881F', '#2A4030'];
    const fmtNumber = (v) => new Intl.NumberFormat('fr-FR').format(v);

    // ── Revenue by category (donut) ────────────────────────
    const catEl = document.getElementById('category-donut');
    if (catEl && data.cat.values.length) {
      new ApexCharts(catEl, {
        chart: { type: 'donut', height: 280, fontFamily: 'Inter, sans-serif' },
        series: data.cat.values,
        labels: data.cat.labels,
        colors: palette,
        legend: { position: 'right', fontSize: '12px', labels: { colors: '#4A4E4C' }, markers: { width: 10, height: 10, radius: 3 } },
        stroke: { width: 0 },
        plotOptions: {
          pie: {
            donut: {
              size: '68%',
              labels: {
                show: true,
                name: { offsetY: -8, color: '#8A8E8C', fontSize: '11px', fontWeight: 500, fontFamily: 'Inter, sans-serif' },
                value: { fontSize: '18px', fontWeight: 600, color: '#1F2421', offsetY: 4, fontFamily: 'Inter, sans-serif', formatter: (v) => fmtCurrency(parseFloat(v)) },
                total: {
                  show: true,
                  label: 'Total',
                  color: '#8A8E8C',
                  fontSize: '11px',
                  fontWeight: 500,
                  formatter: (w) => fmtCurrency(w.globals.seriesTotals.reduce((a,b) => a+b, 0)),
                },
              },
            },
          },
        },
        dataLabels: { enabled: false },
        tooltip: { y: { formatter: (v) => fmtCurrency(v) } },
        responsive: [{ breakpoint: 700, options: { legend: { position: 'bottom' } } }],
      }).render();
    }

    // ── Top cities (horizontal bar) ────────────────────────
    const citiesEl = document.getElementById('cities-bar');
    if (citiesEl && data.cities.values.length) {
      const cityHeight = Math.max(220, data.cities.values.length * 32);
      new ApexCharts(citiesEl, {
        chart: { type: 'bar', height: cityHeight, fontFamily: 'Inter, sans-serif', toolbar: { show: false } },
        series: [{ name: 'Revenus', data: data.cities.values }],
        plotOptions: { bar: { horizontal: true, borderRadius: 4, barHeight: '70%' } },
        colors: ['#3A5A40'],
        xaxis: {
          categories: data.cities.labels,
          labels: { formatter: (v) => fmtCurrency(v), style: { colors: '#8A8E8C', fontSize: '11px' } },
          axisBorder: { show: false }, axisTicks: { show: false },
        },
        yaxis: { labels: { style: { colors: '#4A4E4C', fontSize: '12px' } } },
        grid: { borderColor: '#EFF1ED', strokeDashArray: 4, xaxis: { lines: { show: true } }, yaxis: { lines: { show: false } } },
        dataLabels: {
          enabled: true, textAnchor: 'start', offsetX: 8,
          formatter: (v) => fmtCurrency(v),
          style: { fontSize: '11px', colors: ['#FAF6F0'], fontWeight: 600 },
        },
        tooltip: { y: { formatter: (v) => fmtCurrency(v) } },
      }).render();
    }

    // ── Payment methods (donut) ────────────────────────────
    const payEl = document.getElementById('payment-donut');
    if (payEl && data.pay.values.length) {
      new ApexCharts(payEl, {
        chart: { type: 'donut', height: 240, fontFamily: 'Inter, sans-serif' },
        series: data.pay.values,
        labels: data.pay.labels,
        colors: ['#3A5A40', '#E0A458', '#C8553D'],
        legend: { position: 'bottom', fontSize: '12px', labels: { colors: '#4A4E4C' }, markers: { width: 10, height: 10, radius: 3 } },
        stroke: { width: 0 },
        plotOptions: { pie: { donut: { size: '62%', labels: { show: true, value: { fontSize: '16px', fontWeight: 600, color: '#1F2421', formatter: (v) => fmtCurrency(parseFloat(v)) }, total: { show: true, label: 'Total', color: '#8A8E8C', fontSize: '11px', formatter: (w) => fmtCurrency(w.globals.seriesTotals.reduce((a,b)=>a+b,0)) } } } } },
        dataLabels: { enabled: false },
        tooltip: { y: { formatter: (v) => fmtCurrency(v) } },
      }).render();
    }

    // ── Order fulfillment funnel (descending bar) ──────────
    const funnelEl = document.getElementById('order-funnel');
    if (funnelEl && data.funnel.values[0] > 0) {
      const top = data.funnel.values[0] || 1;
      new ApexCharts(funnelEl, {
        chart: { type: 'bar', height: 240, fontFamily: 'Inter, sans-serif', toolbar: { show: false } },
        series: [{ name: 'Commandes', data: data.funnel.values }],
        plotOptions: { bar: { horizontal: true, borderRadius: 4, distributed: true, barHeight: '65%' } },
        colors: ['#3A5A40', '#6B8A6E', '#E0A458', '#C8553D'],
        xaxis: {
          categories: data.funnel.labels,
          labels: { style: { colors: '#8A8E8C', fontSize: '11px' } },
          axisBorder: { show: false }, axisTicks: { show: false },
        },
        yaxis: { labels: { style: { colors: '#4A4E4C', fontSize: '12px' } } },
        grid: { borderColor: '#EFF1ED', strokeDashArray: 4, xaxis: { lines: { show: true } }, yaxis: { lines: { show: false } } },
        legend: { show: false },
        dataLabels: {
          enabled: true, textAnchor: 'start', offsetX: 8,
          formatter: function(val) {
            const pct = top > 0 ? Math.round((val / top) * 100) : 0;
            return val + '  ·  ' + pct + '%';
          },
          style: { fontSize: '11px', colors: ['#FAF6F0'], fontWeight: 600 },
        },
        tooltip: { y: { formatter: (v) => fmtNumber(v) + ' commandes' } },
      }).render();
    }

    // ── Monthly goal (radial) ──────────────────────────────
    const goalEl = document.getElementById('goal-radial');
    if (goalEl) {
      new ApexCharts(goalEl, {
        chart: { type: 'radialBar', height: 220, fontFamily: 'Inter, sans-serif', sparkline: { enabled: true } },
        series: [data.goal.pct],
        colors: ['#3A5A40'],
        plotOptions: {
          radialBar: {
            startAngle: -135, endAngle: 135,
            hollow: { size: '62%' },
            track: { background: '#EFF1ED', strokeWidth: '100%' },
            dataLabels: {
              name: { show: true, offsetY: -4, color: '#8A8E8C', fontSize: '11px', fontWeight: 500 },
              value: { color: '#1F2421', fontSize: '26px', fontWeight: 700, offsetY: 6, formatter: (v) => v + ' %' },
            },
          },
        },
        fill: { type: 'gradient', gradient: { shade: 'dark', type: 'horizontal', gradientToColors: ['#E0A458'], stops: [0, 100] } },
        stroke: { lineCap: 'round' },
        labels: ['Atteint'],
      }).render();
    }

    // ── Hour-of-day heatmap (orders by day × hour, 30 days) ─
    const heatEl = document.getElementById('hour-heatmap');
    if (heatEl && data.heatmap && data.heatmap.length) {
      new ApexCharts(heatEl, {
        chart: { type: 'heatmap', height: 320, fontFamily: 'Inter, sans-serif', toolbar: { show: false } },
        series: data.heatmap,
        dataLabels: { enabled: false },
        plotOptions: {
          heatmap: {
            radius: 4,
            useFillColorAsStroke: false,
            colorScale: {
              ranges: [
                { from: 0, to: 0,   color: '#F3F5F1', name: 'aucune' },
                { from: 1, to: 1,   color: '#C9D5C5', name: '1' },
                { from: 2, to: 4,   color: '#8AA38B', name: '2-4' },
                { from: 5, to: 999, color: '#3A5A40', name: '5+' },
              ],
            },
          },
        },
        xaxis: {
          labels: { style: { colors: '#8A8E8C', fontSize: '10px' }, rotate: 0, hideOverlappingLabels: true },
          axisBorder: { show: false }, axisTicks: { show: false },
        },
        yaxis: { labels: { style: { colors: '#4A4E4C', fontSize: '12px' } } },
        grid: { padding: { left: 0, right: 0 } },
        legend: { position: 'bottom', fontSize: '11px', markers: { width: 12, height: 12, radius: 2 } },
        tooltip: { y: { formatter: (v) => v + ' commande' + (v > 1 ? 's' : '') } },
      }).render();
    }

    // ── Litepicker for custom range ────────────────────────
    const customBtn = document.getElementById('range-custom-btn');
    if (customBtn) {
      const picker = new Litepicker({
        element: customBtn,
        singleMode: false,
        numberOfMonths: 2,
        numberOfColumns: 2,
        maxDate: new Date(),
        format: 'YYYY-MM-DD',
        startDate: data.range === 'custom' ? data.from : null,
        endDate:   data.range === 'custom' ? data.to   : null,
        setup: (p) => {
          p.on('selected', (start, end) => {
            const params = new URLSearchParams({
              range: 'custom',
              from: start.format('YYYY-MM-DD'),
              to: end.format('YYYY-MM-DD'),
            });
            window.location.search = params.toString();
          });
        },
      });
    }
  });
})();
</script>

<?php require __DIR__ . '/_includes/footer.php'; ?>
