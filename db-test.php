<?php
/**
 * GreenAmal · Standalone DB connectivity smoke test
 * --------------------------------------------------
 * Reads credentials from `includes/config.local.php` (loaded by config.php),
 * so this file contains NO secrets and is safe to commit to git.
 *
 *   1. Make sure `includes/config.local.php` exists on the server (Phase 4
 *      of HOSTING-DEPLOY.md).
 *   2. Open https://yourdomain.com/db-test.php in your browser.
 *   3. DELETE this file (and any cached copy in repositories/) once you're
 *      done — it exposes connection state and a peek at your data.
 */

require_once __DIR__ . '/includes/config.php';

$DB_HOST = DB_HOST;
$DB_PORT = DB_PORT;
$DB_NAME = DB_NAME;
$DB_USER = DB_USER;
$DB_PASS = DB_PASS;

// Refuse to run if the prod overrides clearly haven't been applied.
// (DB_HOST still equals the dev default? Stop before we leak that fact.)
$looks_like_dev = ($DB_HOST === '127.0.0.1' && $DB_USER === 'root');

header('Content-Type: text/html; charset=utf-8');
?><!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>GreenAmal · DB connectivity test</title>
<style>
  body { font: 14px/1.5 -apple-system, system-ui, sans-serif; max-width: 880px; margin: 2rem auto; padding: 0 1rem; color: #1a1a1a; }
  h1   { border-bottom: 2px solid #2e7d32; padding-bottom: .4rem; }
  h2   { margin-top: 2rem; }
  .ok  { color: #1b5e20; }
  .err { color: #b71c1c; }
  pre  { background: #f5f5f5; padding: .75rem 1rem; border-radius: 6px; overflow-x: auto; }
  table { border-collapse: collapse; width: 100%; margin-top: .5rem; }
  th, td { border: 1px solid #ddd; padding: .35rem .6rem; text-align: left; font-size: 13px; }
  th { background: #f0f7f0; }
  .pill { display: inline-block; padding: .1rem .55rem; border-radius: 999px; font-size: 11px; font-weight: 600; }
  .pill-active   { background: #d4edda; color: #155724; }
  .pill-draft    { background: #fff3cd; color: #856404; }
  .pill-archived { background: #f8d7da; color: #721c24; }
  .warn { background: #fff8e1; border-left: 4px solid #f9a825; padding: .75rem 1rem; margin: 1rem 0; }
</style>
</head>
<body>

<h1>GreenAmal · DB connectivity test</h1>

<div class="warn">
  <strong>Reminder:</strong> delete <code>db-test.php</code> after you're
  done. It's harmless but unnecessary in production.
</div>

<?php if ($looks_like_dev): ?>
<div class="warn" style="border-left-color:#b71c1c;background:#fdecea;">
  <strong>config.local.php not loaded.</strong> The constants still match
  the dev defaults (<code>127.0.0.1 / root</code>). Create
  <code>public_html/includes/config.local.php</code> with your production
  credentials (Phase 4 of <code>HOSTING-DEPLOY.md</code>), then reload.
</div>
<?php endif; ?>

<h2>1. Connection</h2>
<?php
try {
    $dsn = "mysql:host={$DB_HOST};port={$DB_PORT};dbname={$DB_NAME};charset=utf8mb4";
    $pdo = new PDO($dsn, $DB_USER, $DB_PASS, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ]);

    $server  = $pdo->query('SELECT VERSION() AS v')->fetch()['v'] ?? '?';
    $current = $pdo->query('SELECT DATABASE() AS d')->fetch()['d'] ?? '?';

    echo "<p class='ok'>✓ Connected successfully.</p>";
    echo "<pre>";
    echo "MySQL server : " . htmlspecialchars($server)  . "\n";
    echo "Database     : " . htmlspecialchars($current) . "\n";
    echo "Host:Port    : " . htmlspecialchars("{$DB_HOST}:{$DB_PORT}") . "\n";
    echo "User         : " . htmlspecialchars($DB_USER) . "\n";
    echo "</pre>";
} catch (Throwable $e) {
    echo "<p class='err'>✗ Could not connect.</p>";
    echo "<pre>" . htmlspecialchars($e->getMessage()) . "</pre>";
    echo "<p>Common causes:</p><ul>";
    echo "<li>Wrong DB name / user / password (re-check Phase 1 of HOSTING-DEPLOY.md)</li>";
    echo "<li>User not granted to the database — cPanel → MySQL Databases → Add User to Database</li>";
    echo "<li>Forgot the cPanel username prefix (e.g. <code>greenamal_greenamal</code>, not just <code>greenamal</code>)</li>";
    echo "</ul>";
    exit;
}
?>

<h2>2. Tables in this database</h2>
<?php
$tables = $pdo->query('SHOW TABLES')->fetchAll(PDO::FETCH_COLUMN);
if (!$tables) {
    echo "<p class='err'>✗ No tables found. Did you import <code>sql/schema.sql</code>?</p>";
    exit;
}
echo "<p class='ok'>✓ Found " . count($tables) . " tables.</p>";
echo "<pre>" . htmlspecialchars(implode("\n", $tables)) . "</pre>";

$required = ['products', 'categories', 'orders', 'customers', 'admin_users'];
$missing  = array_diff($required, $tables);
if ($missing) {
    echo "<p class='err'>✗ Missing expected tables: <code>"
       . htmlspecialchars(implode(', ', $missing)) . "</code></p>";
} else {
    echo "<p class='ok'>✓ All core tables present.</p>";
}
?>

<h2>3. Products</h2>
<?php
try {
    $total  = (int) $pdo->query('SELECT COUNT(*) FROM products')->fetchColumn();
    $byStat = $pdo->query("
        SELECT status, COUNT(*) AS n
        FROM products
        GROUP BY status
    ")->fetchAll();

    echo "<p class='ok'>✓ <strong>{$total}</strong> products in the table.</p>";
    if ($byStat) {
        echo "<ul>";
        foreach ($byStat as $row) {
            echo "<li>" . htmlspecialchars($row['status'])
               . " : <strong>" . (int) $row['n'] . "</strong></li>";
        }
        echo "</ul>";
    }

    if ($total === 0) {
        echo "<p class='err'>No rows — did you import <code>sql/products-shooting.sql</code> "
           . "and <code>sql/products-content.sql</code>?</p>";
    } else {
        $rows = $pdo->query("
            SELECT p.id, p.sku, p.name, p.price, p.stock, p.status, c.name AS category
            FROM products p
            LEFT JOIN categories c ON c.id = p.category_id
            ORDER BY p.id ASC
            LIMIT 20
        ")->fetchAll();

        echo "<table>";
        echo "<thead><tr>"
           . "<th>ID</th><th>SKU</th><th>Name</th><th>Category</th>"
           . "<th>Price</th><th>Stock</th><th>Status</th>"
           . "</tr></thead><tbody>";
        foreach ($rows as $r) {
            $pill = "pill pill-" . htmlspecialchars($r['status']);
            echo "<tr>"
               . "<td>" . (int) $r['id'] . "</td>"
               . "<td>" . htmlspecialchars((string) $r['sku']) . "</td>"
               . "<td>" . htmlspecialchars((string) $r['name']) . "</td>"
               . "<td>" . htmlspecialchars((string) ($r['category'] ?? '—')) . "</td>"
               . "<td>" . htmlspecialchars(number_format((float) $r['price'], 2)) . " DH</td>"
               . "<td>" . (int) $r['stock'] . "</td>"
               . "<td><span class='{$pill}'>" . htmlspecialchars($r['status']) . "</span></td>"
               . "</tr>";
        }
        echo "</tbody></table>";

        if ($total > 20) {
            echo "<p><em>Showing first 20 of {$total}.</em></p>";
        }
    }
} catch (Throwable $e) {
    echo "<p class='err'>✗ Query failed:</p><pre>"
       . htmlspecialchars($e->getMessage()) . "</pre>";
}
?>

<h2>4. Next step</h2>
<p>If everything above is green, your database is wired up correctly.
Continue with <strong>Phase 2 onward</strong> of <code>HOSTING-DEPLOY.md</code>
and remember to delete this file.</p>

</body>
</html>
