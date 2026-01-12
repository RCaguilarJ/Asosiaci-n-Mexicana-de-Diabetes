<?php
/**
 * API de autenticación (JSON)
 * POST { "email": "...", "password": "..." }
 * Responde: { success: true, data: { id, nombre, email, rol } }
 */

require_once __DIR__ . '/config/headers.php';
require_once __DIR__ . '/../includes/db.php';

$method = $_SERVER['REQUEST_METHOD'];
if ($method !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit();
}

$input = json_decode(file_get_contents('php://input'), true);
if (!$input || empty($input['email']) || empty($input['password'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'email y password son requeridos']);
    exit();
}

$email = trim($input['email']);
$password = $input['password'];

try {
    // Intentar obtener rol si existe
    try {
        $stmt = $pdo->prepare("SELECT id, nombre, email, password, rol FROM usuarios WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $pe) {
        if (strpos($pe->getMessage(), 'Unknown column') !== false || strpos($pe->getCode(), '42S22') !== false) {
            $stmt = $pdo->prepare("SELECT id, nombre, email, password FROM usuarios WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($user) $user['rol'] = 'PACIENTE';
        } else {
            throw $pe;
        }
    }

    if (!$user) {
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'Credenciales inválidas']);
        exit();
    }

    if (!password_verify($password, $user['password'])) {
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'Credenciales inválidas']);
        exit();
    }

    // No incluir el hash en la respuesta
    unset($user['password']);
    // Normalizar rol
    $user['rol'] = isset($user['rol']) ? $user['rol'] : 'PACIENTE';

    echo json_encode(['success' => true, 'data' => $user]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>