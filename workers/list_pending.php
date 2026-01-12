<?php
// workers/list_pending.php
// Muestra las filas pendientes en sync_queue
chdir(__DIR__ . '/..');
require_once __DIR__ . '/../includes/db.php';

if (!isset($pdo) || !$pdo) {
    fwrite(STDERR, "No hay conexión PDO disponible (includes/db.php).\n");
    exit(1);
}

try {
    $stmt = $pdo->query("SELECT id, tipo, status, attempts, next_attempt, created_at, updated_at FROM sync_queue WHERE status = 'pending' ORDER BY created_at ASC");
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($rows, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . PHP_EOL;
} catch (Exception $e) {
    fwrite(STDERR, "Error consultando sync_queue: " . $e->getMessage() . "\n");
    exit(1);
}
