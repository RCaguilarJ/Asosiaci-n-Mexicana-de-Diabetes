<?php


// Asegurarse de que la sesión esté iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Verificar si el usuario está logueado o es invitado
if (!isset($_SESSION['usuario_id']) && !isset($_SESSION['es_invitado'])) {

    $_SESSION['pagina_anterior'] = $_SERVER['REQUEST_URI'];
    

    header('Location: login.php');
    exit;
}
?>