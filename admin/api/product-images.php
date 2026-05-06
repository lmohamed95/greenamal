<?php
/**
 * Product gallery API.
 *
 * GET    ?product_id=N    → list images
 * POST   action=add       → attach a new image (body: product_id, url)
 * POST   action=remove    → detach image (body: id)
 * POST   action=reorder   → reorder (body: product_id, ids[] in display order)
 */

require_once __DIR__ . '/../../includes/auth.php';
admin_require_login();

header('Content-Type: application/json; charset=utf-8');

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $product_id = (int) ($_GET['product_id'] ?? 0);
    if ($product_id <= 0) json_response(['ok' => false, 'error' => 'bad_id'], 400);
    $rows = db_all(
        'SELECT id, url, display_order FROM product_images WHERE product_id = ? ORDER BY display_order ASC, id ASC',
        [$product_id]
    );
    json_response(['ok' => true, 'images' => $rows]);
}

if ($method === 'POST') {
    $body = json_decode(file_get_contents('php://input'), true) ?? $_POST;
    $action = $body['action'] ?? '';

    if ($action === 'add') {
        $product_id = (int) ($body['product_id'] ?? 0);
        $url        = trim($body['url'] ?? '');
        if ($product_id <= 0 || $url === '') json_response(['ok' => false, 'error' => 'missing_params'], 400);

        // Compute next display_order
        $next = (int) db_value('SELECT COALESCE(MAX(display_order), -1) + 1 FROM product_images WHERE product_id = ?', [$product_id]);

        $id = db_insert('product_images', [
            'product_id'    => $product_id,
            'url'           => $url,
            'display_order' => $next,
        ]);
        json_response(['ok' => true, 'id' => $id, 'url' => $url, 'display_order' => $next]);
    }

    if ($action === 'remove') {
        $id = (int) ($body['id'] ?? 0);
        if ($id <= 0) json_response(['ok' => false, 'error' => 'bad_id'], 400);
        db_query('DELETE FROM product_images WHERE id = ?', [$id]);
        json_response(['ok' => true]);
    }

    if ($action === 'reorder') {
        $product_id = (int) ($body['product_id'] ?? 0);
        $ids        = array_map('intval', $body['ids'] ?? []);
        foreach ($ids as $order => $id) {
            db_query('UPDATE product_images SET display_order = ? WHERE id = ? AND product_id = ?',
                [$order, $id, $product_id]);
        }
        json_response(['ok' => true]);
    }

    json_response(['ok' => false, 'error' => 'unknown_action'], 400);
}

json_response(['ok' => false, 'error' => 'method_not_allowed'], 405);
