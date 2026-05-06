<?php
require_once __DIR__ . '/_state.php';

$body = json_decode(file_get_contents('php://input'), true) ?? [];
$product_id = (int) ($body['product_id'] ?? 0);

if ($product_id > 0) {
    cart_remove($product_id);
}
cart_state_response();
