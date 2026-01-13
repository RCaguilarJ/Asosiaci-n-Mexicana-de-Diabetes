<?php
// Debug version para identificar el problema
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);

header('Content-Type: application/json');

echo json_encode([
    'debug' => 'Starting guardar_cita debug',
    'session_exists' => isset($_SESSION['usuario_id']),
    'method' => $_SERVER['REQUEST_METHOD'],
    'post_data' => $_POST,
    'timestamp' => date('Y-m-d H:i:s')
]);

try {
    // Probar cada require por separado
    echo "\n<!-- Testing includes -->\n";
    
    if (file_exists('../includes/db.php')) {
        require_once '../includes/db.php';
        echo "<!-- DB included successfully -->\n";
    } else {
        throw new Exception('db.php not found');
    }
    
    if (file_exists('../includes/api_sistema_gestion.php')) {
        require_once '../includes/api_sistema_gestion.php';
        echo "<!-- API helper included successfully -->\n";
    } else {
        echo "<!-- API helper not found, continuing without it -->\n";
    }
    
    // Test database connection
    if (isset($pdo)) {
        $stmt = $pdo->query("SELECT 1");
        echo "<!-- Database connection OK -->\n";
    } else {
        echo "<!-- Database connection failed -->\n";
    }
    
} catch (Exception $e) {
    echo json_encode([
        'error' => $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine(),
        'trace' => $e->getTraceAsString()
    ]);
}
?>