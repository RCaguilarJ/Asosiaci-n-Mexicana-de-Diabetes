<?php
/**
 * API: actualizar rol de usuario
 * POST JSON: { "user_id": 123, "role": "ADMIN" }
 * Autorización: header Authorization: Bearer <TOKEN> OR ?token=...
 */

require_once __DIR__ . '/config/headers.php';
require_once __DIR__ . '/config/auth.php';
require_once __DIR__ . '/../includes/db.php';

// Verificar token
if (!check_api_token()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

// Permitir sólo POST
$method = $_SERVER['REQUEST_METHOD'];
if ($method !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit();
}

// Leer body JSON
$input = json_decode(file_get_contents('php://input'), true);
if (!$input) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid JSON body']);
    exit();
}

$userId = isset($input['user_id']) ? (int)$input['user_id'] : null;
$role = isset($input['role']) ? strtoupper(trim($input['role'])) : null;

if (!$userId && empty($input['email'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'user_id or email required']);
    exit();
}

if (!$role) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'role required']);
    exit();
}

$allowedRoles = ['ADMIN','PACIENTE','DOCTOR','NUTRICIONISTA','ADMINISTRADOR'];
if (!in_array($role, $allowedRoles)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid role']);
    exit();
}

try {
    // Intentar UPDATE con columna rol; si no existe, informar y fail safe
    $params = [];
    if ($userId) {
        $stmt = $pdo->prepare("UPDATE usuarios SET rol = ? WHERE id = ?");
        $params = [$role, $userId];
    } else {
        $email = trim($input['email']);
        $stmt = $pdo->prepare("UPDATE usuarios SET rol = ? WHERE email = ?");
        $params = [$role, $email];
    }

    try {
        $stmt->execute($params);
        echo json_encode(['success' => true, 'message' => 'Role updated']);
    } catch (PDOException $pe) {
        // Si la columna no existe, devolver error informativo
        if (strpos($pe->getMessage(), 'Unknown column') !== false || strpos($pe->getCode(), '42S22') !== false) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => "Column 'rol' does not exist in DB. Run migration."]);
        } else {
            throw $pe;
        }
    }

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>