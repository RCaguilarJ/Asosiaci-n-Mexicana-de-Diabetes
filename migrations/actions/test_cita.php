<?php
session_start();
header('Content-Type: application/json');

// Configurar reporting de errores para debug
error_reporting(E_ALL);
ini_set('display_errors', 1);

try {
    echo json_encode(['debug' => 'Inicio del script']);
    
    // Incluir conexión a la base de datos
    require '../includes/db.php';
    echo json_encode(['debug' => 'DB incluido']);
    
    // Verificar que el usuario esté logueado
    if (!isset($_SESSION['usuario_id'])) {
        echo json_encode(['error' => 'No autorizado', 'session' => $_SESSION]);
        exit;
    }
    
    echo json_encode(['debug' => 'Usuario autorizado', 'usuario_id' => $_SESSION['usuario_id']]);
    
    // Verificar conexión PDO
    if ($pdo) {
        echo json_encode(['debug' => 'Conexión PDO disponible']);
        
        // Probar una consulta simple
        $stmt = $pdo->query("SHOW TABLES");
        $tables = $stmt->fetchAll();
        echo json_encode(['debug' => 'Tablas encontradas', 'tables' => $tables]);
        
        // Verificar si existe la tabla citas
        $stmt = $pdo->query("SHOW TABLES LIKE 'citas'");
        if ($stmt->rowCount() > 0) {
            echo json_encode(['debug' => 'Tabla citas existe']);
        } else {
            echo json_encode(['debug' => 'Tabla citas NO existe']);
        }
        
    } else {
        echo json_encode(['error' => 'No hay conexión PDO disponible']);
    }
    
} catch (Exception $e) {
    echo json_encode(['error' => 'Excepción: ' . $e->getMessage(), 'trace' => $e->getTraceAsString()]);
}
?>