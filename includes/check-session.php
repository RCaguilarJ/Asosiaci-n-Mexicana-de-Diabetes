<?php
/**
 * check-session.php
 * Verifica que el usuario esté logueado o sea invitado antes de acceder a páginas protegidas
 * Si no está logueado ni es invitado, redirige al login
 * 
 * USO: Incluir al inicio de cualquier página protegida:
 *      <?php require 'includes/check-session.php'; ?>
 */

// Asegurarse de que la sesión esté iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Verificar si el usuario está logueado o es invitado
if (!isset($_SESSION['usuario_id']) && !isset($_SESSION['es_invitado'])) {
    // Guardar la página actual para redirigir después del login
    $_SESSION['pagina_anterior'] = $_SERVER['REQUEST_URI'];
    
    // Redirigir al login
    header('Location: ' . dirname($_SERVER['SCRIPT_NAME']) . '/../login.php');
    exit;
}
