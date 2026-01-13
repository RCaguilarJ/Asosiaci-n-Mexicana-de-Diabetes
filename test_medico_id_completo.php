<?php
/**
 * Script de prueba para validar flujo completo:
 * 1. Paciente elige especialista específico
 * 2. Se guarda medico_id en tabla citas local
 * 3. Se sincroniza con medicoId correcto a Sistema de Gestión
 * 4. Médico ve la cita al filtrar por su ID
 */

require_once 'includes/db.php';
require_once 'includes/api_sistema_gestion.php';

header('Content-Type: text/html; charset=utf-8');

echo "<!DOCTYPE html>";
echo "<html><head><title>Test Flujo Completo - Médico ID</title>";
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
</style>";
echo "</head><body>";

echo "<h1>🩺 Test Flujo Completo - Asignación de Médico Específico</h1>";

try {
    // 1. Verificar estructura de tabla local
    echo "<div class='section'>";
    echo "<h2>1. 📋 Verificar Estructura de Tabla Local</h2>";
    
    $stmt = $pdo->query("DESCRIBE citas");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $tieneColumnaMedicoId = false;
    $tieneColumnaPacienteCurp = false;
    
    echo "<table>";
    echo "<tr><th>Columna</th><th>Tipo</th><th>NULL</th><th>Clave</th></tr>";
    
    foreach ($columns as $col) {
        if ($col['Field'] === 'medico_id') $tieneColumnaMedicoId = true;
        if ($col['Field'] === 'paciente_curp') $tieneColumnaPacienteCurp = true;
        
        echo "<tr>";
        echo "<td><strong>" . htmlspecialchars($col['Field']) . "</strong></td>";
        echo "<td>" . htmlspecialchars($col['Type']) . "</td>";
        echo "<td>" . htmlspecialchars($col['Null']) . "</td>";
        echo "<td>" . htmlspecialchars($col['Key']) . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    if ($tieneColumnaMedicoId) {
        echo "<p class='success'>✅ Columna medico_id presente</p>";
    } else {
        echo "<p class='error'>❌ Columna medico_id FALTANTE - Ejecutar migración primero</p>";
    }
    
    if ($tieneColumnaPacienteCurp) {
        echo "<p class='success'>✅ Columna paciente_curp presente</p>";
    } else {
        echo "<p class='warning'>⚠️ Columna paciente_curp faltante (opcional)</p>";
    }
    echo "</div>";
    
    // 2. Test obtener especialistas con IDs
    echo "<div class='section'>";
    echo "<h2>2. 👨‍⚕️ Test Obtener Especialistas con IDs</h2>";
    
    $api = new ApiSistemaGestionHelper();
    $especialistas = $api->obtenerEspecialistas();
    
    if ($especialistas && !empty($especialistas)) {
        echo "<p class='success'>✅ " . count($especialistas) . " especialistas obtenidos desde API</p>";
        
        echo "<table>";
        echo "<tr><th>ID</th><th>Nombre</th><th>Rol</th><th>Email</th><th>Username</th></tr>";
        
        foreach ($especialistas as $esp) {
            echo "<tr>";
            echo "<td><strong>" . htmlspecialchars($esp['id']) . "</strong></td>";
            echo "<td>" . htmlspecialchars($esp['nombre']) . "</td>";
            echo "<td><span style='background: #007bff; color: white; padding: 2px 6px; border-radius: 3px;'>" . htmlspecialchars($esp['role']) . "</span></td>";
            echo "<td>" . htmlspecialchars($esp['email'] ?? 'N/A') . "</td>";
            echo "<td>" . htmlspecialchars($esp['username'] ?? 'N/A') . "</td>";
            echo "</tr>";
        }
        echo "</table>";
        
    } else {
        echo "<p class='error'>❌ Error obteniendo especialistas - Verificar que backend Node.js esté ejecutándose</p>";
        echo "<p class='info'>💡 El backend debe estar en http://localhost:3000/api</p>";
        exit;
    }
    echo "</div>";
    
    // 3. Test crear cita con médico específico
    echo "<div class='section'>";
    echo "<h2>3. 📅 Test Crear Cita con Médico Específico</h2>";
    
    // Usar el primer especialista como ejemplo
    $medicoElegido = $especialistas[0];
    $usuarioId = 1; // Usuario de prueba
    
    echo "<h3>Médico seleccionado:</h3>";
    echo "<div style='background: #f8f9fa; padding: 15px; border-radius: 8px; margin: 10px 0;'>";
    echo "<p><strong>ID:</strong> {$medicoElegido['id']}</p>";
    echo "<p><strong>Nombre:</strong> {$medicoElegido['nombre']}</p>";
    echo "<p><strong>Rol:</strong> {$medicoElegido['role']}</p>";
    echo "</div>";
    
    // Simular inserción en tabla local
    $fechaHora = date('Y-m-d H:i:s', strtotime('+1 day +10:00'));
    $especialidad = $medicoElegido['role'] === 'NUTRI' ? 'Nutrición' : 
                   ($medicoElegido['role'] === 'ENDOCRINO' ? 'Endocrinología' : 'Medicina General');
    
    if ($tieneColumnaMedicoId) {
        $stmt = $pdo->prepare("
            INSERT INTO citas (usuario_id, medico_id, fecha_cita, especialidad, descripcion, estado) 
            VALUES (?, ?, ?, ?, ?, 'pendiente')
        ");
        
        $resultado = $stmt->execute([
            $usuarioId,
            $medicoElegido['id'],  // ID específico del médico
            $fechaHora,
            $especialidad,
            'Cita de prueba para validar asignación de médico específico'
        ]);
        
        if ($resultado) {
            $citaLocalId = $pdo->lastInsertId();
            echo "<p class='success'>✅ Cita guardada en tabla local con medico_id = {$medicoElegido['id']}</p>";
            echo "<p class='info'>ID de cita local: $citaLocalId</p>";
            
            // Verificar que se guardó correctamente
            $stmt = $pdo->prepare("SELECT * FROM citas WHERE id = ?");
            $stmt->execute([$citaLocalId]);
            $citaGuardada = $stmt->fetch(PDO::FETCH_ASSOC);
            
            echo "<h4>Datos guardados en tabla local:</h4>";
            echo "<table>";
            echo "<tr><th>Campo</th><th>Valor</th></tr>";
            foreach ($citaGuardada as $campo => $valor) {
                echo "<tr><td><strong>$campo</strong></td><td>" . htmlspecialchars($valor ?? 'NULL') . "</td></tr>";
            }
            echo "</table>";
            
            // 4. Test sincronización con API
            echo "<h3>Probando sincronización con API:</h3>";
            
            $datosCita = [
                'pacienteId' => $usuarioId,
                'medicoId' => $medicoElegido['id'],  // ID específico
                'fechaHora' => date('c', strtotime($fechaHora)),
                'motivo' => $especialidad . ': Cita de prueba para validar sincronización',
                'notas' => json_encode([
                    'test' => true,
                    'cita_local_id' => $citaLocalId,
                    'medico_seleccionado' => $medicoElegido['nombre']
                ])
            ];
            
            echo "<div style='background: #f8f9fa; padding: 10px; border-radius: 4px; margin: 10px 0;'>";
            echo "<strong>Payload a enviar:</strong><br>";
            echo "<pre>" . json_encode($datosCita, JSON_PRETTY_PRINT) . "</pre>";
            echo "</div>";
            
            $resultadoSync = $api->crearCita($datosCita);
            
            if ($resultadoSync['success']) {
                echo "<p class='success'>✅ Cita sincronizada exitosamente con Sistema de Gestión Médica</p>";
                echo "<p class='info'>ID de cita remota: " . ($resultadoSync['cita']['id'] ?? 'N/A') . "</p>";
                
                if (isset($resultadoSync['cita']['medico'])) {
                    $medicoAsignado = $resultadoSync['cita']['medico'];
                    echo "<p class='success'>✅ Médico asignado correctamente: " . htmlspecialchars($medicoAsignado['nombre'] ?? 'N/A') . " (ID: " . ($medicoAsignado['id'] ?? 'N/A') . ")</p>";
                }
                
            } else {
                echo "<p class='error'>❌ Error sincronizando: " . htmlspecialchars($resultadoSync['error']) . "</p>";
            }
            
        } else {
            echo "<p class='error'>❌ Error guardando cita en tabla local</p>";
        }
        
    } else {
        echo "<p class='error'>❌ No se puede probar: falta columna medico_id</p>";
    }
    echo "</div>";
    
    // 5. Verificar citas guardadas
    echo "<div class='section'>";
    echo "<h2>4. 📊 Verificar Citas en Tabla Local</h2>";
    
    $stmt = $pdo->query("
        SELECT id, usuario_id, medico_id, fecha_cita, especialidad, descripcion, estado 
        FROM citas 
        ORDER BY fecha_cita DESC 
        LIMIT 10
    ");
    
    $citasGuardadas = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (!empty($citasGuardadas)) {
        echo "<table>";
        echo "<tr><th>ID</th><th>Usuario</th><th>Médico ID</th><th>Fecha</th><th>Especialidad</th><th>Estado</th></tr>";
        
        foreach ($citasGuardadas as $cita) {
            $claseMedico = $cita['medico_id'] ? 'success' : 'error';
            echo "<tr>";
            echo "<td>{$cita['id']}</td>";
            echo "<td>{$cita['usuario_id']}</td>";
            echo "<td class='$claseMedico'><strong>" . ($cita['medico_id'] ?? 'NULL') . "</strong></td>";
            echo "<td>{$cita['fecha_cita']}</td>";
            echo "<td>{$cita['especialidad']}</td>";
            echo "<td>{$cita['estado']}</td>";
            echo "</tr>";
        }
        echo "</table>";
        
        $conMedicoId = array_filter($citasGuardadas, function($c) { return !empty($c['medico_id']); });
        echo "<p class='info'>Citas con medico_id: " . count($conMedicoId) . " de " . count($citasGuardadas) . "</p>";
        
    } else {
        echo "<p class='warning'>⚠️ No hay citas en la tabla local</p>";
    }
    echo "</div>";
    
    // 6. Resumen y siguientes pasos
    echo "<div class='section'>";
    echo "<h2>5. 📝 Resumen del Test</h2>";
    
    echo "<h3>✅ Validaciones exitosas:</h3>";
    echo "<ul>";
    if ($tieneColumnaMedicoId) echo "<li>✅ Columna medico_id presente en tabla citas</li>";
    if ($especialistas) echo "<li>✅ API de especialistas funcional</li>";
    if (isset($resultado) && $resultado) echo "<li>✅ Inserción con medico_id específico exitosa</li>";
    if (isset($resultadoSync) && $resultadoSync['success']) echo "<li>✅ Sincronización con medicoId correcto</li>";
    echo "</ul>";
    
    echo "<h3>🎯 Próximos pasos para completar la integración:</h3>";
    echo "<ol>";
    echo "<li>Verificar que el backend Node.js esté ejecutándose en puerto 3000</li>";
    echo "<li>Confirmar que los endpoints de la API estén respondiendo correctamente</li>";
    echo "<li>Probar el formulario web desde <a href='views/citas.php'>views/citas.php</a></li>";
    echo "<li>Iniciar sesión como médico en el sistema de gestión y verificar que vea sus citas</li>";
    echo "<li>Validar que el filtro WHERE medicoId = req.user.id funcione correctamente</li>";
    echo "</ol>";
    
    if (!$tieneColumnaMedicoId) {
        echo "<div style='background: #fff3cd; border: 1px solid #ffeaa7; padding: 15px; border-radius: 8px; margin: 10px 0;'>";
        echo "<h4>⚠️ ACCIÓN REQUERIDA:</h4>";
        echo "<p>Ejecutar primero la migración: <a href='migration_medico_id.php'>migration_medico_id.php</a></p>";
        echo "</div>";
    }
    echo "</div>";
    
} catch (Exception $e) {
    echo "<div class='error'>";
    echo "<h2>❌ Error en las pruebas</h2>";
    echo "<p><strong>Error:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "</div>";
}

echo "</body></html>";
?>