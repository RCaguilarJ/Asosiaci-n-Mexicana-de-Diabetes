<?php
// workers/check_sync_row.php
// Uso: php workers/check_sync_row.php [id]

chdir(__DIR__ . '/..');
require_once __DIR__ . '/../includes/db.php';

if (!isset($pdo) || !$pdo) {
    fwrite(STDERR, "Error: connection $pdo no disponible desde includes/db.php\n");
    exit(1);
}

$id = isset($argv[1]) ? (int)$argv[1] : 1;

try {
    $stmt = $pdo->prepare('SELECT id, tipo, status, attempts, last_error, updated_at FROM sync_queue WHERE id = ?');
    $stmt->execute([$id]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$row) {
        echo json_encode(null) . PHP_EOL;
        exit(0);
    }
    echo json_encode($row, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . PHP_EOL;
} catch (Exception $e) {
    fwrite(STDERR, "Error al consultar la base de datos: " . $e->getMessage() . "\n");
    exit(1);
}
