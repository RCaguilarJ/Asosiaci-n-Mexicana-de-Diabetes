<?php
/**
 * Simulador del panel de médico - Sistema de Gestión Médica
 * Muestra las citas asignadas a cada médico
 */

require_once 'includes/db.php';

header('Content-Type: text/html; charset=utf-8');

// Simular médicos disponibles
$medicos = [
    101 => ['nombre' => 'Dra. María Fernández', 'role' => 'NUTRI', 'especialidad' => 'Nutriología Clínica'],
    102 => ['nombre' => 'Lic. Carlos Nutrition', 'role' => 'NUTRI', 'especialidad' => 'Nutrición Deportiva'],
    201 => ['nombre' => 'Dr. José Endocrino', 'role' => 'ENDOCRINO', 'especialidad' => 'Endocrinología y Diabetes'],
    202 => ['nombre' => 'Dra. Ana Hormona', 'role' => 'ENDOCRINO', 'especialidad' => 'Trastornos Hormonales'],
    301 => ['nombre' => 'Dr. Luis Pies', 'role' => 'PODOLOGO', 'especialidad' => 'Podología Clínica'],
    401 => ['nombre' => 'Dra. Carmen Mente', 'role' => 'PSICOLOGO', 'especialidad' => 'Psicología Clínica'],
    402 => ['nombre' => 'Dr. Rafael Salud', 'role' => 'PSICOLOGO', 'especialidad' => 'Psicología de la Salud'],
    501 => ['nombre' => 'Dr. Roberto General', 'role' => 'DOCTOR', 'especialidad' => 'Medicina General'],
    502 => ['nombre' => 'Dra. Patricia Medicina', 'role' => 'DOCTOR', 'especialidad' => 'Medicina Familiar']
];

// Obtener médico seleccionado
$medicoId = $_GET['medico_id'] ?? 101;
$medico = $medicos[$medicoId] ?? $medicos[101];

