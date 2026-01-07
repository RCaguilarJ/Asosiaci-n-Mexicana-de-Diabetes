<?php
// includes/db.php

// --------------------------------------------------------------------------
// 1. CONFIGURACIÓN LOCAL (Web Asociación Diabetes)
// --------------------------------------------------------------------------
define('DB_HOST_LOCAL', 'localhost');
define('DB_NAME_LOCAL', 'diabetes_db');
define('DB_USER_LOCAL', 'root'); // Cambia esto en producción
define('DB_PASS_LOCAL', '');     // Cambia esto en producción

// --------------------------------------------------------------------------
// 2. CONFIGURACIÓN REMOTA (Sistema Gestión Médica)
// --------------------------------------------------------------------------
define('DB_HOST_REMOTE', '162.240.37.227'); 
define('DB_NAME_REMOTE', 'nombre_db_sistema_medico'); // <--- Poner nombre real de la DB remota
define('DB_USER_REMOTE', 'usuario_remoto_seguro');    // <--- Usuario creado en cPanel/MySQL remoto
define('DB_PASS_REMOTE', 'TuContraseñaSegura!');

// Rutas a certificados SSL (Opcional pero recomendado para datos médicos)
$sslOptions = [
    // Descomentar y ajustar rutas si usas SSL
    // PDO::MYSQL_ATTR_SSL_KEY    => '/ruta/client-key.pem',
    // PDO::MYSQL_ATTR_SSL_CERT   => '/ruta/client-cert.pem',
    // PDO::MYSQL_ATTR_SSL_CA     => '/ruta/ca-cert.pem',
    // PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT => false
];

// --------------------------------------------------------------------------
// FUNCIONES DE CONEXIÓN
// --------------------------------------------------------------------------

function getLocalConnection() {
    try {
        $dsn = "mysql:host=" . DB_HOST_LOCAL . ";dbname=" . DB_NAME_LOCAL . ";charset=utf8";
        return new PDO($dsn, DB_USER_LOCAL, DB_PASS_LOCAL, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]);
    } catch (PDOException $e) {
        // En producción, usa error_log en lugar de die() para no mostrar datos sensibles
        die("Error Local: " . $e->getMessage());
    }
}

function getRemoteConnection() {
    global $sslOptions;
    try {
        $dsn = "mysql:host=" . DB_HOST_REMOTE . ";dbname=" . DB_NAME_REMOTE . ";charset=utf8";
        // Fusionar opciones básicas con las de SSL
        $options = array_replace([
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_TIMEOUT => 5 // Tiempo de espera máximo (segundos) para no colgar la web
        ], $sslOptions);

        return new PDO($dsn, DB_USER_REMOTE, DB_PASS_REMOTE, $options);
    } catch (PDOException $e) {
        error_log("Error Remoto: " . $e->getMessage());
        return null; // Retornamos null para que la web siga funcionando aunque falle el remoto
    }
}

// Inicializamos la conexión local por defecto para compatibilidad con código existente
$pdo = getLocalConnection();
?>