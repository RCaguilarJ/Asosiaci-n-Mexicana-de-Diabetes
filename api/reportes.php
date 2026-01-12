<?php
/**
 * API para reportes y estadísticas médicas
 */

require_once '../config/headers.php';
require_once '../includes/db.php';

$method = $_SERVER['REQUEST_METHOD'];

try {
    switch ($method) {
        case 'GET':
            handleGetRequest();
            break;
            
        default:
            sendErrorResponse('Método no permitido', 405);
    }
} catch (Exception $e) {
    sendErrorResponse('Error interno del servidor', 500, $e->getMessage());
}

function handleGetRequest() {
    $endpoint = $_GET['endpoint'] ?? '';
    
    switch ($endpoint) {
        case 'dashboard':
            getDashboardStats();
            break;
            
        case 'paciente-estadisticas':
            $pacienteId = $_GET['paciente_id'] ?? null;
            if (!$pacienteId) {
                sendErrorResponse('ID de paciente requerido');
            }
            getPacienteEstadisticas($pacienteId);
            break;
            
        case 'tendencias':
            getTendenciasGenerales();
            break;
            
        default:
            sendErrorResponse('Endpoint no encontrado', 404);
    }
}

/**
 * Estadísticas para el dashboard médico
 */
function getDashboardStats() {
    global $pdo;
    
    // Total de pacientes
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM usuarios");
    $totalPacientes = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    // Pacientes activos (con registros en los últimos 30 días)
    $stmt = $pdo->query("SELECT COUNT(DISTINCT usuario_id) as activos 
                        FROM registros_glucosa 
                        WHERE fecha_registro >= DATE_SUB(NOW(), INTERVAL 30 DAY)");
    $pacientesActivos = $stmt->fetch(PDO::FETCH_ASSOC)['activos'];
    
    // Citas programadas
    $stmt = $pdo->query("SELECT COUNT(*) as programadas 
                        FROM citas 
                        WHERE fecha_cita >= CURDATE()");
    $citasProgramadas = $stmt->fetch(PDO::FETCH_ASSOC)['programadas'];
    
    // Registros de glucosa del día
    $stmt = $pdo->query("SELECT COUNT(*) as hoy 
                        FROM registros_glucosa 
                        WHERE DATE(fecha_registro) = CURDATE()");
    $registrosHoy = $stmt->fetch(PDO::FETCH_ASSOC)['hoy'];
    
    // Distribución de niveles de glucosa (últimos 7 días)
    $stmt = $pdo->query("SELECT 
                            COUNT(CASE WHEN nivel_glucosa < 70 THEN 1 END) as bajos,
                            COUNT(CASE WHEN nivel_glucosa BETWEEN 70 AND 180 THEN 1 END) as normales,
                            COUNT(CASE WHEN nivel_glucosa > 180 THEN 1 END) as altos
                        FROM registros_glucosa 
                        WHERE fecha_registro >= DATE_SUB(NOW(), INTERVAL 7 DAY)");
    $distribucionGlucosa = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Pacientes con alertas (niveles extremos en últimos 3 días)
    $stmt = $pdo->query("SELECT DISTINCT u.id, u.nombre, rg.nivel_glucosa, rg.fecha_registro
                        FROM usuarios u
                        INNER JOIN registros_glucosa rg ON u.id = rg.usuario_id
                        WHERE (rg.nivel_glucosa < 60 OR rg.nivel_glucosa > 200)
                        AND rg.fecha_registro >= DATE_SUB(NOW(), INTERVAL 3 DAY)
                        ORDER BY rg.fecha_registro DESC
                        LIMIT 10");
    $alertas = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $dashboard = [
        'resumen' => [
            'total_pacientes' => (int)$totalPacientes,
            'pacientes_activos' => (int)$pacientesActivos,
            'citas_programadas' => (int)$citasProgramadas,
            'registros_hoy' => (int)$registrosHoy
        ],
        'distribucion_glucosa_7_dias' => [
            'bajos' => (int)$distribucionGlucosa['bajos'],
            'normales' => (int)$distribucionGlucosa['normales'],
            'altos' => (int)$distribucionGlucosa['altos']
        ],
        'alertas_recientes' => array_map(function($alerta) {
            return [
                'paciente_id' => (int)$alerta['id'],
                'paciente_nombre' => $alerta['nombre'],
                'nivel_glucosa' => (float)$alerta['nivel_glucosa'],
                'fecha_registro' => $alerta['fecha_registro'],
                'tipo_alerta' => $alerta['nivel_glucosa'] < 60 ? 'hipoglucemia_severa' : 'hiperglucemia_severa'
            ];
        }, $alertas)
    ];
    
    sendJsonResponse($dashboard, 200, 'Estadísticas del dashboard obtenidas exitosamente');
}

/**
 * Estadísticas detalladas de un paciente específico
 */
function getPacienteEstadisticas($pacienteId) {
    global $pdo;
    
    $dias = $_GET['dias'] ?? 30;
    
    // Verificar que el paciente existe
    $stmt = $pdo->prepare("SELECT nombre FROM usuarios WHERE id = ?");
    $stmt->execute([$pacienteId]);
    $paciente = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$paciente) {
        sendErrorResponse('Paciente no encontrado', 404);
    }
    
    // Estadísticas de glucosa por período
    $stmt = $pdo->prepare("SELECT 
                            AVG(nivel_glucosa) as promedio,
                            MIN(nivel_glucosa) as minimo,
                            MAX(nivel_glucosa) as maximo,
                            COUNT(*) as total_registros,
                            COUNT(CASE WHEN nivel_glucosa < 70 THEN 1 END) as episodios_bajos,
                            COUNT(CASE WHEN nivel_glucosa > 180 THEN 1 END) as episodios_altos
                          FROM registros_glucosa 
                          WHERE usuario_id = ? AND fecha_registro >= DATE_SUB(NOW(), INTERVAL ? DAY)");
    $stmt->execute([$pacienteId, $dias]);
    $stats = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Tendencia diaria (últimos X días)
    $stmt = $pdo->prepare("SELECT 
                            DATE(fecha_registro) as fecha,
                            AVG(nivel_glucosa) as promedio_dia,
                            MIN(nivel_glucosa) as min_dia,
                            MAX(nivel_glucosa) as max_dia,
                            COUNT(*) as registros_dia
                          FROM registros_glucosa 
                          WHERE usuario_id = ? AND fecha_registro >= DATE_SUB(NOW(), INTERVAL ? DAY)
                          GROUP BY DATE(fecha_registro)
                          ORDER BY fecha DESC");
    $stmt->execute([$pacienteId, $dias]);
    $tendenciaDiaria = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Distribución por horarios
    $stmt = $pdo->prepare("SELECT 
                            HOUR(fecha_registro) as hora,
                            AVG(nivel_glucosa) as promedio_hora,
                            COUNT(*) as registros_hora
                          FROM registros_glucosa 
                          WHERE usuario_id = ? AND fecha_registro >= DATE_SUB(NOW(), INTERVAL ? DAY)
                          GROUP BY HOUR(fecha_registro)
                          ORDER BY hora");
    $stmt->execute([$pacienteId, $dias]);
    $distribucionHoraria = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $estadisticas = [
        'paciente' => [
            'id' => (int)$pacienteId,
            'nombre' => $paciente['nombre']
        ],
        'periodo_analisis' => [
            'dias' => (int)$dias,
            'fecha_inicio' => date('Y-m-d', strtotime("-{$dias} days")),
            'fecha_fin' => date('Y-m-d')
        ],
        'resumen_glucosa' => [
            'promedio' => $stats['promedio'] ? round((float)$stats['promedio'], 2) : null,
            'minimo' => $stats['minimo'] ? (float)$stats['minimo'] : null,
            'maximo' => $stats['maximo'] ? (float)$stats['maximo'] : null,
            'total_registros' => (int)$stats['total_registros'],
            'episodios_bajos' => (int)$stats['episodios_bajos'],
            'episodios_altos' => (int)$stats['episodios_altos'],
            'porcentaje_tiempo_rango' => $stats['total_registros'] > 0 ? 
                round((($stats['total_registros'] - $stats['episodios_bajos'] - $stats['episodios_altos']) / $stats['total_registros']) * 100, 2) : 0
        ],
        'tendencia_diaria' => array_map(function($dia) {
            return [
                'fecha' => $dia['fecha'],
                'promedio' => round((float)$dia['promedio_dia'], 2),
                'minimo' => (float)$dia['min_dia'],
                'maximo' => (float)$dia['max_dia'],
                'registros' => (int)$dia['registros_dia']
            ];
        }, $tendenciaDiaria),
        'distribucion_horaria' => array_map(function($hora) {
            return [
                'hora' => (int)$hora['hora'],
                'promedio' => round((float)$hora['promedio_hora'], 2),
                'registros' => (int)$hora['registros_hora']
            ];
        }, $distribucionHoraria)
    ];
    
    sendJsonResponse($estadisticas, 200, 'Estadísticas del paciente obtenidas exitosamente');
}

/**
 * Tendencias generales de la población de pacientes
 */
function getTendenciasGenerales() {
    global $pdo;
    
    // Tendencia de registros por mes (últimos 6 meses)
    $stmt = $pdo->query("SELECT 
                            DATE_FORMAT(fecha_registro, '%Y-%m') as mes,
                            COUNT(*) as total_registros,
                            COUNT(DISTINCT usuario_id) as pacientes_activos,
                            AVG(nivel_glucosa) as promedio_glucosa
                        FROM registros_glucosa 
                        WHERE fecha_registro >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
                        GROUP BY DATE_FORMAT(fecha_registro, '%Y-%m')
                        ORDER BY mes DESC");
    $tendenciaMensual = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Especialidades más solicitadas en citas
    $stmt = $pdo->query("SELECT 
                            especialidad,
                            COUNT(*) as total_citas
                        FROM citas 
                        WHERE fecha_cita >= DATE_SUB(NOW(), INTERVAL 3 MONTH)
                        GROUP BY especialidad
                        ORDER BY total_citas DESC
                        LIMIT 10");
    $especialidadesPopulares = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $tendencias = [
        'tendencia_mensual' => array_map(function($mes) {
            return [
                'mes' => $mes['mes'],
                'total_registros' => (int)$mes['total_registros'],
                'pacientes_activos' => (int)$mes['pacientes_activos'],
                'promedio_glucosa' => round((float)$mes['promedio_glucosa'], 2)
            ];
        }, $tendenciaMensual),
        'especialidades_populares' => array_map(function($esp) {
            return [
                'especialidad' => $esp['especialidad'],
                'total_citas' => (int)$esp['total_citas']
            ];
        }, $especialidadesPopulares)
    ];
    
    sendJsonResponse($tendencias, 200, 'Tendencias generales obtenidas exitosamente');
}
?>