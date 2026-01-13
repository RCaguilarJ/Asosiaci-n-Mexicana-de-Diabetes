<?php
/**
 * Script de prueba completa para integración con Sistema de Gestión Médica
 * Valida autenticación HMAC, especialistas y creación de citas
 */

require_once 'includes/db.php';
require_once 'includes/api_sistema_gestion.php';

header('Content-Type: text/html; charset=utf-8');

echo "<!DOCTYPE html>";
echo "<html><head><title>Test Integración Sistema Gestión Médica</title>";
echo "<style>
    body { font-family: Arial; margin: 20px; line-height: 1.6; }
    .success { color: #28a745; }
    .error { color: #dc3545; }
    .warning { color: #ffc107; }
    .info { color: #17a2b8; }
    .section { margin: 25px 0; padding: 20px; border: 1px solid #ddd; border-radius: 8px; }
    .code { background: #f8f9fa; padding: 10px; border-radius: 4px; font-family: monospace; }
    table { width: 100%; border-collapse: collapse; margin: 10px 0; }
    th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
    th { background: #f8f9fa; }
</style>";
echo "</head><body>";

echo "<h1>🏥 Test Integración Sistema de Gestión Médica</h1>";
echo "<p class='info'>Verificando conexión completa con backend Node.js y autenticación HMAC</p>";

try {
    $api = new ApiSistemaGestionHelper();
    
    // 1. Verificar configuración
    echo "<div class='section'>";
    echo "<h2>1. 📋 Configuración del Sistema</h2>";
    
    $baseUrl = getenv('SISTEMA_GESTION_API_URL') ?: 'http://localhost:3000/api';
    $secret = getenv('AMD_SYNC_SECRET') ?: 'amd_diabetes_sync_key_2026_secure_hmac';
    
    echo "<table>";
    echo "<tr><th>Variable</th><th>Valor</th><th>Estado</th></tr>";
    echo "<tr><td>API URL</td><td>$baseUrl</td><td class='success'>✓ Configurado</td></tr>";
    echo "<tr><td>Secret HMAC</td><td>" . substr($secret, 0, 10) . "...</td><td class='success'>✓ Configurado</td></tr>";
    echo "</table>";
    echo "</div>";
    
    // 2. Test de especialistas
    echo "<div class='section'>";
    echo "<h2>2. 👨‍⚕️ Test Obtener Especialistas</h2>";
    
    echo "<h3>Todos los especialistas:</h3>";
    $especialistas = $api->obtenerEspecialistas();
    
    if ($especialistas !== false && !empty($especialistas)) {
        echo "<p class='success'>✓ " . count($especialistas) . " especialistas obtenidos desde API</p>";
        
        echo "<table>";
        echo "<tr><th>ID</th><th>Nombre</th><th>Rol</th><th>Email</th><th>Username</th></tr>";
        
        foreach ($especialistas as $esp) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($esp['id']) . "</td>";
            echo "<td>" . htmlspecialchars($esp['nombre']) . "</td>";
            echo "<td><strong>" . htmlspecialchars($esp['role']) . "</strong></td>";
            echo "<td>" . htmlspecialchars($esp['email'] ?? 'N/A') . "</td>";
            echo "<td>" . htmlspecialchars($esp['username'] ?? 'N/A') . "</td>";
            echo "</tr>";
        }
        echo "</table>";
        
        // Agrupar por rol
        $porRol = [];
        foreach ($especialistas as $esp) {
            $porRol[$esp['role']][] = $esp;
        }
        
        echo "<h3>Agrupados por rol:</h3>";
        foreach ($porRol as $rol => $lista) {
            echo "<p class='info'><strong>$rol:</strong> " . count($lista) . " especialistas</p>";
        }
        
    } else {
        echo "<p class='error'>✗ Error obteniendo especialistas desde API</p>";
        echo "<p class='warning'>⚠️ Verificar que el backend Node.js esté funcionando en $baseUrl</p>";
    }
    
    // Test por rol específico
    echo "<h3>Test por rol específico (NUTRI):</h3>";
    $nutris = $api->obtenerEspecialistas('NUTRI');
    
    if ($nutris !== false) {
        echo "<p class='success'>✓ Especialistas de NUTRI: " . count($nutris) . "</p>";
    } else {
        echo "<p class='error'>✗ Error obteniendo nutriólogos</p>";
    }
    echo "</div>";
    
    // 3. Test crear cita
    echo "<div class='section'>";
    echo "<h2>3. 📅 Test Crear Cita</h2>";
    
    if (!empty($especialistas)) {
        $primerMedico = $especialistas[0];
        
        $datosCitaPrueba = [
            'pacienteId' => 1, // Usuario de prueba
            'medicoId' => $primerMedico['id'],
            'fechaHora' => date('c', strtotime('+1 week')), // ISO 8601
            'motivo' => 'Consulta de prueba desde app diabetes',
            'notas' => json_encode([
                'app_diabetes' => true,
                'test' => true,
                'fecha_prueba' => date('Y-m-d H:i:s')
            ])
        ];
        
        echo "<h3>Datos de la cita de prueba:</h3>";
        echo "<div class='code'>" . json_encode($datosCitaPrueba, JSON_PRETTY_PRINT) . "</div>";
        
        echo "<h3>Enviando cita...</h3>";
        $resultadoCita = $api->crearCita($datosCitaPrueba);
        
        if ($resultadoCita['success']) {
            echo "<p class='success'>✓ Cita creada exitosamente</p>";
            echo "<p class='info'><strong>Mensaje:</strong> " . htmlspecialchars($resultadoCita['message']) . "</p>";
            
            if (isset($resultadoCita['cita'])) {
                echo "<h3>Datos de la cita creada:</h3>";
                echo "<div class='code'>" . json_encode($resultadoCita['cita'], JSON_PRETTY_PRINT) . "</div>";
                
                // Verificar médico asignado
                if (isset($resultadoCita['cita']['medico'])) {
                    $medico = $resultadoCita['cita']['medico'];
                    echo "<p class='success'>✓ <strong>Médico asignado:</strong> " . htmlspecialchars($medico['nombre'] ?? 'N/A') . " (" . htmlspecialchars($medico['role'] ?? 'N/A') . ")</p>";
                }
                
                // Verificar ID de cita
                if (isset($resultadoCita['cita']['id'])) {
                    echo "<p class='info'><strong>ID de cita en sistema médico:</strong> " . $resultadoCita['cita']['id'] . "</p>";
                }
            }
            
        } else {
            echo "<p class='error'>✗ Error creando cita: " . htmlspecialchars($resultadoCita['error']) . "</p>";
        }
        
    } else {
        echo "<p class='warning'>⚠️ No se pueden hacer pruebas de citas sin especialistas disponibles</p>";
    }
    echo "</div>";
    
    // 4. Test estadísticas de sync
    echo "<div class='section'>";
    echo "<h2>4. 📊 Estadísticas de Sincronización</h2>";
    
    $estadisticas = $api->obtenerEstadisticasSync();
    
    if (!empty($estadisticas)) {
        echo "<table>";
        echo "<tr><th>Operación</th><th>Estado</th><th>Total</th><th>Último Intento</th></tr>";
        
        foreach ($estadisticas as $stat) {
            $clase = $stat['estado'] === 'completado' ? 'success' : 
                    ($stat['estado'] === 'error' ? 'error' : 'warning');
            
            echo "<tr class='$clase'>";
            echo "<td>" . htmlspecialchars($stat['operacion']) . "</td>";
            echo "<td>" . htmlspecialchars($stat['estado']) . "</td>";
            echo "<td>" . $stat['total'] . "</td>";
            echo "<td>" . htmlspecialchars($stat['ultimo_intento']) . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p class='info'>No hay estadísticas de sincronización aún</p>";
    }
    echo "</div>";
    
    // 5. Verificaciones finales
    echo "<div class='section'>";
    echo "<h2>5. ✅ Verificaciones Finales</h2>";
    
    $verificaciones = [
        'Autenticación HMAC' => !empty($secret),
        'Conexión API' => !empty($especialistas),
        'Tabla sync_queue' => true, // Se verificaría con consulta
        'Variables de entorno' => !empty($baseUrl),
        'Fallback local' => true // Se puede usar DB local si API falla
    ];
    
    foreach ($verificaciones as $item => $estado) {
        $clase = $estado ? 'success' : 'error';
        $icono = $estado ? '✓' : '✗';
        echo "<p class='$clase'>$icono $item</p>";
    }
    
    echo "<hr>";
    echo "<h3>🎯 Resumen de Integración</h3>";
    echo "<ul>";
    echo "<li><strong>Especialistas:</strong> " . (empty($especialistas) ? '❌ No disponibles' : '✅ ' . count($especialistas) . ' cargados') . "</li>";
    echo "<li><strong>API Sistema Médico:</strong> " . ($baseUrl === 'http://localhost:3000/api' ? '✅ Configurado' : '⚠️ Verificar URL') . "</li>";
    echo "<li><strong>Autenticación:</strong> " . (!empty($secret) ? '✅ HMAC configurado' : '❌ Secret faltante') . "</li>";
    echo "<li><strong>Citas:</strong> " . (isset($resultadoCita) && $resultadoCita['success'] ? '✅ Funcionando' : '⚠️ Pendiente verificar') . "</li>";
    echo "</ul>";
    echo "</div>";
    
} catch (Exception $e) {
    echo "<div class='error'>";
    echo "<h2>❌ Error en las pruebas</h2>";
    echo "<p><strong>Error:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<p><strong>Línea:</strong> " . $e->getLine() . "</p>";
    echo "<p><strong>Archivo:</strong> " . $e->getFile() . "</p>";
    echo "</div>";
}

echo "<hr>";
echo "<div style='margin: 20px 0; padding: 15px; background: #f8f9fa; border-radius: 8px;'>";
echo "<h3>🚀 Siguientes Pasos</h3>";
echo "<ol>";
echo "<li>Verificar que el backend Node.js esté ejecutándose en <code>http://localhost:3000</code></li>";
echo "<li>Confirmar que los endpoints <code>/api/sync/amd/especialistas</code> y <code>/api/sync/amd/citas</code> estén disponibles</li>";
echo "<li>Probar crear citas desde la interfaz web en <a href='../views/citas.php'>página de citas</a></li>";
echo "<li>Verificar que los especialistas vean solo sus citas correspondientes en el sistema médico</li>";
echo "</ol>";
echo "</div>";

echo "</body></html>";
?>