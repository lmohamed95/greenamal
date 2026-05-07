<?php
/**
 * GreenAmal — Dashboard demo data generator.
 *
 * Returns a deterministic set of fake KPIs / charts / lists that overrides
 * the live DB-derived variables in admin/index.php when the
 * `dashboard_demo_mode` setting is on. No DB writes — purely presentational.
 */

require_once __DIR__ . '/helpers.php';

/**
 * Returns an associative array meant to be `extract()`ed into admin/index.php.
 * All variable names match the ones admin/index.php uses for rendering.
 */
function dashboard_demo_data(string $from, string $to, string $today, string $prev_from, string $prev_to): array {
    mt_srand(42); // deterministic
    $period_days = max(1, (int) round((strtotime($to) - strtotime($from)) / 86400) + 1);

    // ─── Daily series (current + previous) ──────────────────────────────
    $build_series = function (string $start, int $days, float $base_revenue): array {
        $series = [];
        for ($i = 0; $i < $days; $i++) {
            $d = date('Y-m-d', strtotime($start . " +$i days"));
            $weekend = in_array((int) date('N', strtotime($d)), [6, 7], true);
            $orders = max(2, (int) round(($weekend ? 11 : 7) + mt_rand(-3, 5)));
            $aov = 70 + mt_rand(-15, 50);
            $rev = round($base_revenue * 0.05 + $orders * $aov, 2);
            $series[] = ['date' => $d, 'rev' => $rev, 'orders' => $orders];
        }
        return $series;
    };

    $series_cur = $build_series($from, $period_days, 1000);
    $series_prv = $build_series($prev_from, $period_days, 850);

    $rev_total = round(array_sum(array_column($series_cur, 'rev')), 2);
    $orders_total = (int) array_sum(array_column($series_cur, 'orders'));
    $prv_rev_total = round(array_sum(array_column($series_prv, 'rev')), 2);
    $prv_orders_total = (int) array_sum(array_column($series_prv, 'orders'));

    $cur = [
        'revenue' => $rev_total,
        'orders'  => $orders_total,
        'aov'     => $orders_total > 0 ? round($rev_total / $orders_total, 2) : 0,
        'pending' => max(2, (int) round($orders_total * 0.08)),
    ];
    $prv = [
        'revenue' => $prv_rev_total,
        'orders'  => $prv_orders_total,
        'aov'     => $prv_orders_total > 0 ? round($prv_rev_total / $prv_orders_total, 2) : 0,
        'pending' => max(1, (int) round($prv_orders_total * 0.07)),
    ];

    $cur_new_customers = max(8, (int) round($orders_total * 0.45));
    $prv_new_customers = max(6, (int) round($prv_orders_total * 0.42));
    $customers_total   = 184;

    $delta = function (float $cur, float $prev): array {
        if ($prev <= 0) return $cur > 0 ? ['Nouveau', 'up'] : ['-', 'flat'];
        $pct = (($cur - $prev) / $prev) * 100;
        $cls = $pct > 0.5 ? 'up' : ($pct < -0.5 ? 'down' : 'flat');
        $arrow = $pct > 0.5 ? '↑' : ($pct < -0.5 ? '↓' : '→');
        return [$arrow . ' ' . number_format(abs($pct), 1, ',', ' ') . ' %', $cls];
    };
    [$rev_delta,  $rev_cls]  = $delta($cur['revenue'], $prv['revenue']);
    [$ord_delta,  $ord_cls]  = $delta((float) $cur['orders'], (float) $prv['orders']);
    [$aov_delta,  $aov_cls]  = $delta($cur['aov'], $prv['aov']);
    [$cust_delta, $cust_cls] = $delta((float) $cur_new_customers, (float) $prv_new_customers);

    // ─── Sparklines (last 14 days) ──────────────────────────────────────
    $spark_revenue = $spark_orders_arr = $spark_aov = $spark_customers = [];
    for ($i = 0; $i < 14; $i++) {
        $weekend = in_array((int) date('N', strtotime("-" . (13 - $i) . " days")), [6, 7], true);
        $o = max(2, ($weekend ? 11 : 7) + mt_rand(-3, 5));
        $a = 70 + mt_rand(-15, 50);
        $spark_revenue[]    = round($o * $a, 2);
        $spark_orders_arr[] = $o;
        $spark_aov[]        = $a;
        $spark_customers[]  = max(0, mt_rand(0, 6));
    }

    // ─── Top products (use real catalogue if present) ───────────────────
    $real = db_all("SELECT id, name, image_main, price FROM products WHERE status='active' ORDER BY RAND() LIMIT 5");
    $top_products = [];
    foreach ($real as $i => $p) {
        $top_products[] = $p + ['sales_count' => 38 - $i * 5];
    }

    // ─── Recent orders (synthetic) ──────────────────────────────────────
    $cities = ['Casablanca', 'Rabat', 'Marrakech', 'Fès', 'Tanger', 'Agadir', 'Meknès', 'Oujda'];
    $first_names = ['Amina', 'Youssef', 'Sara', 'Mehdi', 'Fatima', 'Rachid', 'Nadia', 'Karim', 'Hicham', 'Leila'];
    $last_names  = ['Benali', 'Alami', 'Mahmoudi', 'Khalifi', 'Zahra', 'Bouhssini', 'Amrani', 'El Idrissi'];
    $statuses = ['pending', 'processing', 'shipped', 'delivered', 'delivered', 'delivered'];
    $recent_orders = [];
    for ($i = 0; $i < 5; $i++) {
        $first = $first_names[mt_rand(0, count($first_names) - 1)];
        $last  = $last_names[mt_rand(0, count($last_names) - 1)];
        $recent_orders[] = [
            'id' => 1000 + $i,
            'order_number' => 'GA-2026-' . str_pad((string)(312 - $i), 4, '0', STR_PAD_LEFT),
            'first_name' => $first, 'last_name' => $last,
            'shipping_name' => $first . ' ' . $last,
            'city' => $cities[mt_rand(0, count($cities) - 1)],
            'shipping_city' => $cities[mt_rand(0, count($cities) - 1)],
            'total' => mt_rand(80, 480),
            'status' => $statuses[mt_rand(0, count($statuses) - 1)],
            'payment_method' => mt_rand(0, 2) ? 'cod' : 'cmi',
            'created_at' => date('Y-m-d H:i:s', strtotime("-" . $i . " hours")),
        ];
    }

    // ─── Low stock (real catalogue intersect, demo numbers) ─────────────
    $low_stock = db_all("SELECT id, name, sku FROM products WHERE status='active' ORDER BY RAND() LIMIT 3");
    foreach ($low_stock as &$p) $p['stock'] = mt_rand(1, 4);
    unset($p);

    // ─── Revenue by category ────────────────────────────────────────────
    $cats = ['Huiles essentielles', 'Eaux florales', 'Couscous artisanal', 'Savons', 'Plantes', 'Packs', 'Huiles végétales'];
    $cat_revenue = [];
    foreach ($cats as $i => $name) {
        $cat_revenue[] = ['name' => $name, 'revenue' => round($rev_total * (0.22 - $i * 0.025) * (0.9 + mt_rand(0, 30) / 100), 2)];
    }
    usort($cat_revenue, fn($a, $b) => $b['revenue'] <=> $a['revenue']);
    $cat_labels = array_map(fn($r) => $r['name'], $cat_revenue);
    $cat_values = array_map(fn($r) => $r['revenue'], $cat_revenue);

    // ─── Top cities ─────────────────────────────────────────────────────
    $top_cities = [];
    foreach (['Casablanca', 'Rabat', 'Marrakech', 'Tanger', 'Fès', 'Agadir', 'Meknès', 'Oujda'] as $i => $city) {
        $top_cities[] = [
            'city' => $city,
            'orders_n' => 38 - $i * 4,
            'revenue' => round($rev_total * (0.28 - $i * 0.035) * (0.85 + mt_rand(0, 30) / 100), 2),
        ];
    }
    $city_labels = array_map(fn($r) => $r['city'], $top_cities);
    $city_values = array_map(fn($r) => $r['revenue'], $top_cities);

    // ─── Payment methods ────────────────────────────────────────────────
    $payment_breakdown = [
        ['method' => 'cod',      'orders_n' => (int) round($orders_total * 0.78), 'revenue' => round($rev_total * 0.74, 2)],
        ['method' => 'cmi',      'orders_n' => (int) round($orders_total * 0.18), 'revenue' => round($rev_total * 0.22, 2)],
        ['method' => 'transfer', 'orders_n' => (int) round($orders_total * 0.04), 'revenue' => round($rev_total * 0.04, 2)],
    ];
    $payment_labels = ['COD', 'CMI', 'Virement'];
    $payment_values = array_map(fn($r) => $r['revenue'], $payment_breakdown);

    // ─── Funnel ─────────────────────────────────────────────────────────
    $f_created   = $orders_total + $cur['pending'];
    $f_confirmed = (int) round($f_created   * 0.92);
    $f_shipped   = (int) round($f_confirmed * 0.88);
    $f_delivered = (int) round($f_shipped   * 0.95);
    $funnel_row = ['created' => $f_created, 'confirmed' => $f_confirmed, 'shipped' => $f_shipped, 'delivered' => $f_delivered];
    $funnel_stages = [
        ['Commandes créées', $f_created],
        ['Confirmées',       $f_confirmed],
        ['Expédiées',        $f_shipped],
        ['Livrées',          $f_delivered],
    ];
    $funnel_labels = array_column($funnel_stages, 0);
    $funnel_values = array_column($funnel_stages, 1);

    // ─── Goal ───────────────────────────────────────────────────────────
    $goal_target  = 60000;
    $goal_so_far  = round($rev_total * (date('j') / 30) * 0.65, 2);
    $goal_pct     = $goal_target > 0 ? min(100, round(($goal_so_far / $goal_target) * 100, 1)) : 0;

    // ─── Activity feed ──────────────────────────────────────────────────
    $activity = [];
    for ($i = 0; $i < 12; $i++) {
        $kind = ['order', 'order', 'customer', 'review', 'order'][mt_rand(0, 4)];
        $first = $first_names[mt_rand(0, count($first_names) - 1)];
        $last  = $last_names[mt_rand(0, count($last_names) - 1)];
        $created = date('Y-m-d H:i:s', strtotime("-" . ($i * 3 + mt_rand(0, 30)) . " minutes"));
        if ($kind === 'order') {
            $activity[] = [
                'kind' => 'order', 'id' => 100 + $i,
                'l1' => 'GA-2026-' . str_pad((string)(311 - $i), 4, '0', STR_PAD_LEFT),
                'l2' => (string) mt_rand(80, 480),
                'l3' => $statuses[mt_rand(0, count($statuses) - 1)],
                'created_at' => $created,
            ];
        } elseif ($kind === 'customer') {
            $activity[] = [
                'kind' => 'customer', 'id' => 200 + $i,
                'l1' => $first . ' ' . $last,
                'l2' => strtolower($first . '.' . $last[0]) . '@email.com',
                'l3' => 'new',
                'created_at' => $created,
            ];
        } else {
            $activity[] = [
                'kind' => 'review', 'id' => 300 + $i,
                'l1' => $top_products[mt_rand(0, count($top_products) - 1)]['name'] ?? 'Produit',
                'l2' => (string) mt_rand(4, 5),
                'l3' => 'approved',
                'created_at' => $created,
            ];
        }
    }

    // ─── Top customers ──────────────────────────────────────────────────
    $top_customers = [];
    for ($i = 0; $i < 5; $i++) {
        $first = $first_names[$i % count($first_names)];
        $last  = $last_names[$i % count($last_names)];
        $top_customers[] = [
            'id' => 500 + $i,
            'first_name' => $first, 'last_name' => $last,
            'email' => strtolower($first . '.' . $last[0]) . '@email.com',
            'total_orders' => 12 - $i * 2,
            'lifetime_value' => 4200 - $i * 600,
            'segment' => $i < 2 ? 'vip' : 'regular',
        ];
    }

    // ─── Heatmap (orders by day-of-week × hour) ─────────────────────────
    $dow_render_order = [1, 7, 6, 5, 4, 3, 2];
    $dow_label = [1 => 'Dim', 2 => 'Lun', 3 => 'Mar', 4 => 'Mer', 5 => 'Jeu', 6 => 'Ven', 7 => 'Sam'];
    $heat_lookup = [];
    foreach ($dow_render_order as $d) {
        for ($h = 0; $h < 24; $h++) {
            // peak around 10-13 and 19-22, low at night
            $peak = (($h >= 10 && $h <= 13) || ($h >= 19 && $h <= 22)) ? 6 : ($h < 7 || $h > 23 ? 0 : 2);
            $weekend_boost = in_array($d, [6, 7, 1], true) ? 2 : 0;
            $heat_lookup[$d][$h] = max(0, $peak + $weekend_boost + mt_rand(-2, 3));
        }
    }
    $heatmap_series = [];
    foreach ($dow_render_order as $d) {
        $points = [];
        for ($h = 0; $h < 24; $h++) {
            $points[] = ['x' => sprintf('%02dh', $h), 'y' => $heat_lookup[$d][$h]];
        }
        $heatmap_series[] = ['name' => $dow_label[$d], 'data' => $points];
    }

    // ─── Anomalies ──────────────────────────────────────────────────────
    $today_rev = round(end($series_cur)['rev'], 2);
    $today_orders = (int) end($series_cur)['orders'];
    reset($series_cur);
    $rev_anomaly = ['↑ 32 % aujourd\'hui', 'up'];
    $ord_anomaly = null;

    // ─── Chart x labels & series arrays ─────────────────────────────────
    $x_labels = array_map(fn($r) => $r['date'], $series_cur);
    $rev_cur_data = array_map(fn($r) => round($r['rev'], 2), $series_cur);
    $rev_prv_data = array_map(fn($r) => round($r['rev'], 2), $series_prv);
    $prv_dates = array_map(fn($r) => $r['date'], $series_prv);

    return compact(
        'cur', 'prv',
        'cur_new_customers', 'prv_new_customers', 'customers_total',
        'rev_delta', 'rev_cls', 'ord_delta', 'ord_cls', 'aov_delta', 'aov_cls', 'cust_delta', 'cust_cls',
        'series_cur', 'series_prv',
        'spark_revenue', 'spark_orders_arr', 'spark_aov', 'spark_customers',
        'top_products', 'recent_orders', 'low_stock',
        'cat_revenue', 'cat_labels', 'cat_values',
        'top_cities', 'city_labels', 'city_values',
        'payment_breakdown', 'payment_labels', 'payment_values',
        'funnel_row', 'funnel_stages', 'funnel_labels', 'funnel_values',
        'goal_target', 'goal_so_far', 'goal_pct',
        'activity', 'top_customers',
        'heat_lookup', 'heatmap_series',
        'today_rev', 'today_orders', 'rev_anomaly', 'ord_anomaly',
        'x_labels', 'rev_cur_data', 'rev_prv_data', 'prv_dates'
    );
}
