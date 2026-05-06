<?php
/**
 * GreenAmal — Database connection (PDO)
 */

require_once __DIR__ . '/config.php';

function db(): PDO {
    static $pdo = null;
    if ($pdo === null) {
        $dsn = sprintf('mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4', DB_HOST, DB_PORT, DB_NAME);
        try {
            $pdo = new PDO($dsn, DB_USER, DB_PASS, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]);
        } catch (PDOException $e) {
            if (APP_DEBUG) {
                http_response_code(500);
                die("Database connection failed: " . $e->getMessage() . "<br><br>"
                    . "Check <code>includes/config.php</code> — DB_HOST, DB_PORT, DB_NAME, DB_USER, DB_PASS.<br>"
                    . "Did you import <code>sql/schema.sql</code> and <code>sql/seed.sql</code>?");
            }
            http_response_code(503);
            die('Service temporarily unavailable.');
        }
    }
    return $pdo;
}

/**
 * Convenience helpers
 */
function db_query(string $sql, array $params = []): PDOStatement {
    $stmt = db()->prepare($sql);
    $stmt->execute($params);
    return $stmt;
}

function db_one(string $sql, array $params = []): ?array {
    $row = db_query($sql, $params)->fetch();
    return $row === false ? null : $row;
}

function db_all(string $sql, array $params = []): array {
    return db_query($sql, $params)->fetchAll();
}

function db_value(string $sql, array $params = []) {
    $val = db_query($sql, $params)->fetchColumn();
    return $val === false ? null : $val;
}

function db_insert(string $table, array $data): int {
    $cols = array_keys($data);
    $placeholders = array_map(fn($c) => ':' . $c, $cols);
    $sql = sprintf(
        'INSERT INTO %s (%s) VALUES (%s)',
        $table,
        implode(', ', $cols),
        implode(', ', $placeholders)
    );
    db_query($sql, $data);
    return (int) db()->lastInsertId();
}
