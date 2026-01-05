<?php
// includes/db.php - Configuración de base de datos

// ============================================================
// SELECCIONA EL AMBIENTE (Cambiar entre DESARROLLO y PRODUCCIÓN)
// ============================================================
define('ENVIRONMENT', 'development'); // Cambiar a 'production' para deploy

// ============================================================
// CONFIGURACIÓN LOCAL (DESARROLLO)
// ============================================================
if (ENVIRONMENT === 'development') {
    $host = 'localhost';
    $dbname = 'diabetes_db';
    $username = 'root';
    $password = '';
    $useSsl = false; // No necesitamos SSL en local
}

// ============================================================
// CONFIGURACIÓN REMOTA (PRODUCCIÓN)
// ============================================================
// Descomenta estas líneas cuando hagas deploy
// elseif (ENVIRONMENT === 'production') {
//     $host = '162.240.37.227';
//     $dbname = 'diabetes_db';
//     $username = 'usuario_remoto';
//     $password = 'tuc0ntras3ñaSegura!';
//     $useSsl = true;
//     $ssl_ca = '/ruta/segura/al/certificado/ca-cert.pem';
// }

try {
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ];

    // Agregar opciones SSL si estamos en producción
    if ($useSsl) {
        $options[PDO::MYSQL_ATTR_SSL_CA] = $ssl_ca;
        $options[PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT] = false;
    }

    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password, $options);

} catch (PDOException $e) {
    // En producción, no muestres errores detallados
    if (ENVIRONMENT === 'production') {
        error_log("Error de conexión: " . $e->getMessage());
        die("Error de conexión al sistema.");
    } else {
        // En desarrollo, muestra el error completo para debugging
        die("Error de conexión: " . $e->getMessage());
    }
}
?>