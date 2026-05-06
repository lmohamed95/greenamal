<?php
/**
 * Helper: build cart state JSON response
 */
require_once __DIR__ . '/../includes/helpers.php';

function cart_state_response(): void {
    $items = array_values(cart_get());
    json_response([
        'items'    => $items,
        'count'    => cart_count(),
        'subtotal' => cart_subtotal(),
    ]);
}
