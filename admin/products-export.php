<?php
/**
 * Export all products to CSV (Excel-compatible).
 *
 * Format choices:
 *  - UTF-8 BOM + semicolon separator → Excel (French locale) opens it cleanly
 *    without the "all in one column" issue.
 *  - First column is ID, used as the match key on re-import. Owners change
 *    Prix / Prix barré / Stock; everything else is read-only on import.
 */

require_once __DIR__ . '/../includes/auth.php';
admin_require_login();

$rows = db_all("
    SELECT p.id, p.sku, p.name, c.name AS category_name,
           p.price, p.compare_at_price, p.stock, p.status
    FROM products p
    LEFT JOIN categories c ON c.id = p.category_id
    ORDER BY c.name, p.name
");

$filename = 'greenamal-produits-' . date('Y-m-d') . '.csv';

header('Content-Type: text/csv; charset=UTF-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Cache-Control: no-store, no-cache, must-revalidate');
header('Pragma: no-cache');

$out = fopen('php://output', 'w');

// UTF-8 BOM — makes Excel detect encoding correctly.
fwrite($out, "\xEF\xBB\xBF");

// Header row. The import matches columns by these French labels.
fputcsv($out, [
    'ID',
    'SKU',
    'Nom',
    'Catégorie',
    'Prix (DH)',
    'Prix barré (DH)',
    'Stock',
    'Statut',
], ';');

foreach ($rows as $r) {
    fputcsv($out, [
        $r['id'],
        $r['sku'] ?? '',
        $r['name'],
        $r['category_name'] ?? '',
        number_format((float) $r['price'], 2, '.', ''),
        $r['compare_at_price'] !== null ? number_format((float) $r['compare_at_price'], 2, '.', '') : '',
        (int) $r['stock'],
        $r['status'],
    ], ';');
}

fclose($out);
exit;
