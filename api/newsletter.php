<?php
require_once __DIR__ . '/../includes/helpers.php';

$body = json_decode(file_get_contents('php://input'), true) ?? [];
$email = trim($body['email'] ?? '');
$source = $body['source'] ?? 'site';

if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
    db_query(
        'INSERT IGNORE INTO newsletter_subscribers (email, source) VALUES (?, ?)',
        [$email, $source]
    );
    json_response(['ok' => true]);
}
json_response(['ok' => false, 'error' => 'invalid_email'], 400);
