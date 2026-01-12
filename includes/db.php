<?php
// includes/db.php
// Load local .env when present (for developer machines). This does not override
// system environment variables and is ignored in production when no .env file exists.
if (file_exists(__DIR__ . '/load_env.php')) {
  require_once __DIR__ . '/load_env.php';
}
// Ajusta únicamente estas variables según la base que use el sitio:
$host   = getenv('DB_HOST') ?: 'localhost';
$dbname = getenv('DB_NAME') ?: 'diabetes_db';

// Use credentials from environment when possible. Configure DB_USER/DB_PASS
// in the system environment or via Apache/WAMP env vars. This avoids committing
// production credentials to the repo. Defaults keep compatibility with WAMP.
$user   = getenv('DB_USER') ?: 'root';
$pass   = getenv('DB_PASS') ?: '';                    // contraseña vacía en tu entorno WAMP

// Opciones PDO recomendadas
$pdoOptions = [
  PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
  PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
  PDO::ATTR_EMULATE_PREPARES => false,
];

$pdo = null;  // Alias PDO esperado por el código existente
$db = null;   // PDO instance (interna)
$conn = null; // mysqli instance (si disponible)

try {
  // Intentamos conexión PDO (preferible)
  $dsn = "mysql:host={$host};dbname={$dbname};charset=utf8mb4";
  $db = new PDO($dsn, $user, $pass, $pdoOptions);
  // Mantener compatibilidad: exponer la conexión PDO como `$pdo`
  $pdo = $db;

  // Creamos también una conexión mysqli ligera para compatibilidad con código que use $conn
  $conn = new mysqli($host, $user, $pass, $dbname);
  if ($conn->connect_error) {
    // No bloqueante: dejamos $db funcional y registramos el error
    error_log('MySQLi connect warning: ' . $conn->connect_error);
  }
} catch (PDOException $e) {
  // Si PDO falla, intentamos mysqli como respaldo
  error_log('PDO connection failed: ' . $e->getMessage());
  $db = null;
  $pdo = null;
  $conn = new mysqli($host, $user, $pass, $dbname);
  if ($conn->connect_error) {
    error_log('MySQLi connection failed: ' . $conn->connect_error);
    // No podemos continuar si ninguna conexión funciona
    die('Error de conexión a la base de datos. Revisa logs.');
  }
}

// Short helper to close connections if needed
function db_close() {
  global $db, $conn;
  if ($db instanceof PDO) { $db = null; }
  if ($conn instanceof mysqli) { $conn->close(); }
}

/**
 * getRemoteConnection
 * Devuelve un PDO conectado a la base de datos remota (sistema médico).
 * Lee las credenciales desde variables de entorno para evitar secretos en el repo.
 * Variables esperadas:
 *  REMOTE_DB_HOST, REMOTE_DB_NAME, REMOTE_DB_USER, REMOTE_DB_PASS
 */
function getRemoteConnection() {
  // Prefer REMOTE_DB_* variables; si no existen, hacer fallback a DB_* (local)
  $host = getenv('REMOTE_DB_HOST') ?: getenv('DB_HOST') ?: null;
  $dbname = getenv('REMOTE_DB_NAME') ?: getenv('DB_NAME') ?: null;
  $user = getenv('REMOTE_DB_USER') ?: getenv('DB_USER') ?: null;
  $pass = getenv('REMOTE_DB_PASS') ?: getenv('DB_PASS') ?: null;

  if (!$host || !$dbname || !$user) {
    error_log('getRemoteConnection: faltan variables de entorno para la conexión remota (REMOTE_DB_* o DB_*)');
    return null;
  }

  $pdoOptions = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false,
  ];

  try {
    $dsn = "mysql:host={$host};dbname={$dbname};charset=utf8mb4";
    return new PDO($dsn, $user, $pass, $pdoOptions);
  } catch (PDOException $e) {
    error_log('getRemoteConnection error: ' . $e->getMessage());
    return null;
  }
}