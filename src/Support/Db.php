<?php

namespace Amd\Support;

use PDO;
use PDOException;

final class Db
{
    private static ?PDO $remotePdo = null;
    private static bool $remoteAttempted = false;

    public static function connect(): PDO
    {
        Env::load();

        $host = getenv('DB_HOST') ?: 'localhost';
        $dbname = getenv('DB_NAME') ?: 'sistema_gestion_medica';
        $username = getenv('DB_USER') ?: 'root';
        $password = getenv('DB_PASS') ?: '';

        $dsn = "mysql:host={$host};dbname={$dbname};charset=utf8mb4";

        try {
            return new PDO($dsn, $username, $password, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4",
            ]);
        } catch (PDOException $e) {
            error_log('DB connection error: ' . $e->getMessage());

            if (php_sapi_name() !== 'cli' && strpos($_SERVER['REQUEST_URI'] ?? '', 'api/') !== false) {
                header('Content-Type: application/json');
                http_response_code(500);
                echo json_encode([
                    'success' => false,
                    'message' => 'Error de conexion a base de datos',
                ]);
                exit;
            }

            throw $e;
        }
    }

    public static function connectRemote(): ?PDO
    {
        if (self::$remotePdo instanceof PDO) {
            return self::$remotePdo;
        }

        if (self::$remoteAttempted) {
            return null;
        }

        self::$remoteAttempted = true;

        $host = getenv('REMOTE_DB_HOST');
        $dbname = getenv('REMOTE_DB_NAME');
        $username = getenv('REMOTE_DB_USER');
        $password = getenv('REMOTE_DB_PASS') ?: '';

        if (!$host || !$dbname || !$username) {
            error_log('connectRemote: missing remote credentials');
            return null;
        }

        try {
            $dsn = "mysql:host={$host};dbname={$dbname};charset=utf8mb4";
            self::$remotePdo = new PDO($dsn, $username, $password, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4",
            ]);

            return self::$remotePdo;
        } catch (PDOException $e) {
            error_log('connectRemote error: ' . $e->getMessage());
            return null;
        }
    }
}
