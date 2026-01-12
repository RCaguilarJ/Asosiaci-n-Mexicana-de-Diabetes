<?php
// logout.php
// Finaliza la sesión del usuario replicando la política de cookies endurecida del proyecto.

$httpsEnabled = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'domain' => $_SERVER['HTTP_HOST'] ?? '',
        'secure' => $httpsEnabled,
        'httponly' => true,
        'samesite' => 'Strict',
    ]);
    session_start();
}

$_SESSION = [];

if (ini_get('session.use_cookies')) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
}

session_destroy();
session_write_close();

header('Location: login.php?logout=success');
exit;
<?php
/**
 * logout.php
 * Cierra la sesión del usuario de forma segura
 */

// Asegurarse de que la sesión esté iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Guardar el nombre del usuario antes de destruir la sesión (opcional)
$nombreUsuario = $_SESSION['usuario_nombre'] ?? 'Usuario';

// Destruir la sesión de forma segura
$_SESSION = [];

if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

session_destroy();

// Redirigir al usuario a la página de login con un mensaje
header("Location: login.php?logout=success");
exit;
?>