<?php
// api/config/headers.php
// Common headers and auth bootstrap for API endpoints.

// Load local .env for development if present
if (file_exists(__DIR__ . '/../../includes/load_env.php')) {
    require_once __DIR__ . '/../../includes/load_env.php';
}

// Basic CORS + JSON headers
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

// Load token auth helper (defines check_api_token()) if available
if (file_exists(__DIR__ . '/auth.php')) {
    require_once __DIR__ . '/auth.php';
    // Enforce token for non-GET requests
    if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
        if (!check_api_token()) {
            http_response_code(401);
            echo json_encode(['error' => 'Unauthorized']);
            exit;
        }
    }
}

?>
<?php
/**
 * Configuración de headers para API REST
 * Permite comunicación entre plataforma de pacientes y sistema médico
 */

// Configurar headers CORS para permitir comunicación con la plataforma médica
header("Access-Control-Allow-Origin: http://localhost:5173"); // Puerto típico de Vite
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");
header("Content-Type: application/json; charset=UTF-8");

// Manejar preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Función para enviar respuesta JSON estandarizada
function sendJsonResponse($data, $statusCode = 200, $message = null) {
    http_response_code($statusCode);
    
    $response = [
        'success' => $statusCode < 400,
        'data' => $data,
        'timestamp' => date('Y-m-d H:i:s'),
        'status_code' => $statusCode
    ];
    
    if ($message) {
        $response['message'] = $message;
    }
    
    echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    exit();
}

// Función para manejar errores
function sendErrorResponse($message, $statusCode = 400, $details = null) {
    $data = ['error' => $message];
    if ($details) {
        $data['details'] = $details;
    }
    sendJsonResponse($data, $statusCode, $message);
}
?>