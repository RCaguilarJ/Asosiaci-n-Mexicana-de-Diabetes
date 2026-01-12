<?php
// includes/sync_queue.php
// Helper para encolar y gestionar sincronizaciones fallidas.
require_once __DIR__ . '/db.php';

function enqueue_sync($tipo, array $payload, $pdo_local = null, $errorMessage = null) {
    if ($pdo_local === null) {
        global $pdo;
        $pdo_local = $pdo;
    }
    if (!($pdo_local instanceof PDO)) {
        error_log('enqueue_sync: PDO local no disponible');
        return false;
    }

    $nextAttempt = date('Y-m-d H:i:s', time() + 60); // primer reintento en 60s
    $sql = "INSERT INTO sync_queue (tipo, payload, last_error, next_attempt) VALUES (:tipo, :payload, :last_error, :next_attempt)";
    $stmt = $pdo_local->prepare($sql);
    try {
        $stmt->execute([
            ':tipo' => $tipo,
            ':payload' => json_encode($payload, JSON_UNESCAPED_UNICODE),
            ':last_error' => $errorMessage,
            ':next_attempt' => $nextAttempt
        ]);
        return $pdo_local->lastInsertId();
    } catch (Exception $e) {
        error_log('enqueue_sync failed: ' . $e->getMessage());
        return false;
    }
}

function mark_sync_done($id, $pdo_local = null) {
    if ($pdo_local === null) { global $pdo; $pdo_local = $pdo; }
    $stmt = $pdo_local->prepare("UPDATE sync_queue SET status='done', updated_at = NOW() WHERE id = ?");
    return $stmt->execute([$id]);
}

function mark_sync_failed($id, $error, $pdo_local = null) {
    if ($pdo_local === null) { global $pdo; $pdo_local = $pdo; }
    $stmt = $pdo_local->prepare("UPDATE sync_queue SET attempts = attempts + 1, last_error = :err, next_attempt = :na, status = CASE WHEN attempts+1 >= max_attempts THEN 'failed' ELSE 'pending' END WHERE id = :id");
    $nextAttempt = date('Y-m-d H:i:s', time() + 60 * 5); // backoff 5 minutos
    return $stmt->execute([':err' => substr($error,0,1000), ':na' => $nextAttempt, ':id' => $id]);
}

?>