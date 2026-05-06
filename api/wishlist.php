<?php
/**
 * POST /api/wishlist.php
 *  - op=toggle&product_id=N → toggles, returns { ok, in_wishlist, count }
 *  - op=remove&product_id=N → removes, returns { ok, count }
 *  - op=list                → returns { ok, ids[] }
 *
 * Requires customer login.
 */
require_once __DIR__ . '/../includes/helpers.php';
header('Content-Type: application/json; charset=utf-8');

if (!customer_logged_in()) {
    http_response_code(401);
    echo json_encode(['ok' => false, 'error' => 'login_required']);
    exit;
}

$user_id = (int) customer_user()['id'];
$op = $_REQUEST['op'] ?? 'list';

if ($op === 'list') {
    $ids = array_map('intval', array_column(
        db_all('SELECT product_id FROM wishlists WHERE customer_id = ?', [$user_id]),
        'product_id'
    ));
    echo json_encode(['ok' => true, 'ids' => $ids]);
    exit;
}

$pid = (int) ($_REQUEST['product_id'] ?? 0);
if ($pid <= 0) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'bad_product']);
    exit;
}

if ($op === 'toggle') {
    $exists = db_value('SELECT id FROM wishlists WHERE customer_id = ? AND product_id = ?', [$user_id, $pid]);
    if ($exists) {
        db_query('DELETE FROM wishlists WHERE id = ?', [$exists]);
        $in = false;
    } else {
        db_query('INSERT IGNORE INTO wishlists (customer_id, product_id) VALUES (?, ?)', [$user_id, $pid]);
        $in = true;
    }
    $count = (int) db_value('SELECT COUNT(*) FROM wishlists WHERE customer_id = ?', [$user_id]);
    echo json_encode(['ok' => true, 'in_wishlist' => $in, 'count' => $count]);
    exit;
}

if ($op === 'remove') {
    db_query('DELETE FROM wishlists WHERE customer_id = ? AND product_id = ?', [$user_id, $pid]);
    $count = (int) db_value('SELECT COUNT(*) FROM wishlists WHERE customer_id = ?', [$user_id]);
    echo json_encode(['ok' => true, 'count' => $count]);
    exit;
}

http_response_code(400);
echo json_encode(['ok' => false, 'error' => 'bad_op']);
