<?php
/**
 * API: Listar usuarios (id, nombre, email, rol)
 * Protegido por token (Authorization: Bearer <TOKEN>)
 */

require_once __DIR__ . '/config/headers.php';
require_once __DIR__ . '/config/auth.php';
require_once __DIR__ . '/../includes/db.php';

if (!check_api_token()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

$method = $_SERVER['REQUEST_METHOD'];
if ($method !== 'GET') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit();
}

try {
    // Intentar seleccionar rol si columna existe
    try {
        $stmt = $pdo->query("SELECT id, nombre, email, rol, fecha_registro FROM usuarios ORDER BY fecha_registro DESC LIMIT 1000");
        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
        // Asegurar campo rol presente
        foreach ($users as &$u) {
            if (!isset($u['rol'])) $u['rol'] = 'PACIENTE';
        }
    } catch (PDOException $pe) {
        if (strpos($pe->getMessage(), 'Unknown column') !== false || strpos($pe->getCode(), '42S22') !== false) {
            $stmt = $pdo->query("SELECT id, nombre, email, fecha_registro FROM usuarios ORDER BY fecha_registro DESC LIMIT 1000");
            $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
            foreach ($users as &$u) {
                $u['rol'] = 'PACIENTE';
            }
        } else {
            throw $pe;
        }
    }

    echo json_encode(['success' => true, 'data' => $users]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>