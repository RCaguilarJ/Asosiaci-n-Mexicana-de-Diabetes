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