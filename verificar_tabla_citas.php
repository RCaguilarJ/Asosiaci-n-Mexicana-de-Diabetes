<?php
/**
 * Verificar y crear estructura de tabla citas
 */

require_once 'includes/db.php';

header('Content-Type: text/html; charset=utf-8');

echo "<h2>🔧 Verificación estructura tabla citas</h2>";

try {
    // Verificar base de datos actual
    $stmt = $pdo->query("SELECT DATABASE()");
    $currentDb = $stmt->fetchColumn();
    echo "<p><strong>Base de datos activa:</strong> $currentDb</p>";
    
    // Verificar si existe tabla citas
    $stmt = $pdo->query("SHOW TABLES LIKE 'citas'");
    $tableExists = $stmt->fetch();
    
    if (!$tableExists) {
        echo "<p style='color: orange;'>⚠️ Tabla 'citas' no existe. Creándola...</p>";
        
        $createTable = "
        CREATE TABLE citas (
            id INT AUTO_INCREMENT PRIMARY KEY,
            usuario_id INT NOT NULL,
            medico_id INT NULL,
            nombre VARCHAR(255) NOT NULL,
            email VARCHAR(255) NULL,
            telefono VARCHAR(50) NULL,
            especialidad VARCHAR(100) NOT NULL,
            fecha_cita DATETIME NOT NULL,
            descripcion TEXT NULL,
            estado ENUM('pendiente', 'confirmada', 'completada', 'cancelada') DEFAULT 'pendiente',
            fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_usuario_id (usuario_id),
            INDEX idx_medico_id (medico_id),
            INDEX idx_fecha_cita (fecha_cita),
            INDEX idx_estado (estado)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ";
        
        $pdo->exec($createTable);
        echo "<p style='color: green;'>✅ Tabla 'citas' creada exitosamente</p>";
    } else {
        echo "<p style='color: green;'>✅ Tabla 'citas' existe</p>";
    }
    
    // Mostrar estructura actual
    echo "<h3>📋 Estructura actual de tabla 'citas':</h3>";
    $stmt = $pdo->query("DESCRIBE citas");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<table border='1' style='border-collapse: collapse; margin: 20px 0;'>";
    echo "<tr style='background: #f0f0f0;'>";
    echo "<th style='padding: 8px;'>Campo</th>";
    echo "<th style='padding: 8px;'>Tipo</th>";
    echo "<th style='padding: 8px;'>Null</th>";
    echo "<th style='padding: 8px;'>Key</th>";
    echo "<th style='padding: 8px;'>Default</th>";
    echo "<th style='padding: 8px;'>Extra</th>";
    echo "</tr>";
    
    $columnasNecesarias = ['usuario_id', 'medico_id', 'nombre', 'email', 'telefono', 'fecha_cita', 'especialidad', 'descripcion', 'estado'];
    $columnasExistentes = [];
    
    foreach ($columns as $col) {
        $columnasExistentes[] = $col['Field'];
        
        $color = in_array($col['Field'], $columnasNecesarias) ? '#e8f5e8' : '#fff';
        echo "<tr style='background: $color;'>";
        echo "<td style='padding: 8px; font-weight: bold;'>{$col['Field']}</td>";
        echo "<td style='padding: 8px;'>{$col['Type']}</td>";
        echo "<td style='padding: 8px;'>{$col['Null']}</td>";
        echo "<td style='padding: 8px;'>{$col['Key']}</td>";
        echo "<td style='padding: 8px;'>{$col['Default']}</td>";
        echo "<td style='padding: 8px;'>{$col['Extra']}</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    // Verificar columnas faltantes
    $columnasFaltantes = array_diff($columnasNecesarias, $columnasExistentes);
    
    if (!empty($columnasFaltantes)) {
        echo "<h3 style='color: orange;'>⚠️ Columnas faltantes:</h3>";
        echo "<ul>";
        foreach ($columnasFaltantes as $columna) {
            echo "<li style='color: red;'>$columna</li>";
        }
        echo "</ul>";
        
        echo "<p><strong>Agregando columnas faltantes...</strong></p>";
        
        $alterStatements = [
            'nombre' => "ALTER TABLE citas ADD COLUMN nombre VARCHAR(255) NOT NULL AFTER medico_id",
            'email' => "ALTER TABLE citas ADD COLUMN email VARCHAR(255) NULL AFTER nombre",
            'telefono' => "ALTER TABLE citas ADD COLUMN telefono VARCHAR(50) NULL AFTER email"
        ];
        
        foreach ($columnasFaltantes as $columna) {
            if (isset($alterStatements[$columna])) {
                try {
                    $pdo->exec($alterStatements[$columna]);
                    echo "<p style='color: green;'>✅ Columna '$columna' agregada</p>";
                } catch (Exception $e) {
                    echo "<p style='color: red;'>❌ Error agregando '$columna': " . $e->getMessage() . "</p>";
                }
            }
        }
    } else {
        echo "<p style='color: green;'>✅ Todas las columnas necesarias están presentes</p>";
    }
    
    // Mostrar datos de ejemplo si existen
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM citas");
    $count = $stmt->fetch();
    
    echo "<h3>📊 Datos en tabla:</h3>";
    echo "<p><strong>Total de citas:</strong> {$count['total']}</p>";
    
    if ($count['total'] > 0) {
        echo "<h4>Últimas 5 citas:</h4>";
        $stmt = $pdo->query("SELECT * FROM citas ORDER BY fecha_registro DESC LIMIT 5");
        $citas = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "<table border='1' style='border-collapse: collapse; margin: 10px 0;'>";
        echo "<tr style='background: #f0f0f0;'>";
        echo "<th style='padding: 5px;'>ID</th>";
        echo "<th style='padding: 5px;'>Usuario</th>";
        echo "<th style='padding: 5px;'>Médico</th>";
        echo "<th style='padding: 5px;'>Especialidad</th>";
        echo "<th style='padding: 5px;'>Fecha Cita</th>";
        echo "<th style='padding: 5px;'>Estado</th>";
        echo "</tr>";
        
        foreach ($citas as $cita) {
            echo "<tr>";
            echo "<td style='padding: 5px;'>{$cita['id']}</td>";
            echo "<td style='padding: 5px;'>{$cita['usuario_id']}</td>";
            echo "<td style='padding: 5px;'>" . ($cita['medico_id'] ?? 'NULL') . "</td>";
            echo "<td style='padding: 5px;'>{$cita['especialidad']}</td>";
            echo "<td style='padding: 5px;'>{$cita['fecha_cita']}</td>";
            echo "<td style='padding: 5px;'>{$cita['estado']}</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error: " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<p><strong>Detalles:</strong></p>";
    echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
}

echo "<h3>🔗 Tests disponibles:</h3>";
echo "<p>";
echo "<a href='test_guardar_cita.php' style='color: blue;'>🧪 Test guardar_cita.php</a> | ";
echo "<a href='views/citas.php' style='color: blue;'>📝 Formulario citas</a> | ";
echo "<a href='panel_medico.php' style='color: blue;'>👨‍⚕️ Panel médico</a>";
echo "</p>";
?>