echo "<!DOCTYPE html>";
echo "<html lang='es'>";
echo "<head>";
echo "<meta charset='UTF-8'>";
echo "<meta name='viewport' content='width=device-width, initial-scale=1.0'>";
echo "<title>Panel Médico - " . htmlspecialchars($medico['nombre']) . "</title>";
echo "<style>
    body { 
        font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; 
        margin: 0; 
        padding: 20px; 
        background: #f5f5f5; 
        color: #333;
    }
    .header { 
        background: #2c3e50; 
        color: white; 
        padding: 20px; 
        border-radius: 8px; 
        margin-bottom: 20px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    }
    .header h1 { margin: 0; font-size: 24px; }
    .header p { margin: 10px 0 0 0; opacity: 0.9; }
    .card { 
        background: white; 
        padding: 25px; 
        border-radius: 8px; 
        margin-bottom: 20px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    }
    .selector { 
        background: #3498db; 
        color: white; 
        padding: 15px; 
        border-radius: 8px; 
        margin-bottom: 20px;
    }
    .selector select { 
        padding: 8px 12px; 
        border-radius: 4px; 
        border: none; 
        font-size: 14px;
        min-width: 250px;
    }
    .citas-list { margin-top: 20px; }
    .cita-item { 
        border: 1px solid #e1e5e9; 
        border-radius: 6px; 
        padding: 16px; 
        margin-bottom: 12px;
        background: #fafbfc;
        transition: all 0.2s;
    }
    .cita-item:hover { 
        border-color: #3498db; 
        box-shadow: 0 2px 8px rgba(52, 152, 219, 0.1);
    }
    .cita-header { 
        display: flex; 
        justify-content: space-between; 
        align-items: center; 
        margin-bottom: 10px;
    }
    .cita-paciente { font-weight: 600; font-size: 16px; color: #2c3e50; }
    .cita-fecha { 
        background: #e8f5e8; 
        color: #27ae60; 
        padding: 4px 12px; 
        border-radius: 12px; 
        font-size: 13px;
        font-weight: 500;
    }
    .cita-info { color: #666; font-size: 14px; line-height: 1.4; }
    .cita-motivo { 
        background: #f8f9fa; 
        padding: 10px; 
        border-radius: 4px; 
        margin-top: 10px;
        font-style: italic;
    }
    .stats { 
        display: grid; 
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); 
        gap: 15px; 
        margin-bottom: 20px;
    }
    .stat-card { 
        background: white; 
        padding: 20px; 
        border-radius: 8px; 
        text-align: center;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    }
    .stat-number { font-size: 28px; font-weight: bold; color: #3498db; }
    .stat-label { font-size: 14px; color: #666; margin-top: 5px; }
    .no-citas { 
        text-align: center; 
        padding: 40px; 
        color: #666;
        background: #f8f9fa;
        border-radius: 8px;
        border: 2px dashed #ddd;
    }
    .refresh-btn {
        background: #27ae60;
        color: white;
        padding: 10px 20px;
        border: none;
        border-radius: 6px;
        cursor: pointer;
        font-size: 14px;
        margin-left: 15px;
    }
    .refresh-btn:hover { background: #229954; }
</style>";
echo "</head>";
echo "<body>";

echo "<div class='header'>";
echo "<h1>🏥 Panel Médico - Sistema de Gestión Médica</h1>";
echo "<p>Panel de control para especialistas</p>";
echo "</div>";

echo "<div class='selector'>";
echo "<label>👨‍⚕️ <strong>Médico Activo:</strong></label> ";
echo "<select onchange=\"window.location='?medico_id=' + this.value\">";
foreach ($medicos as $id => $med) {
    $selected = $id == $medicoId ? 'selected' : '';
    echo "<option value='$id' $selected>{$med['nombre']} ({$med['especialidad']})</option>";
}
echo "</select>";
echo "<button class='refresh-btn' onclick='location.reload()'>🔄 Actualizar</button>";
echo "</div>";

echo "<div class='card'>";
echo "<h2>📋 {$medico['nombre']}</h2>";
echo "<p><strong>Especialidad:</strong> {$medico['especialidad']}</p>";
echo "<p><strong>Rol:</strong> {$medico['role']}</p>";

try {
    // Obtener citas asignadas a este médico
    $stmt = $pdo->prepare("
        SELECT c.*, u.nombre as paciente_nombre, u.email as paciente_email
        FROM citas c
        LEFT JOIN usuarios u ON c.usuario_id = u.id
        WHERE c.medico_id = ?
        ORDER BY c.fecha_cita ASC
    ");
    $stmt->execute([$medicoId]);
    $citas = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Estadísticas
    $totalCitas = count($citas);
    $citasPendientes = count(array_filter($citas, function($c) { return $c['estado'] === 'pendiente'; }));
    $citasHoy = count(array_filter($citas, function($c) { return date('Y-m-d', strtotime($c['fecha_cita'])) === date('Y-m-d'); }));
    
    echo "<div class='stats'>";
    echo "<div class='stat-card'>";
    echo "<div class='stat-number'>$totalCitas</div>";
    echo "<div class='stat-label'>Total Citas</div>";
    echo "</div>";
    echo "<div class='stat-card'>";
    echo "<div class='stat-number'>$citasPendientes</div>";
    echo "<div class='stat-label'>Pendientes</div>";
    echo "</div>";
    echo "<div class='stat-card'>";
    echo "<div class='stat-number'>$citasHoy</div>";
    echo "<div class='stat-label'>Hoy</div>";
    echo "</div>";
    echo "</div>";
    
    echo "<h3>📅 Citas Asignadas</h3>";
    
    if (empty($citas)) {
        echo "<div class='no-citas'>";
        echo "<h4>📋 No hay citas asignadas</h4>";
        echo "<p>Este médico no tiene citas programadas en el sistema.</p>";
        echo "<p><small>Las citas aparecerán aquí cuando los pacientes las agenden desde la app de diabetes.</small></p>";
        echo "</div>";
    } else {
        echo "<div class='citas-list'>";
        foreach ($citas as $cita) {
            $fechaCita = new DateTime($cita['fecha_cita']);
            $fechaFormateada = $fechaCita->format('d/m/Y H:i');
            $fechaRelativa = $fechaCita < new DateTime() ? 'Pasada' : 
                           ($fechaCita->format('Y-m-d') === date('Y-m-d') ? 'Hoy' : 'Próxima');
            
            echo "<div class='cita-item'>";
            echo "<div class='cita-header'>";
            echo "<div class='cita-paciente'>👤 " . htmlspecialchars($cita['paciente_nombre'] ?: 'Paciente #' . $cita['usuario_id']) . "</div>";
            echo "<div class='cita-fecha'>📅 $fechaFormateada ($fechaRelativa)</div>";
            echo "</div>";
            
            echo "<div class='cita-info'>";
            echo "<strong>📋 Especialidad:</strong> " . htmlspecialchars($cita['especialidad']) . "<br>";
            echo "<strong>📧 Email:</strong> " . htmlspecialchars($cita['paciente_email'] ?: 'No disponible') . "<br>";
            echo "<strong>🏷️ Estado:</strong> " . ucfirst($cita['estado']) . "<br>";
            echo "<strong>📝 ID Cita:</strong> #{$cita['id']}";
            echo "</div>";
            
            if (!empty($cita['descripcion'])) {
                echo "<div class='cita-motivo'>";
                echo "<strong>💬 Motivo:</strong> " . htmlspecialchars($cita['descripcion']);
                echo "</div>";
            }
            
            echo "</div>";
        }
        echo "</div>";
    }
    
} catch (Exception $e) {
    echo "<div style='color: red; background: #ffe6e6; padding: 15px; border-radius: 6px;'>";
    echo "<strong>❌ Error:</strong> " . htmlspecialchars($e->getMessage());
    echo "</div>";
}

echo "</div>";

echo "<div class='card'>";
echo "<h3>🔗 Enlaces Útiles</h3>";
echo "<p>";
echo "<a href='views/citas.php' style='color: #3498db; text-decoration: none; margin-right: 20px;'>📝 Agendar Nueva Cita</a>";
echo "<a href='test_todos_especialistas.php' style='color: #3498db; text-decoration: none; margin-right: 20px;'>🧪 Test Sistema</a>";
echo "<a href='api/get_especialistas.php' style='color: #3498db; text-decoration: none;'>📋 API Especialistas</a>";
echo "</p>";
echo "</div>";

echo "<script>";
echo "// Auto-refresh cada 30 segundos";
echo "setTimeout(function() { location.reload(); }, 30000);";
echo "</script>";

echo "</body>";
echo "</html>";
?>