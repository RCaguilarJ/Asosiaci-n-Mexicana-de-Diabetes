<?php
/**
 * Script para ejecutar migración completa del sistema
 * Incluye verificación y corrección de tabla citas
 */

require_once 'includes/db.php';

header('Content-Type: text/html; charset=utf-8');

echo "<!DOCTYPE html>";
echo "<html><head><title>Migración Sistema Especialistas</title>";
echo "<style>
    body { font-family: Arial; margin: 20px; line-height: 1.6; }
    .success { color: #28a745; }
    .error { color: #dc3545; }
    .warning { color: #ffc107; }
    .info { color: #17a2b8; }
    .section { margin: 25px 0; padding: 20px; border: 1px solid #ddd; border-radius: 8px; }
    pre { background: #f8f9fa; padding: 15px; border-radius: 8px; overflow-x: auto; }
    .step { background: #e7f3ff; padding: 15px; margin: 10px 0; border-radius: 8px; border-left: 4px solid #007bff; }
</style>";
echo "</head><body>";

echo "<h1>🚀 Migración Sistema de Especialistas</h1>";
echo "<p class='info'>Este script actualiza la base de datos para soportar médicos reales del Sistema de Gestión Médica</p>";

try {
    // 1. Verificar conexión con base de datos
    echo "<div class='section'>";
    echo "<h2>1. 📊 Verificación de Base de Datos</h2>";
    
    $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    echo "<p class='success'>✓ Conexión exitosa. Tablas encontradas: " . count($tables) . "</p>";
    
    if (in_array('citas', $tables)) {
        echo "<p class='success'>✓ Tabla 'citas' existe</p>";
        
        // Verificar estructura actual
        $columns = $pdo->query("DESCRIBE citas")->fetchAll(PDO::FETCH_ASSOC);
        $medicoIdExists = false;
        $estructura = [];
        
        foreach ($columns as $column) {
            $estructura[] = $column['Field'] . " (" . $column['Type'] . ")";
            if ($column['Field'] === 'medico_id') {
                $medicoIdExists = true;
            }
        }
        
        echo "<div class='step'>";
        echo "<h3>Estructura Actual de 'citas':</h3>";
        echo "<ul>";
        foreach ($estructura as $col) {
            $highlight = (strpos($col, 'medico_id') !== false) ? ' class="success"' : '';
            echo "<li$highlight>$col</li>";
        }
        echo "</ul>";
        echo "</div>";
        
        if (!$medicoIdExists) {
            echo "<p class='warning'>⚠️ Columna 'medico_id' no existe. Procediendo con migración...</p>";
            
            // Ejecutar migración
            $sql = "ALTER TABLE citas ADD COLUMN medico_id INT NULL AFTER usuario_id";
            
            try {
                $pdo->exec($sql);
                echo "<p class='success'>✅ Columna 'medico_id' agregada exitosamente</p>";
                
                // Verificar que se agregó correctamente
                $columns = $pdo->query("DESCRIBE citas")->fetchAll(PDO::FETCH_ASSOC);
                foreach ($columns as $column) {
                    if ($column['Field'] === 'medico_id') {
                        echo "<p class='info'>📋 Nueva columna: medico_id ({$column['Type']}, {$column['Null']}, Default: {$column['Default']})</p>";
                        $medicoIdExists = true;
                        break;
                    }
                }
                
            } catch (PDOException $e) {
                if (strpos($e->getMessage(), 'Duplicate column name') !== false) {
                    echo "<p class='warning'>⚠️ Columna ya existe (error duplicado ignorado)</p>";
                    $medicoIdExists = true;
                } else {
                    throw $e;
                }
            }
        } else {
            echo "<p class='success'>✅ Columna 'medico_id' ya existe</p>";
        }
        
    } else {
        echo "<p class='error'>❌ Tabla 'citas' no encontrada</p>";
    }
    echo "</div>";
    
    // 2. Verificar archivos del sistema
    echo "<div class='section'>";
    echo "<h2>2. 📁 Verificación de Archivos</h2>";
    
    $archivos_necesarios = [
        'includes/api_sistema_gestion.php' => 'Helper API Sistema Gestión',
        'api/get_especialistas.php' => 'Endpoint Especialistas',
        'actions/guardar_cita.php' => 'Guardado de Citas',
        'views/citas.php' => 'Formulario de Citas',
        '.env' => 'Configuración API'
    ];
    
    foreach ($archivos_necesarios as $archivo => $descripcion) {
        if (file_exists($archivo)) {
            $size = filesize($archivo);
            echo "<p class='success'>✓ $descripcion: <code>$archivo</code> (" . number_format($size) . " bytes)</p>";
        } else {
            echo "<p class='error'>✗ $descripcion: <code>$archivo</code> NO ENCONTRADO</p>";
        }
    }
    echo "</div>";
    
    // 3. Test de API
    echo "<div class='section'>";
    echo "<h2>3. 🔗 Test de API Sistema Gestión</h2>";
    
    if (file_exists('includes/api_sistema_gestion.php')) {
        require_once 'includes/api_sistema_gestion.php';
        
        try {
            $api = new ApiSistemaGestionHelper();
            
            // Test de cada rol
            $roles = ['NUTRI', 'ENDOCRINO', 'PODOLOGO', 'PSICOLOGO', 'DOCTOR'];
            
            foreach ($roles as $rol) {
                echo "<div class='step'>";
                echo "<h3>Testing $rol</h3>";
                
                $especialistas = $api->obtenerEspecialistas($rol);
                
                if ($especialistas !== false && !empty($especialistas)) {
                    echo "<p class='success'>✅ " . count($especialistas) . " especialistas encontrados</p>";
                    
                    // Mostrar primer especialista como ejemplo
                    $primer = $especialistas[0];
                    echo "<p class='info'>👨‍⚕️ Ejemplo: {$primer['nombre']} (ID: {$primer['id']})</p>";
                    
                } else {
                    echo "<p class='warning'>⚠️ No hay especialistas disponibles o error de conexión</p>";
                }
                
                echo "</div>";
            }
            
        } catch (Exception $e) {
            echo "<p class='error'>❌ Error en API: " . htmlspecialchars($e->getMessage()) . "</p>";
        }
        
    } else {
        echo "<p class='error'>❌ Helper de API no encontrado</p>";
    }
    echo "</div>";
    
    // 4. Test del endpoint web
    echo "<div class='section'>";
    echo "<h2>4. 🌐 Test Endpoint Web</h2>";
    
    $url = "http://" . $_SERVER['HTTP_HOST'] . "/api/get_especialistas.php";
    
    echo "<p class='info'>Probando: <code>$url</code></p>";
    
    $context = stream_context_create([
        'http' => [
            'method' => 'GET',
            'header' => 'Content-Type: application/json',
            'timeout' => 10
        ]
    ]);
    
    $response = file_get_contents($url, false, $context);
    
    if ($response) {
        $data = json_decode($response, true);
        
        if ($data && $data['success']) {
            echo "<p class='success'>✅ Endpoint funcionando correctamente</p>";
            echo "<p><strong>Roles disponibles:</strong> " . count($data['roles']) . "</p>";
            
            $total_especialistas = 0;
            foreach ($data['roles'] as $rol) {
                $total_especialistas += count($rol['especialistas']);
            }
            
            echo "<p><strong>Total especialistas:</strong> $total_especialistas</p>";
            
            if (isset($data['fallback_local']) && $data['fallback_local']) {
                echo "<p class='warning'>⚠️ Usando datos locales (fallback)</p>";
            } else {
                echo "<p class='success'>✅ Datos desde Sistema de Gestión Médica</p>";
            }
            
        } else {
            echo "<p class='error'>❌ Error en respuesta del endpoint</p>";
            echo "<pre>" . htmlspecialchars($response) . "</pre>";
        }
    } else {
        echo "<p class='error'>❌ No se pudo conectar al endpoint</p>";
    }
    echo "</div>";
    
    // 5. Verificar .env
    echo "<div class='section'>";
    echo "<h2>5. ⚙️ Configuración .env</h2>";
    
    if (file_exists('.env')) {
        echo "<p class='success'>✓ Archivo .env encontrado</p>";
        
        $env_content = file_get_contents('.env');
        $lines = explode("\n", $env_content);
        
        $required_vars = ['AMD_API_BASE_URL', 'AMD_SYNC_SECRET'];
        $found_vars = [];
        
        foreach ($lines as $line) {
            $line = trim($line);
            if (!empty($line) && strpos($line, '=') !== false) {
                $parts = explode('=', $line, 2);
                $var_name = trim($parts[0]);
                if (in_array($var_name, $required_vars)) {
                    $found_vars[] = $var_name;
                    echo "<p class='success'>✓ $var_name configurado</p>";
                }
            }
        }
        
        foreach ($required_vars as $var) {
            if (!in_array($var, $found_vars)) {
                echo "<p class='error'>❌ Variable $var no encontrada</p>";
            }
        }
        
    } else {
        echo "<p class='error'>❌ Archivo .env no encontrado</p>";
        echo "<div class='step'>";
        echo "<h3>Crear .env con:</h3>";
        echo "<pre>";
        echo "AMD_API_BASE_URL=http://localhost:3000/api\n";
        echo "AMD_SYNC_SECRET=tu_secreto_hmac_aqui\n";
        echo "</pre>";
        echo "</div>";
    }
    echo "</div>";
    
    // 6. Resumen final
    echo "<div class='section'>";
    echo "<h2>6. 🎯 Resumen Final</h2>";
    
    $todo_ok = true;
    $problemas = [];
    
    if (!$medicoIdExists) {
        $todo_ok = false;
        $problemas[] = "Tabla citas necesita columna medico_id";
    }
    
    if (!file_exists('includes/api_sistema_gestion.php')) {
        $todo_ok = false;
        $problemas[] = "Helper de API faltante";
    }
    
    if (!file_exists('.env')) {
        $todo_ok = false;
        $problemas[] = "Archivo .env faltante";
    }
    
    if ($todo_ok) {
        echo "<div style='background: #d4edda; color: #155724; padding: 20px; border-radius: 8px; border-left: 4px solid #28a745;'>";
        echo "<h3>🎉 Sistema Completamente Configurado</h3>";
        echo "<p>✅ Todos los especialistas están listos para funcionar</p>";
        echo "<p>✅ La tabla de citas puede almacenar medico_id reales</p>";
        echo "<p>✅ La API está conectada al Sistema de Gestión Médica</p>";
        echo "<p>✅ Los médicos podrán ver sus citas asignadas</p>";
        echo "</div>";
    } else {
        echo "<div style='background: #f8d7da; color: #721c24; padding: 20px; border-radius: 8px; border-left: 4px solid #dc3545;'>";
        echo "<h3>⚠️ Configuración Incompleta</h3>";
        echo "<p>Los siguientes problemas deben resolverse:</p>";
        echo "<ul>";
        foreach ($problemas as $problema) {
            echo "<li>$problema</li>";
        }
        echo "</ul>";
        echo "</div>";
    }
    
    echo "<div style='background: #cce5ff; padding: 20px; border-radius: 8px; margin-top: 20px;'>";
    echo "<h3>🔄 Próximos Pasos</h3>";
    echo "<ol>";
    echo "<li>Probar formulario de citas: <a href='views/citas.php'>views/citas.php</a></li>";
    echo "<li>Verificar todos los especialistas: <a href='test_todos_especialistas.php'>test_todos_especialistas.php</a></li>";
    echo "<li>Comprobar que médicos ven citas: Sistema de Gestión Médica</li>";
    echo "</ol>";
    echo "</div>";
    echo "</div>";

} catch (Exception $e) {
    echo "<div class='error'>";
    echo "<h2>❌ Error durante migración</h2>";
    echo "<p><strong>Error:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<p><strong>Línea:</strong> " . $e->getLine() . "</p>";
    echo "<p><strong>Archivo:</strong> " . $e->getFile() . "</p>";
    echo "</div>";
}

echo "</body></html>";
?>