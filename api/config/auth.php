<?php
// api/config/auth.php
// TOKEN DE API para autorizar llamadas desde la plataforma médica.
// Preferible: establecer `API_SHARED_TOKEN` como variable de entorno del sistema
// (por ejemplo con `setx API_SHARED_TOKEN "<secreto>"`) y no dejar secretos en el repo.

// Load local .env for development (if present) so API endpoints can read the same
// values as the rest of the app during local testing. In production you should
// set real environment variables instead and remove .env from the server.
if (file_exists(__DIR__ . '/../../includes/load_env.php')) {
    require_once __DIR__ . '/../../includes/load_env.php';
}

$API_SHARED_TOKEN = getenv('API_SHARED_TOKEN') ?: 'CHANGE_THIS_TO_A_STRONG_SECRET_TOKEN';

// Verifica token desde header Authorization: Bearer <token>
function check_api_token() {
    global $API_SHARED_TOKEN;

    $headers = [];
    if (function_exists('getallheaders')) {
        $headers = getallheaders();
    }

    $authHeader = $headers['Authorization'] ?? $headers['authorization'] ?? ($_SERVER['HTTP_AUTHORIZATION'] ?? null);

    if ($authHeader && stripos($authHeader, 'Bearer ') === 0) {
        $token = trim(substr($authHeader, 7));
        return hash_equals($API_SHARED_TOKEN, $token);
    }

    // Fallback: allow token via POST/GET param (least secure)
    $tokenParam = $_GET['token'] ?? ($_POST['token'] ?? null);
    if ($tokenParam) {
        return hash_equals($API_SHARED_TOKEN, $tokenParam);
    }

    return false;
}
?>