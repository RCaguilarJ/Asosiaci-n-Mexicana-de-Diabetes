<?php
/**
 * Migración para crear tabla sync_queue
 * Esta tabla almacena el registro de operaciones de sincronización
 * con el Sistema de Gestión Médica
 */

require_once __DIR__ . '/../includes/db.php';

header('Content-Type: text/html; charset=utf-8');

echo "<h2>🔧 Migración: Crear tabla sync_queue</h2>\n";
echo "<p><strong>Propósito:</strong> Almacenar registro de operaciones de sincronización con el Sistema de Gestión Médica</p>\n";

try {
    // Verificar base de datos actual
    $stmt = $pdo->query("SELECT DATABASE()");
    $currentDb = $stmt->fetchColumn();
    echo "<p><strong>Base de datos activa:</strong> $currentDb</p>\n";
    
    // Verificar si existe tabla sync_queue
    $stmt = $pdo->query("SHOW TABLES LIKE 'sync_queue'");
    $tableExists = $stmt->fetch();
    
    if ($tableExists) {
        echo "<p style='color: orange;'>⚠️ La tabla 'sync_queue' ya existe</p>\n";
        
        // Mostrar estructura actual
        echo "<h3>📋 Estructura actual de tabla 'sync_queue':</h3>\n";
        $stmt = $pdo->query("DESCRIBE sync_queue");
        $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "<table border='1' style='border-collapse: collapse; margin: 20px 0;'>\n";
        echo "<tr style='background: #f0f0f0;'>";
        echo "<th style='padding: 8px;'>Campo</th>";
        echo "<th style='padding: 8px;'>Tipo</th>";
        echo "<th style='padding: 8px;'>Null</th>";
        echo "<th style='padding: 8px;'>Key</th>";
        echo "<th style='padding: 8px;'>Default</th>";
        echo "<th style='padding: 8px;'>Extra</th>";
        echo "</tr>\n";
        
        foreach ($columns as $col) {
            echo "<tr>";
            echo "<td style='padding: 8px; font-weight: bold;'>{$col['Field']}</td>";
            echo "<td style='padding: 8px;'>{$col['Type']}</td>";
            echo "<td style='padding: 8px;'>{$col['Null']}</td>";
            echo "<td style='padding: 8px;'>{$col['Key']}</td>";
            echo "<td style='padding: 8px;'>{$col['Default']}</td>";
            echo "<td style='padding: 8px;'>{$col['Extra']}</td>";
            echo "</tr>\n";
        }
        echo "</table>\n";
        
        echo "<p style='color: green;'>✅ No se requiere acción. La tabla ya está creada.</p>\n";
        
    } else {
        echo "<p style='color: blue;'>📝 Creando tabla 'sync_queue'...</p>\n";
        
        // Crear la tabla sync_queue
        $createTableSQL = "
        CREATE TABLE IF NOT EXISTS sync_queue (
            id INT AUTO_INCREMENT PRIMARY KEY,
            operacion VARCHAR(100) NOT NULL,
            estado ENUM('pendiente', 'completado', 'error') DEFAULT 'pendiente',
            referencia_id VARCHAR(255) NULL,
            error_mensaje TEXT NULL,
            datos_json TEXT NULL,
            fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_operacion (operacion),
            INDEX idx_estado (estado),
            INDEX idx_fecha (fecha_creacion)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ";
        
        $pdo->exec($createTableSQL);
        
        echo "<p style='color: green;'>✅ Tabla 'sync_queue' creada exitosamente</p>\n";
        
        // Verificar la creación mostrando estructura
        echo "<h3>📋 Estructura de la tabla creada:</h3>\n";
        $stmt = $pdo->query("DESCRIBE sync_queue");
        $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "<table border='1' style='border-collapse: collapse; margin: 20px 0;'>\n";
        echo "<tr style='background: #f0f0f0;'>";
        echo "<th style='padding: 8px;'>Campo</th>";
        echo "<th style='padding: 8px;'>Tipo</th>";
        echo "<th style='padding: 8px;'>Null</th>";
        echo "<th style='padding: 8px;'>Key</th>";
        echo "<th style='padding: 8px;'>Default</th>";
        echo "<th style='padding: 8px;'>Extra</th>";
        echo "</tr>\n";
        
        foreach ($columns as $col) {
            echo "<tr>";
            echo "<td style='padding: 8px; font-weight: bold;'>{$col['Field']}</td>";
            echo "<td style='padding: 8px;'>{$col['Type']}</td>";
            echo "<td style='padding: 8px;'>{$col['Null']}</td>";
            echo "<td style='padding: 8px;'>{$col['Key']}</td>";
            echo "<td style='padding: 8px;'>{$col['Default']}</td>";
            echo "<td style='padding: 8px;'>{$col['Extra']}</td>";
            echo "</tr>\n";
        }
        echo "</table>\n";
        
        // Mostrar índices creados
        echo "<h3>🔍 Índices creados:</h3>\n";
        $stmt = $pdo->query("SHOW INDEX FROM sync_queue");
        $indexes = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "<ul>\n";
        $indexNames = [];
        foreach ($indexes as $index) {
            $indexName = $index['Key_name'];
            if (!in_array($indexName, $indexNames)) {
                $indexNames[] = $indexName;
                echo "<li><strong>{$indexName}</strong> en columna: {$index['Column_name']}</li>\n";
            }
        }
        echo "</ul>\n";
    }
    
    // Mostrar estadísticas si hay datos
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM sync_queue");
    $count = $stmt->fetch();
    
    echo "<h3>📊 Estadísticas:</h3>\n";
    echo "<p><strong>Total de registros en sync_queue:</strong> {$count['total']}</p>\n";
    
    if ($count['total'] > 0) {
        echo "<h4>Últimos 10 registros:</h4>\n";
        $stmt = $pdo->query("
            SELECT operacion, estado, referencia_id, fecha_creacion 
            FROM sync_queue 
            ORDER BY fecha_creacion DESC 
            LIMIT 10
        ");
        $records = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "<table border='1' style='border-collapse: collapse; margin: 10px 0;'>\n";
        echo "<tr style='background: #f0f0f0;'>";
        echo "<th style='padding: 5px;'>Operación</th>";
        echo "<th style='padding: 5px;'>Estado</th>";
        echo "<th style='padding: 5px;'>Referencia</th>";
        echo "<th style='padding: 5px;'>Fecha</th>";
        echo "</tr>\n";
        
        foreach ($records as $record) {
            $estadoColor = $record['estado'] === 'completado' ? 'green' : ($record['estado'] === 'error' ? 'red' : 'orange');
            echo "<tr>";
            echo "<td style='padding: 5px;'>{$record['operacion']}</td>";
            echo "<td style='padding: 5px; color: $estadoColor; font-weight: bold;'>{$record['estado']}</td>";
            echo "<td style='padding: 5px;'>" . ($record['referencia_id'] ?? 'NULL') . "</td>";
            echo "<td style='padding: 5px;'>{$record['fecha_creacion']}</td>";
            echo "</tr>\n";
        }
        echo "</table>\n";
    }
    
    echo "<p style='color: green; font-weight: bold; font-size: 18px; margin-top: 30px;'>✅ Migración completada exitosamente</p>\n";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error en la migración: " . htmlspecialchars($e->getMessage()) . "</p>\n";
    echo "<p><strong>Detalles del error:</strong></p>\n";
    echo "<pre style='background: #f5f5f5; padding: 10px; border: 1px solid #ddd;'>" . htmlspecialchars($e->getTraceAsString()) . "</pre>\n";
    http_response_code(500);
}

echo "<hr>\n";
echo "<h3>🔗 Enlaces útiles:</h3>\n";
echo "<p>";
echo "<a href='../test_sistema_gestion.php' style='color: blue;'>🧪 Probar Sistema de Gestión</a> | ";
echo "<a href='../actions/guardar_cita.php' style='color: blue;'>📝 Endpoint guardar cita</a> | ";
echo "<a href='README.md' style='color: blue;'>📖 Ver documentación de migraciones</a>";
echo "</p>\n";
?>
