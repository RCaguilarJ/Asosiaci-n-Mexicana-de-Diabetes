<?php
/**
 * Migration: agregar columna 'rol' si no existe
 * Uso: abrir este archivo en el navegador o via curl
 * Protegido por token (Authorization: Bearer <TOKEN>) usando api/config/auth.php
 */

require_once __DIR__ . '/../api/config/headers.php';
require_once __DIR__ . '/../api/config/auth.php';
require_once __DIR__ . '/../includes/db.php';

// Verificar token
if (!check_api_token()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

try {
    // Comprobar si la columna ya existe usando INFORMATION_SCHEMA
    $stmt = $pdo->prepare("SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'usuarios' AND COLUMN_NAME = 'rol'");
    $stmt->execute();
    $col = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($col) {
        echo json_encode(['success' => true, 'message' => "La columna 'rol' ya existe. No se requiere acción."]); 
        exit;
    }

    // Ejecutar ALTER TABLE para agregar columna
    $alter = "ALTER TABLE usuarios ADD COLUMN rol VARCHAR(32) NOT NULL DEFAULT 'PACIENTE'";
    $pdo->exec($alter);

    echo json_encode(['success' => true, 'message' => "Columna 'rol' añadida correctamente."]); 
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>