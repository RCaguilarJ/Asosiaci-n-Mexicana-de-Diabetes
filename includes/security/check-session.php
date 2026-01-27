<?php
/**
 * check-session.php
 * Verifica sesión y redirige usando la ruta completa del proyecto
 */
require_once __DIR__ . '/../base_path.php';

// Hardened session start: secure cookie params, timeout e invalidación
ini_set('session.use_strict_mode', 1);
$secure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');
session_set_cookie_params([
    'lifetime' => 0,
    'path' => '/',
    'domain' => $_SERVER['HTTP_HOST'],
    'secure' => $secure,
    'httponly' => true,
    'samesite' => 'Strict'
]);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Regenerar id de sesión periódicamente para evitar fixation
if (!isset($_SESSION['created'])) {
    $_SESSION['created'] = time();
} elseif (time() - $_SESSION['created'] > 300) {
    session_regenerate_id(true);
    $_SESSION['created'] = time();
}

// Timeout de inactividad (30 minutos)
$timeout = 30 * 60;
if (isset($_SESSION['LAST_ACTIVITY']) && (time() - $_SESSION['LAST_ACTIVITY'] > $timeout)) {
    // limpiar y destruir sesión
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params['path'], $params['domain'], $params['secure'], $params['httponly']);
    }
    session_destroy();
    header('Location: ' . $basePath . '/login.php');
    exit;
}
$_SESSION['LAST_ACTIVITY'] = time();

// 2. Verificar si el usuario NO está logueado Y NO es invitado
if (!isset($_SESSION['usuario_id']) && !isset($_SESSION['es_invitado'])) {
    // Guardar la página actual para volver después de loguearse
    $_SESSION['pagina_anterior'] = $_SERVER['REQUEST_URI'];
    header('Location: ' . $basePath . '/login.php');
    exit;
}
?>
