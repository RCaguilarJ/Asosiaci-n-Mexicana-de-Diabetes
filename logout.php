<?php
    session_start(); // Iniciar la sesión para poder destruirla
    session_unset(); // Borrar todas las variables de sesión
    session_destroy(); // Destruir la sesión completamente

    // Redirigir al usuario a la página de login
    header("Location: login.php");
    exit;
?>