<?php
// api/config/headers.php
// Configuración centralizada de cabeceras, CORS y autenticación para la API.

// 1. Cargar variables de entorno si existen (para desarrollo local)
if (file_exists(__DIR__ . '/../../includes/load_env.php')) {
    require_once __DIR__ . '/../../includes/load_env.php';
}

// 2. Configuración de CORS (Permitir peticiones desde tu Frontend)
// En producción, cambia '*' por tu dominio real, ej: 'https://mi-app-diabetes.com'
header("Access-Control-Allow-Origin: https://app.desingsgdl.app");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");
header("Content-Type: application/json; charset=UTF-8");

// 3. Manejar peticiones OPTIONS (Preflight)
// Cuando el navegador pregunta "¿puedo conectarme?", respondemos sí y terminamos.
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// 4. Seguridad: Verificar Token de API (si existe el archivo auth.php)
// Esto protege tus endpoints de accesos no autorizados (excepto GET si así lo deseas)
if (file_exists(__DIR__ . '/auth.php')) {
    require_once __DIR__ . '/auth.php';
    
    // Si quieres forzar token para todo (incluso GET), quita la condición 'if'
    // Si quieres que GET sea público (ej. leer blog) y POST privado, déjalo así:
    if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
        if (function_exists('check_api_token') && !check_api_token()) {
            http_response_code(401); // No autorizado
            echo json_encode(['success' => false, 'error' => 'Unauthorized: Invalid or missing Token']);
            exit;
        }
    }
}

// 5. Funciones Helper para respuestas consistentes

function sendJsonResponse($data, $statusCode = 200, $message = null) {
    http_response_code($statusCode);
    
    $response = [
        'success' => $statusCode >= 200 && $statusCode < 300,
        'status_code' => $statusCode,
        'timestamp' => date('Y-m-d H:i:s'),
        'data' => $data
    ];
    
    if ($message) {
        $response['message'] = $message;
    }
    
    echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    exit();
}

function sendErrorResponse($message, $statusCode = 400, $details = null) {
    $response = ['error' => $message];
    if ($details) {
        $response['details'] = $details;
    }
    // Usamos false para indicar fallo en la estructura lógica, aunque sendJsonResponse maneja el booleano
    sendJsonResponse($response, $statusCode, $message);
}
?>