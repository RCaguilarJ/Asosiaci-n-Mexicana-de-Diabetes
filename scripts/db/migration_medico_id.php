<?php
/**
 * Migración para agregar columna medico_id a la tabla citas
 */

require_once 'includes/db.php';

echo "<h2>Migrando tabla citas - Agregando columna medico_id</h2>\n";

try {
    // 1. Verificar si la columna ya existe
    $stmt = $pdo->query("DESCRIBE citas");
    $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    if (in_array('medico_id', $columns)) {
        echo "<p style='color: orange;'>⚠️ La columna medico_id ya existe en la tabla citas</p>\n";
    } else {
        // 2. Agregar la columna medico_id
        $pdo->exec("ALTER TABLE citas ADD COLUMN medico_id INT NULL AFTER especialidad");
        echo "<p style='color: green;'>✅ Columna medico_id agregada exitosamente</p>\n";
    }
    
    // 3. Verificar si la columna paciente_curp ya existe
    if (in_array('paciente_curp', $columns)) {
        echo "<p style='color: orange;'>⚠️ La columna paciente_curp ya existe en la tabla citas</p>\n";
    } else {
        // 4. Agregar la columna paciente_curp (opcional)
        $pdo->exec("ALTER TABLE citas ADD COLUMN paciente_curp VARCHAR(18) NULL AFTER usuario_id");
        echo "<p style='color: green;'>✅ Columna paciente_curp agregada exitosamente</p>\n";
    }
    
    // 5. Agregar índices si no existen
    try {
        $pdo->exec("ALTER TABLE citas ADD INDEX idx_medico_fecha (medico_id, fecha_cita)");
        echo "<p style='color: green;'>✅ Índice idx_medico_fecha agregado</p>\n";
    } catch (Exception $e) {
        echo "<p style='color: orange;'>⚠️ Índice idx_medico_fecha ya existe o error: " . $e->getMessage() . "</p>\n";
    }
    
    try {
        $pdo->exec("ALTER TABLE citas ADD INDEX idx_paciente_curp (paciente_curp)");
        echo "<p style='color: green;'>✅ Índice idx_paciente_curp agregado</p>\n";
    } catch (Exception $e) {
        echo "<p style='color: orange;'>⚠️ Índice idx_paciente_curp ya existe o error: " . $e->getMessage() . "</p>\n";
    }
    
    // 6. Mostrar estructura final
    echo "<h3>Estructura final de la tabla citas:</h3>\n";
    $stmt = $pdo->query("DESCRIBE citas");
    echo "<table border='1' style='border-collapse: collapse;'>\n";
    echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>\n";
    
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "<tr>";
        echo "<td>" . htmlspecialchars($row['Field']) . "</td>";
        echo "<td>" . htmlspecialchars($row['Type']) . "</td>";
        echo "<td>" . htmlspecialchars($row['Null']) . "</td>";
        echo "<td>" . htmlspecialchars($row['Key']) . "</td>";
        echo "<td>" . htmlspecialchars($row['Default'] ?? 'NULL') . "</td>";
        echo "<td>" . htmlspecialchars($row['Extra']) . "</td>";
        echo "</tr>\n";
    }
    echo "</table>\n";
    
    echo "<p style='color: green; font-weight: bold;'>✅ Migración completada exitosamente</p>\n";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error en la migración: " . $e->getMessage() . "</p>\n";
}

echo "<hr><p><a href='test_sistema_gestion.php'>← Probar integración con Sistema de Gestión</a></p>\n";
?>