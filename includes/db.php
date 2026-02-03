<?php

$autoload = __DIR__ . '/../vendor/autoload.php';
if (file_exists($autoload)) {
    require_once $autoload;
}

if (class_exists(Amd\Support\Db::class)) {
    $pdo = Amd\Support\Db::connect();
}

if (file_exists(__DIR__ . '/load_env.php')) {
    require_once __DIR__ . '/load_env.php';
}

try {
    $host = getenv('DB_HOST') ?: 'localhost';
    $dbname = getenv('DB_NAME') ?: 'sistema_gestion_medica';
    $username = getenv('DB_USER') ?: 'root';
    $password = getenv('DB_PASS') ?: '';

    $dsn = "mysql:host=$host;dbname=$dbname;charset=utf8mb4";

    $pdo = new PDO($dsn, $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
        PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"
    ]);

} catch (PDOException $e) {
    error_log("Error de conexion DB: " . $e->getMessage());

    if (php_sapi_name() !== 'cli' && strpos($_SERVER['REQUEST_URI'] ?? '', 'api/') !== false) {
        header('Content-Type: application/json');
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'Error de conexion a base de datos'
        ]);
        exit;
    }

    throw $e;
}

if (!function_exists('getRemoteConnection')) {
function getRemoteConnection() {
    if (class_exists(Amd\Support\Db::class)) {
        return Amd\Support\Db::connectRemote();
    }

    static $remotePdo = null;
    static $attempted = false;

    if ($remotePdo instanceof PDO) {
        return $remotePdo;
    }

    if ($attempted) {
        return null;
    }

    $attempted = true;

    $host = getenv('REMOTE_DB_HOST');
    $dbname = getenv('REMOTE_DB_NAME');
    $username = getenv('REMOTE_DB_USER');
    $password = getenv('REMOTE_DB_PASS') ?: '';

    if (!$host || !$dbname || !$username) {
        error_log('getRemoteConnection: credenciales remotas incompletas');
        return null;
    }

    try {
        $dsn = "mysql:host=$host;dbname=$dbname;charset=utf8mb4";
        $remotePdo = new PDO($dsn, $username, $password, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"
        ]);

        return $remotePdo;

    } catch (PDOException $e) {
        error_log('getRemoteConnection error: ' . $e->getMessage());
        return null;
    }
}
}
