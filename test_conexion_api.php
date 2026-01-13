<?php
/**
 * Test de conexión con Sistema de Gestión Médica
 * Verifica la disponibilidad de la API externa
 */

require_once 'includes/load_env.php';

header('Content-Type: text/html; charset=utf-8');

echo "<!DOCTYPE html>";
echo "<html><head><title>Test Conexión Sistema Gestión Médica</title>";
echo "<style>
    body { font-family: Arial; margin: 20px; line-height: 1.6; }
    .success { color: #28a745; background: #d4edda; padding: 10px; border-radius: 5px; margin: 10px 0; }
    .error { color: #721c24; background: #f8d7da; padding: 10px; border-radius: 5px; margin: 10px 0; }
    .warning { color: #856404; background: #fff3cd; padding: 10px; border-radius: 5px; margin: 10px 0; }
    .info { color: #0c5460; background: #d1ecf1; padding: 10px; border-radius: 5px; margin: 10px 0; }
    .section { margin: 20px 0; padding: 20px; border: 1px solid #ddd; border-radius: 8px; }
    pre { background: #f8f9fa; padding: 15px; border-radius: 5px; overflow-x: auto; }
    .btn { display: inline-block; padding: 10px 20px; background: #007bff; color: white; text-decoration: none; border-radius: 5px; margin: 5px; }
    .btn:hover { background: #0056b3; }
    .status-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px; margin: 20px 0; }
</style>";
echo "</head><body>";

echo "<h1>🔧 Test de Conexión - Sistema de Gestión Médica</h1>";

try {
    // 1. Verificar configuración .env
    echo "<div class='section'>";
    echo "<h2>1. 📋 Configuración .env</h2>";
    
    $apiUrl = getenv('SISTEMA_GESTION_API_URL') ?: getenv('AMD_API_BASE_URL');
    $secret = getenv('AMD_SYNC_SECRET');
    
    if ($apiUrl) {
        echo "<div class='success'>✅ URL API configurada: <code>$apiUrl</code></div>";
    } else {
        echo "<div class='error'>❌ URL API no configurada</div>";
    }
    
    if ($secret) {
        echo "<div class='success'>✅ Secret HMAC configurado: <code>" . substr($secret, 0, 8) . "...</code></div>";
    } else {
        echo "<div class='error'>❌ Secret HMAC no configurado</div>";
    }
    echo "</div>";
    
    // 2. Test de conectividad básica
    echo "<div class='section'>";
    echo "<h2>2. 🌐 Test de Conectividad</h2>";
    
    if ($apiUrl) {
        $baseUrl = str_replace('/api', '', $apiUrl);
        
        echo "<div class='info'>🔍 Probando conectividad a: <code>$baseUrl</code></div>";
        
        $context = stream_context_create([
            'http' => [
                'method' => 'GET',
                'timeout' => 5,
                'ignore_errors' => true
            ]
        ]);
        
        $response = @file_get_contents($baseUrl, false, $context);
        $httpCode = 0;
        
        if (isset($http_response_header)) {
            foreach ($http_response_header as $header) {
                if (strpos($header, 'HTTP/') === 0) {
                    preg_match('/HTTP\/\d\.\d\s+(\d+)/', $header, $matches);
                    if (isset($matches[1])) {
                        $httpCode = intval($matches[1]);
                    }
                }
            }
        }
        
        if ($response !== false) {
            echo "<div class='success'>✅ Servidor responde (HTTP $httpCode)</div>";
            
            // Probar endpoint específico de especialistas
            echo "<h3>🧑‍⚕️ Test Endpoint Especialistas</h3>";
            
            $especialistasUrl = $apiUrl . '/especialistas';
            echo "<div class='info'>📡 Probando: <code>$especialistasUrl</code></div>";
            
            $especialistasResponse = @file_get_contents($especialistasUrl, false, $context);
            
            if ($especialistasResponse !== false) {
                $data = json_decode($especialistasResponse, true);
                
                if ($data !== null) {
                    echo "<div class='success'>✅ Endpoint de especialistas responde correctamente</div>";
                    echo "<pre>" . json_encode($data, JSON_PRETTY_PRINT) . "</pre>";
                } else {
                    echo "<div class='warning'>⚠️ Endpoint responde pero no es JSON válido</div>";
                    echo "<pre>" . htmlspecialchars(substr($especialistasResponse, 0, 500)) . "</pre>";
                }
            } else {
                echo "<div class='error'>❌ Endpoint de especialistas no responde</div>";
            }
            
        } else {
            echo "<div class='error'>❌ No se puede conectar al servidor</div>";
            echo "<div class='warning'>💡 El Sistema de Gestión Médica probablemente no está corriendo</div>";
        }
    }
    echo "</div>";
    
    // 3. Test del helper de API
    echo "<div class='section'>";
    echo "<h2>3. 🔌 Test Helper API</h2>";
    
    if (file_exists('includes/api_sistema_gestion.php')) {
        echo "<div class='success'>✅ Helper API encontrado</div>";
        
        require_once 'includes/api_sistema_gestion.php';
        
        try {
            $api = new ApiSistemaGestionHelper();
            echo "<div class='success'>✅ Helper instanciado correctamente</div>";
            
            // Probar obtener especialistas
            echo "<h3>👨‍⚕️ Test obtenerEspecialistas()</h3>";
            
            foreach (['NUTRI', 'DOCTOR', 'ENDOCRINO'] as $rol) {
                echo "<div class='info'>🔍 Probando rol: <code>$rol</code></div>";
                
                $especialistas = $api->obtenerEspecialistas($rol);
                
                if ($especialistas !== false && !empty($especialistas)) {
                    echo "<div class='success'>✅ $rol: " . count($especialistas) . " especialistas encontrados</div>";
                    
                    if (!empty($especialistas)) {
                        $primer = $especialistas[0];
                        echo "<div class='info'>📋 Ejemplo: {$primer['nombre']} (ID: {$primer['id']})</div>";
                    }
                } else {
                    echo "<div class='warning'>⚠️ $rol: No hay especialistas o error de conexión</div>";
                }
            }
            
        } catch (Exception $e) {
            echo "<div class='error'>❌ Error en helper: " . htmlspecialchars($e->getMessage()) . "</div>";
        }
    } else {
        echo "<div class='error'>❌ Helper API no encontrado</div>";
    }
    echo "</div>";
    
    // 4. Diagnóstico y recomendaciones
    echo "<div class='section'>";
    echo "<h2>4. 🎯 Diagnóstico y Recomendaciones</h2>";
    
    if (!$apiUrl || $response === false) {
        echo "<div class='error'>";
        echo "<h3>❌ Problema: Sistema de Gestión Médica no disponible</h3>";
        echo "<p><strong>Causas posibles:</strong></p>";
        echo "<ul>";
        echo "<li>El servidor Node.js no está corriendo en puerto 3000</li>";
        echo "<li>La URL en .env es incorrecta</li>";
        echo "<li>Problema de firewall o red</li>";
        echo "</ul>";
        
        echo "<p><strong>✅ Soluciones:</strong></p>";
        echo "<ol>";
        echo "<li><strong>Iniciar Sistema de Gestión Médica:</strong>";
        echo "<pre>cd C:\\path\\to\\sistema-gestion-medica\nnpm start</pre></li>";
        echo "<li><strong>Verificar puerto:</strong> <code>netstat -an | findstr :3000</code></li>";
        echo "<li><strong>Verificar URL en .env:</strong> <code>SISTEMA_GESTION_API_URL=http://localhost:3000/api</code></li>";
        echo "</ol>";
        echo "</div>";
        
        echo "<div class='info'>";
        echo "<h3>💡 Mientras tanto: Modo Fallback Local</h3>";
        echo "<p>El sistema continuará funcionando con especialistas locales hasta que se restaure la conexión.</p>";
        echo "<p>Los especialistas locales están configurados en la base de datos <code>diabetes_db</code>.</p>";
        echo "</div>";
        
    } else {
        echo "<div class='success'>";
        echo "<h3>🎉 ¡Excelente! Sistema funcionando correctamente</h3>";
        echo "<p>La conexión con el Sistema de Gestión Médica está activa.</p>";
        echo "<p>Los especialistas se cargan desde la API externa.</p>";
        echo "</div>";
    }
    echo "</div>";
    
    // 5. Enlaces útiles
    echo "<div class='section'>";
    echo "<h2>5. 🔗 Enlaces Útiles</h2>";
    echo "<div class='status-grid'>";
    echo "<div>";
    echo "<h4>🧪 Tests</h4>";
    echo "<a href='api/get_especialistas.php' class='btn'>Endpoint Especialistas</a><br>";
    echo "<a href='test_selector_especialistas.php' class='btn'>Test Selector</a><br>";
    echo "<a href='test_todos_especialistas.php' class='btn'>Test Completo</a>";
    echo "</div>";
    echo "<div>";
    echo "<h4>📋 Formularios</h4>";
    echo "<a href='views/citas.php' class='btn'>Agendar Cita</a><br>";
    echo "<a href='setup_database.php' class='btn'>Setup DB</a>";
    echo "</div>";
    echo "<div>";
    echo "<h4>⚙️ Configuración</h4>";
    echo "<a href='ejecutar_migracion.php' class='btn'>Migración</a><br>";
    echo "<a href='verificar_migracion.php' class='btn'>Verificar</a>";
    echo "</div>";
    echo "</div>";
    echo "</div>";

} catch (Exception $e) {
    echo "<div class='error'>";
    echo "<h2>❌ Error durante diagnóstico</h2>";
    echo "<p><strong>Error:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "</div>";
}

echo "</body></html>";
?>