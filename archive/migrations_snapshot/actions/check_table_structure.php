<?php
session_start();
header('Content-Type: application/json');

require '../includes/db.php';

try {
    // Verificar estructura de la tabla citas
    $stmt = $pdo->query("DESCRIBE citas");
    $columns = $stmt->fetchAll();
    
    echo json_encode([
        'tabla_estructura' => $columns,
        'mensaje' => 'Estructura de la tabla citas'
    ]);
    
} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>