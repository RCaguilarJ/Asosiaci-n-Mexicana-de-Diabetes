<?php
/**
 * Script de prueba para verificar la sincronización entre la app de diabetes
 * y el sistema de gestión médica
 */

require_once '../includes/db.php';
require_once '../includes/sync_helper.php';

header('Content-Type: text/html; charset=utf-8');

echo "<!DOCTYPE html>";
echo "<html><head><title>Prueba de Sincronización</title>";
echo "<style>
    body { font-family: Arial; margin: 20px; }
    .success { color: green; }
    .error { color: red; }
    .info { color: blue; }
    .section { margin: 20px 0; padding: 10px; border: 1px solid #ccc; }
</style>";
echo "</head><body>";

echo "<h1>Prueba de Sincronización - App Diabetes → Sistema Gestión Médica</h1>";

try {
    // 1. Probar conexión a sistema remoto
    echo "<div class='section'>";
    echo "<h2>1. Verificar Conexión al Sistema Remoto</h2>";
    
    $remoteConnection = getRemoteConnection();
    if ($remoteConnection) {
        echo "<p class='success'>✓ Conexión exitosa al sistema de gestión médica</p>";
    } else {
        echo "<p class='error'>✗ Error: No se pudo conectar al sistema remoto</p>";
        throw new Exception("Conexión fallida");
    }
    echo "</div>";

    // 2. Probar sincronización de paciente
    echo "<div class='section'>";
    echo "<h2>2. Sincronización de Paciente de Prueba</h2>";
    
    $pacientePrueba = [
        'nombre' => 'Paciente Prueba Sync',
        'email' => 'prueba.sync.' . time() . '@test.com',
        'telefono' => '555-0123',
        'usuario_id_app' => 9999
    ];
    
    $pacienteId = sincronizarPacienteEnSistemaGestion($pacientePrueba);
    
    if ($pacienteId) {
        echo "<p class='success'>✓ Paciente sincronizado exitosamente</p>";
        echo "<p class='info'>ID del paciente en sistema remoto: $pacienteId</p>";
        echo "<p class='info'>Datos: " . json_encode($pacientePrueba, JSON_PRETTY_PRINT) . "</p>";
    } else {
        echo "<p class='error'>✗ Error al sincronizar paciente</p>";
    }
    echo "</div>";

    // 3. Probar sincronización de cita
    if ($pacienteId) {
        echo "<div class='section'>";
        echo "<h2>3. Sincronización de Cita de Prueba</h2>";
        
        $citaPrueba = [
            'paciente_id' => $pacienteId,
            'fecha_hora' => date('Y-m-d H:i:s', strtotime('+1 week')),
            'tipo_cita' => 'Nutrición',
            'motivo' => 'Consulta de prueba para verificar sincronización',
            'estado' => 'pendiente'
        ];
        
        $citaId = sincronizarCitaEnSistemaGestion($citaPrueba);
        
        if ($citaId) {
            echo "<p class='success'>✓ Cita sincronizada exitosamente</p>";
            echo "<p class='info'>ID de la cita en sistema remoto: $citaId</p>";
            echo "<p class='info'>Datos: " . json_encode($citaPrueba, JSON_PRETTY_PRINT) . "</p>";
        } else {
            echo "<p class='error'>✗ Error al sincronizar cita</p>";
        }
        echo "</div>";
    }

    // 4. Probar mapeo de especialidades
    echo "<div class='section'>";
    echo "<h2>4. Verificar Mapeo de Especialidades a Roles</h2>";
    
    $especialidades = ['Nutrición', 'Endocrinología', 'Podología', 'Psicología', 'Medicina General'];
    
    foreach ($especialidades as $esp) {
        $rol = mapearEspecialidadARol($esp);
        echo "<p class='info'>$esp → $rol</p>";
    }
    echo "</div>";

    // 5. Probar consulta por rol
    echo "<div class='section'>";
    echo "<h2>5. Consultar Pacientes por Rol</h2>";
    
    $roles = ['NUTRI', 'ENDOCRINO', 'PODOLOGO', 'PSICOLOGO'];
    
    foreach ($roles as $rol) {
        $pacientes = obtenerPacientesPorRol($rol);
        echo "<p class='info'>Rol $rol: " . count($pacientes) . " pacientes encontrados</p>";
        
        if (!empty($pacientes)) {
            echo "<ul>";
            foreach (array_slice($pacientes, 0, 3) as $p) { // Solo mostrar primeros 3
                echo "<li>{$p['nombre']} - {$p['email']}</li>";
            }
            if (count($pacientes) > 3) {
                echo "<li>... y " . (count($pacientes) - 3) . " más</li>";
            }
            echo "</ul>";
        }
    }
    echo "</div>";

} catch (Exception $e) {
    echo "<div class='error'>";
    echo "<h2>Error en las pruebas</h2>";
    echo "<p>Error: " . $e->getMessage() . "</p>";
    echo "</div>";
}

echo "<div class='section'>";
echo "<h2>6. URLs de las APIs creadas</h2>";
echo "<p><strong>Consultar pacientes por rol:</strong><br>";
echo "<code>GET /api/pacientes_por_rol.php?rol=NUTRI</code></p>";
echo "<p><strong>Historial de paciente:</strong><br>";
echo "<code>GET /api/historial_paciente.php?paciente_id=1&rol=NUTRI</code></p>";
echo "</div>";

echo "<div class='section'>";
echo "<h2>Estado del Sistema</h2>";
echo "<p class='info'>Fecha de prueba: " . date('Y-m-d H:i:s') . "</p>";
echo "<p class='info'>Servidor: " . $_SERVER['SERVER_NAME'] . "</p>";
echo "</div>";

echo "</body></html>";
?>