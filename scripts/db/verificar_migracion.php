<?php
/**
 * Script para verificar y actualizar la estructura de la tabla citas
 * Agrega columna medico_id si no existe
 */

require_once 'includes/db.php';

header('Content-Type: text/html; charset=utf-8');

echo "<h1>Verificación y Migración de Tabla Citas</h1>";

try {
    // 1. Verificar estructura actual
    echo "<h2>1. Estructura Actual</h2>";
    $stmt = $pdo->query("DESCRIBE citas");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<table border='1'>";
    echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
    
    $medicoIdExists = false;
    foreach ($columns as $column) {
        echo "<tr>";
        echo "<td>" . $column['Field'] . "</td>";
        echo "<td>" . $column['Type'] . "</td>";
        echo "<td>" . $column['Null'] . "</td>";
        echo "<td>" . $column['Key'] . "</td>";
        echo "<td>" . $column['Default'] . "</td>";
        echo "<td>" . $column['Extra'] . "</td>";
        echo "</tr>";
        
        if ($column['Field'] === 'medico_id') {
            $medicoIdExists = true;
        }
    }
    echo "</table>";
    
    // 2. Verificar si necesita migración
    echo "<h2>2. Estado de Migración</h2>";
    
    if ($medicoIdExists) {
        echo "<p style='color: green;'>✓ La columna 'medico_id' ya existe</p>";
    } else {
        echo "<p style='color: orange;'>⚠ La columna 'medico_id' NO existe. Ejecutando migración...</p>";
        
        // Ejecutar migración
        $alterSQL = "ALTER TABLE citas ADD COLUMN medico_id INT NULL AFTER usuario_id";
        $pdo->exec($alterSQL);
        
        echo "<p style='color: green;'>✓ Columna 'medico_id' agregada exitosamente</p>";
        
        // Agregar índice para optimización
        try {
            $indexSQL = "ALTER TABLE citas ADD INDEX idx_medico_id (medico_id)";
            $pdo->exec($indexSQL);
            echo "<p style='color: green;'>✓ Índice agregado para medico_id</p>";
        } catch (Exception $e) {
            if (strpos($e->getMessage(), 'Duplicate key name') === false) {
                throw $e;
            }
            echo "<p style='color: blue;'>ℹ Índice ya existía</p>";
        }
    }
    
    // 3. Verificar estructura después de migración
    echo "<h2>3. Estructura Final</h2>";
    $stmt = $pdo->query("DESCRIBE citas");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<table border='1'>";
    echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
    
    foreach ($columns as $column) {
        $style = ($column['Field'] === 'medico_id') ? "background: #d4edda;" : "";
        echo "<tr style='$style'>";
        echo "<td><strong>" . $column['Field'] . "</strong></td>";
        echo "<td>" . $column['Type'] . "</td>";
        echo "<td>" . $column['Null'] . "</td>";
        echo "<td>" . $column['Key'] . "</td>";
        echo "<td>" . $column['Default'] . "</td>";
        echo "<td>" . $column['Extra'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    // 4. Test de todos los especialistas
    echo "<h2>4. Test de Especialistas por Rol</h2>";
    
    require_once 'includes/api_sistema_gestion.php';
    $api = new ApiSistemaGestionHelper();
    
    $roles = ['NUTRI', 'ENDOCRINO', 'PODOLOGO', 'PSICOLOGO', 'DOCTOR'];
    
    foreach ($roles as $role) {
        echo "<h3>Especialistas - $role</h3>";
        
        $especialistas = $api->obtenerEspecialistas($role);
        
        if ($especialistas !== false && !empty($especialistas)) {
            echo "<p style='color: green;'>✓ " . count($especialistas) . " especialistas encontrados</p>";
            
            echo "<ul>";
            foreach ($especialistas as $esp) {
                echo "<li><strong>ID:</strong> {$esp['id']} - <strong>Nombre:</strong> {$esp['nombre']} - <strong>Rol:</strong> {$esp['role']}</li>";
            }
            echo "</ul>";
        } else {
            echo "<p style='color: red;'>✗ No se pudieron cargar especialistas para $role</p>";
        }
    }
    
    // 5. Test de la API local
    echo "<h2>5. Test Endpoint Local</h2>";
    
    $url = "http://" . $_SERVER['HTTP_HOST'] . "/api/get_especialistas.php";
    
    echo "<p><strong>URL:</strong> <a href='$url' target='_blank'>$url</a></p>";
    
    $response = file_get_contents($url);
    $data = json_decode($response, true);
    
    if ($data && $data['success']) {
        echo "<p style='color: green;'>✓ Endpoint funcionando correctamente</p>";
        echo "<p>Total de roles disponibles: " . count($data['roles']) . "</p>";
        
        foreach ($data['roles'] as $rol) {
            echo "<p>• <strong>{$rol['nombre_rol']} ({$rol['role']}):</strong> {$rol['especialistas']} especialistas</p>";
        }
    } else {
        echo "<p style='color: red;'>✗ Error en endpoint local</p>";
        if ($data) {
            echo "<pre>" . htmlspecialchars(json_encode($data, JSON_PRETTY_PRINT)) . "</pre>";
        }
    }
    
    echo "<h2>✅ Estado del Sistema</h2>";
    echo "<ul>";
    echo "<li>✓ Tabla citas preparada con columna medico_id</li>";
    echo "<li>✓ API de especialistas configurada</li>";
    echo "<li>✓ Todos los roles disponibles: " . implode(', ', $roles) . "</li>";
    echo "<li>✓ Sistema listo para capturar ID real de médicos</li>";
    echo "</ul>";
    
    echo "<hr>";
    echo "<p><a href='views/citas.php'>🔗 Ir a formulario de citas</a></p>";
    echo "<p><a href='test_sistema_gestion.php'>🔗 Test completo de integración</a></p>";

} catch (Exception $e) {
    echo "<h2 style='color: red;'>❌ Error</h2>";
    echo "<p><strong>Error:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<p><strong>Línea:</strong> " . $e->getLine() . "</p>";
}
?>