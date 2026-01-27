<?php
/**
 * Test completo del flujo de citas para todos los especialistas
 * Verifica que cada rol funcione correctamente
 */

require_once 'includes/db.php';
require_once 'includes/api_sistema_gestion.php';

session_start();

header('Content-Type: text/html; charset=utf-8');

echo "<!DOCTYPE html>";
echo "<html><head><title>Test Completo - Todos los Especialistas</title>";
echo "<style>
    body { font-family: Arial; margin: 20px; line-height: 1.6; }
    .success { color: #28a745; }
    .error { color: #dc3545; }
    .warning { color: #ffc107; }
    .info { color: #17a2b8; }
    .section { margin: 25px 0; padding: 20px; border: 1px solid #ddd; border-radius: 8px; }
    table { width: 100%; border-collapse: collapse; margin: 10px 0; }
    th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
    th { background: #f8f9fa; }
    .test-card { background: #f8f9fa; padding: 15px; margin: 10px 0; border-radius: 8px; border-left: 4px solid #007bff; }
</style>";
echo "</head><body>";

echo "<h1>🏥 Test Completo - Todos los Especialistas</h1>";

// Simular usuario logueado para las pruebas
if (!isset($_SESSION['usuario_id'])) {
    $_SESSION['usuario_id'] = 1;
    $_SESSION['usuario_nombre'] = 'Usuario Test';
    echo "<p class='warning'>⚠️ Sesión simulada para pruebas (usuario_id: 1)</p>";
}

try {
    $api = new ApiSistemaGestionHelper();
    
    // 1. Test de todos los especialistas
    echo "<div class='section'>";
    echo "<h2>1. 🧑‍⚕️ Especialistas por Rol</h2>";
    
    $todosLosRoles = ['NUTRI', 'ENDOCRINO', 'PODOLOGO', 'PSICOLOGO', 'DOCTOR'];
    $especialistasPorRol = [];
    
    foreach ($todosLosRoles as $rol) {
        echo "<div class='test-card'>";
        echo "<h3>$rol</h3>";
        
        $especialistas = $api->obtenerEspecialistas($rol);
        
        if ($especialistas !== false && !empty($especialistas)) {
            echo "<p class='success'>✓ " . count($especialistas) . " especialistas encontrados</p>";
            
            echo "<table>";
            echo "<tr><th>ID</th><th>Nombre</th><th>Email</th><th>Username</th></tr>";
            
            foreach ($especialistas as $esp) {
                echo "<tr>";
                echo "<td>{$esp['id']}</td>";
                echo "<td>{$esp['nombre']}</td>";
                echo "<td>{$esp['email']}</td>";
                echo "<td>{$esp['username'] ?? 'N/A'}</td>";
                echo "</tr>";
                
                $especialistasPorRol[$rol][] = $esp;
            }
            echo "</table>";
            
        } else {
            echo "<p class='error'>✗ No se encontraron especialistas para $rol</p>";
            $especialistasPorRol[$rol] = [];
        }
        
        echo "</div>";
    }
    echo "</div>";
    
    // 2. Test de creación de citas para cada rol
    echo "<div class='section'>";
    echo "<h2>2. 📅 Test de Citas por Especialista</h2>";
    
    foreach ($especialistasPorRol as $rol => $especialistas) {
        if (empty($especialistas)) {
            echo "<div class='test-card'>";
            echo "<h3>$rol - SALTADO</h3>";
            echo "<p class='warning'>⚠️ No hay especialistas disponibles para probar</p>";
            echo "</div>";
            continue;
        }
        
        // Tomar el primer especialista de cada rol
        $especialista = $especialistas[0];
        
        echo "<div class='test-card'>";
        echo "<h3>$rol - {$especialista['nombre']}</h3>";
        
        // Datos de prueba para la cita
        $datosCita = [
            'pacienteId' => $_SESSION['usuario_id'],
            'medicoId' => $especialista['id'],
            'fechaHora' => date('c', strtotime('+' . rand(1, 30) . ' days +' . rand(9, 17) . ' hours')),
            'motivo' => "Consulta de prueba para $rol",
            'notas' => json_encode([
                'test' => true,
                'especialista' => $especialista['nombre'],
                'rol' => $rol,
                'fecha_test' => date('Y-m-d H:i:s')
            ])
        ];
        
        echo "<p class='info'><strong>Datos de la cita:</strong></p>";
        echo "<ul>";
        echo "<li><strong>Paciente ID:</strong> {$datosCita['pacienteId']}</li>";
        echo "<li><strong>Médico ID:</strong> {$datosCita['medicoId']}</li>";
        echo "<li><strong>Médico:</strong> {$especialista['nombre']}</li>";
        echo "<li><strong>Fecha/Hora:</strong> {$datosCita['fechaHora']}</li>";
        echo "<li><strong>Motivo:</strong> {$datosCita['motivo']}</li>";
        echo "</ul>";
        
        // Intentar crear la cita
        $resultado = $api->crearCita($datosCita);
        
        if ($resultado['success']) {
            echo "<p class='success'>✅ Cita creada exitosamente</p>";
            
            if (isset($resultado['cita']['id'])) {
                echo "<p class='info'>📋 <strong>ID de cita en sistema médico:</strong> {$resultado['cita']['id']}</p>";
            }
            
            if (isset($resultado['cita']['medico'])) {
                $medico = $resultado['cita']['medico'];
                echo "<p class='success'>👨‍⚕️ <strong>Médico asignado:</strong> {$medico['nombre']} (Rol: {$medico['role']})</p>";
                
                // Verificar que el rol coincida
                if ($medico['role'] === $rol) {
                    echo "<p class='success'>✅ Rol correcto asignado</p>";
                } else {
                    echo "<p class='error'>❌ Rol incorrecto: esperado $rol, obtenido {$medico['role']}</p>";
                }
            }
            
        } else {
            echo "<p class='error'>❌ Error creando cita: {$resultado['error']}</p>";
        }
        
        echo "</div>";
        
        // Pausa pequeña entre requests para no saturar la API
        usleep(500000); // 0.5 segundos
    }
    echo "</div>";
    
    // 3. Test del formulario web
    echo "<div class='section'>";
    echo "<h2>3. 🌐 Test del Formulario Web</h2>";
    
    echo "<p class='info'>Verificando endpoint de especialistas...</p>";
    
    // Test del endpoint get_especialistas
    $url = "http://" . $_SERVER['HTTP_HOST'] . "/api/get_especialistas.php";
    
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
            echo "<p><strong>Total de roles:</strong> " . count($data['roles']) . "</p>";
            
            echo "<table>";
            echo "<tr><th>Especialidad</th><th>Rol Técnico</th><th>Especialistas</th></tr>";
            
            foreach ($data['roles'] as $rol) {
                $count = count($rol['especialistas']);
                echo "<tr>";
                echo "<td>{$rol['nombre_rol']}</td>";
                echo "<td>{$rol['role']}</td>";
                echo "<td class='" . ($count > 0 ? 'success' : 'error') . "'>$count</td>";
                echo "</tr>";
            }
            echo "</table>";
            
        } else {
            echo "<p class='error'>❌ Error en respuesta del endpoint</p>";
            if ($data) {
                echo "<pre>" . htmlspecialchars(json_encode($data, JSON_PRETTY_PRINT)) . "</pre>";
            }
        }
    } else {
        echo "<p class='error'>❌ No se pudo conectar al endpoint</p>";
    }
    echo "</div>";
    
    // 4. Verificar tabla local
    echo "<div class='section'>";
    echo "<h2>4. 🗃️ Verificar Tabla Local 'citas'</h2>";
    
    // Verificar estructura
    $columns = $pdo->query("DESCRIBE citas")->fetchAll(PDO::FETCH_ASSOC);
    $medicoIdExists = false;
    
    foreach ($columns as $column) {
        if ($column['Field'] === 'medico_id') {
            $medicoIdExists = true;
            break;
        }
    }
    
    if ($medicoIdExists) {
        echo "<p class='success'>✅ Columna 'medico_id' existe en tabla citas</p>";
    } else {
        echo "<p class='error'>❌ Columna 'medico_id' NO existe. Ejecutar migración.</p>";
    }
    
    // Mostrar últimas citas
    $stmt = $pdo->query("SELECT * FROM citas ORDER BY fecha_registro DESC LIMIT 5");
    $ultimasCitas = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (!empty($ultimasCitas)) {
        echo "<h3>Últimas 5 citas registradas:</h3>";
        echo "<table>";
        echo "<tr><th>ID</th><th>Usuario ID</th><th>Médico ID</th><th>Especialidad</th><th>Fecha Cita</th><th>Estado</th></tr>";
        
        foreach ($ultimasCitas as $cita) {
            echo "<tr>";
            echo "<td>{$cita['id']}</td>";
            echo "<td>{$cita['usuario_id']}</td>";
            echo "<td class='" . ($cita['medico_id'] ? 'success' : 'error') . "'>" . ($cita['medico_id'] ?: 'NULL') . "</td>";
            echo "<td>{$cita['especialidad']}</td>";
            echo "<td>{$cita['fecha_cita']}</td>";
            echo "<td>{$cita['estado']}</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p class='info'>ℹ️ No hay citas registradas aún</p>";
    }
    echo "</div>";
    
    // 5. Resumen final
    echo "<div class='section'>";
    echo "<h2>5. 📊 Resumen Final</h2>";
    
    $totalEspecialistas = array_sum(array_map('count', $especialistasPorRol));
    $rolesConEspecialistas = count(array_filter($especialistasPorRol, function($lista) { return !empty($lista); }));
    
    echo "<div style='background: #e7f3ff; padding: 20px; border-radius: 8px; border-left: 4px solid #007bff;'>";
    echo "<h3>Estado del Sistema</h3>";
    echo "<ul>";
    echo "<li><strong>Total de especialistas:</strong> $totalEspecialistas</li>";
    echo "<li><strong>Roles con especialistas:</strong> $rolesConEspecialistas de " . count($todosLosRoles) . "</li>";
    echo "<li><strong>Tabla citas:</strong> " . ($medicoIdExists ? '✅ Preparada' : '❌ Necesita migración') . "</li>";
    echo "<li><strong>API funcionando:</strong> " . (isset($data) && $data['success'] ? '✅ Sí' : '❌ No') . "</li>";
    echo "</ul>";
    
    if ($totalEspecialistas > 0 && $medicoIdExists) {
        echo "<p class='success'><strong>🎉 Sistema completamente funcional para todos los especialistas</strong></p>";
        echo "<p>Los pacientes pueden agendar citas con cualquier especialista y los médicos verán sus citas correspondientes.</p>";
    } else {
        echo "<p class='warning'><strong>⚠️ Sistema necesita configuración adicional</strong></p>";
        if (!$medicoIdExists) echo "<p>• Ejecutar migración de tabla citas</p>";
        if ($totalEspecialistas === 0) echo "<p>• Verificar conexión con Sistema de Gestión Médica</p>";
    }
    echo "</div>";
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
echo "<div style='text-align: center; margin: 20px 0;'>";
echo "<h3>🔗 Enlaces Útiles</h3>";
echo "<a href='views/citas.php' style='margin: 0 10px; padding: 10px 20px; background: #007bff; color: white; text-decoration: none; border-radius: 5px;'>Formulario de Citas</a>";
echo "<a href='verificar_migracion.php' style='margin: 0 10px; padding: 10px 20px; background: #28a745; color: white; text-decoration: none; border-radius: 5px;'>Verificar Migración</a>";
echo "<a href='test_sistema_gestion.php' style='margin: 0 10px; padding: 10px 20px; background: #17a2b8; color: white; text-decoration: none; border-radius: 5px;'>Test Sistema Gestión</a>";
echo "</div>";

echo "</body></html>";
?>