<?php
/**
 * Test directo de guardar_cita.php para debug
 */

session_start();

// Simular sesión de usuario
$_SESSION['usuario_id'] = 1;
$_SESSION['usuario_nombre'] = 'Test User';

// Simular datos POST
$_POST = [
    'nombre' => 'Juan Pérez Test',
    'email' => 'juan@test.com',
    'telefono' => '33-1234-5678',
    'especialidad' => 'Nutrición',
    'medico_id' => '101',
    'fecha' => '2026-01-15',
    'hora' => '10:00',
    'descripcion' => 'Consulta de prueba desde test'
];

$_SERVER['REQUEST_METHOD'] = 'POST';

echo "<h2>🧪 Test directo de guardar_cita.php</h2>";
echo "<p><strong>Datos de prueba:</strong></p>";
echo "<pre>" . json_encode($_POST, JSON_PRETTY_PRINT) . "</pre>";

echo "<p><strong>Ejecutando guardar_cita.php:</strong></p>";
echo "<pre>";

// Incluir el archivo
ob_start();
include 'actions/guardar_cita.php';
$output = ob_get_clean();

echo htmlspecialchars($output);
echo "</pre>";

echo "<p><strong>Logs de PHP:</strong></p>";
echo "<p>Revisar logs en: <code>C:\wamp64\logs\php_error.log</code></p>";
?>