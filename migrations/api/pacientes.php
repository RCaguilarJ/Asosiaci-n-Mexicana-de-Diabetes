<?php
/**
 * API para gestión de pacientes - Integración con sistema médico
 */

require_once '../config/headers.php';
require_once '../includes/db.php';

// Verificar método HTTP
$method = $_SERVER['REQUEST_METHOD'];

try {
    switch ($method) {
        case 'GET':
            handleGetRequest();
            break;
        
        case 'POST':
            handlePostRequest();
            break;
            
        case 'PUT':
            handlePutRequest();
            break;
            
        default:
            sendErrorResponse('Método no permitido', 405);
    }
} catch (Exception $e) {
    sendErrorResponse('Error interno del servidor', 500, $e->getMessage());
}

/**
 * Manejar peticiones GET
 */
function handleGetRequest() {
    global $pdo;
    
    $endpoint = $_GET['endpoint'] ?? '';
    
    switch ($endpoint) {
        case 'pacientes':
            getAllPacientes();
            break;
            
        case 'paciente':
            $id = $_GET['id'] ?? null;
            if (!$id) {
                sendErrorResponse('ID de paciente requerido');
            }
            getPacienteDetalle($id);
            break;
            
        case 'glucosa':
            $pacienteId = $_GET['paciente_id'] ?? null;
            if (!$pacienteId) {
                sendErrorResponse('ID de paciente requerido');
            }
            getRegistrosGlucosa($pacienteId);
            break;
            
        case 'citas':
            $pacienteId = $_GET['paciente_id'] ?? null;
            if ($pacienteId) {
                getCitasPaciente($pacienteId);
            } else {
                getAllCitas();
            }
            break;
            
        default:
            sendErrorResponse('Endpoint no encontrado', 404);
    }
}

/**
 * Obtener todos los pacientes
 */
function getAllPacientes() {
    global $pdo;
    
    $sql = "SELECT 
                u.id,
                u.nombre,
                u.email,
                u.fecha_registro,
                COUNT(DISTINCT rg.id) as total_registros_glucosa,
                COUNT(DISTINCT c.id) as total_citas,
                AVG(rg.nivel_glucosa) as promedio_glucosa,
                MAX(rg.fecha_registro) as ultimo_registro
            FROM usuarios u
            LEFT JOIN registros_glucosa rg ON u.id = rg.usuario_id
            LEFT JOIN citas c ON u.id = c.usuario_id
            WHERE u.id IS NOT NULL
            GROUP BY u.id, u.nombre, u.email, u.fecha_registro
            ORDER BY u.fecha_registro DESC";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $pacientes = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Formatear datos para el sistema médico
    $pacientesFormateados = array_map(function($paciente) {
        return [
            'id' => (int)$paciente['id'],
            'nombre' => $paciente['nombre'],
            'email' => $paciente['email'],
            'fecha_registro' => $paciente['fecha_registro'],
            'estadisticas' => [
                'total_registros_glucosa' => (int)$paciente['total_registros_glucosa'],
                'total_citas' => (int)$paciente['total_citas'],
                'promedio_glucosa' => $paciente['promedio_glucosa'] ? round((float)$paciente['promedio_glucosa'], 2) : null,
                'ultimo_registro' => $paciente['ultimo_registro']
            ]
        ];
    }, $pacientes);
    
    sendJsonResponse($pacientesFormateados, 200, 'Pacientes obtenidos exitosamente');
}

/**
 * Obtener detalle completo de un paciente
 */
function getPacienteDetalle($pacienteId) {
    global $pdo;
    
    // Información básica del paciente
    $sql = "SELECT * FROM usuarios WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$pacienteId]);
    $paciente = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$paciente) {
        sendErrorResponse('Paciente no encontrado', 404);
    }
    
    // Últimos registros de glucosa
    $sqlGlucosa = "SELECT * FROM registros_glucosa 
                   WHERE usuario_id = ? 
                   ORDER BY fecha_registro DESC 
                   LIMIT 10";
    $stmt = $pdo->prepare($sqlGlucosa);
    $stmt->execute([$pacienteId]);
    $registrosGlucosa = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Próximas citas
    $sqlCitas = "SELECT * FROM citas 
                 WHERE usuario_id = ? AND fecha_cita >= CURDATE() 
                 ORDER BY fecha_cita ASC, hora_cita ASC 
                 LIMIT 5";
    $stmt = $pdo->prepare($sqlCitas);
    $stmt->execute([$pacienteId]);
    $proximasCitas = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Estadísticas generales
    $sqlStats = "SELECT 
                    AVG(nivel_glucosa) as promedio_glucosa,
                    MIN(nivel_glucosa) as minimo_glucosa,
                    MAX(nivel_glucosa) as maximo_glucosa,
                    COUNT(*) as total_registros
                 FROM registros_glucosa 
                 WHERE usuario_id = ? AND fecha_registro >= DATE_SUB(NOW(), INTERVAL 30 DAY)";
    $stmt = $pdo->prepare($sqlStats);
    $stmt->execute([$pacienteId]);
    $estadisticas = $stmt->fetch(PDO::FETCH_ASSOC);
    
    $detalleCompleto = [
        'paciente' => [
            'id' => (int)$paciente['id'],
            'nombre' => $paciente['nombre'],
            'email' => $paciente['email'],
            'fecha_registro' => $paciente['fecha_registro']
        ],
        'registros_glucosa_recientes' => array_map(function($registro) {
            return [
                'id' => (int)$registro['id'],
                'nivel_glucosa' => (float)$registro['nivel_glucosa'],
                'fecha_registro' => $registro['fecha_registro'],
                'notas' => $registro['notas'] ?? null
            ];
        }, $registrosGlucosa),
        'proximas_citas' => array_map(function($cita) {
            return [
                'id' => (int)$cita['id'],
                'fecha_cita' => $cita['fecha_cita'],
                'hora_cita' => $cita['hora_cita'],
                'especialidad' => $cita['especialidad'],
                'estado' => $cita['estado'] ?? 'programada'
            ];
        }, $proximasCitas),
        'estadisticas_30_dias' => [
            'promedio_glucosa' => $estadisticas['promedio_glucosa'] ? round((float)$estadisticas['promedio_glucosa'], 2) : null,
            'minimo_glucosa' => $estadisticas['minimo_glucosa'] ? (float)$estadisticas['minimo_glucosa'] : null,
            'maximo_glucosa' => $estadisticas['maximo_glucosa'] ? (float)$estadisticas['maximo_glucosa'] : null,
            'total_registros' => (int)$estadisticas['total_registros']
        ]
    ];
    
    sendJsonResponse($detalleCompleto, 200, 'Detalle del paciente obtenido exitosamente');
}

/**
 * Obtener registros de glucosa de un paciente
 */
function getRegistrosGlucosa($pacienteId) {
    global $pdo;
    
    $limite = $_GET['limite'] ?? 30;
    $fechaInicio = $_GET['fecha_inicio'] ?? null;
    $fechaFin = $_GET['fecha_fin'] ?? null;
    
    $sql = "SELECT * FROM registros_glucosa WHERE usuario_id = ?";
    $params = [$pacienteId];
    
    if ($fechaInicio) {
        $sql .= " AND fecha_registro >= ?";
        $params[] = $fechaInicio;
    }
    
    if ($fechaFin) {
        $sql .= " AND fecha_registro <= ?";
        $params[] = $fechaFin;
    }
    
    $sql .= " ORDER BY fecha_registro DESC LIMIT ?";
    $params[] = (int)$limite;
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $registros = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $registrosFormateados = array_map(function($registro) {
        return [
            'id' => (int)$registro['id'],
            'nivel_glucosa' => (float)$registro['nivel_glucosa'],
            'fecha_registro' => $registro['fecha_registro'],
            'notas' => $registro['notas'] ?? null,
            'estado' => clasificarNivelGlucosa((float)$registro['nivel_glucosa'])
        ];
    }, $registros);
    
    sendJsonResponse($registrosFormateados, 200, 'Registros de glucosa obtenidos exitosamente');
}

/**
 * Obtener citas de un paciente o todas las citas
 */
function getCitasPaciente($pacienteId) {
    global $pdo;
    
    $sql = "SELECT c.*, u.nombre as paciente_nombre, u.email as paciente_email
            FROM citas c 
            INNER JOIN usuarios u ON c.usuario_id = u.id 
            WHERE c.usuario_id = ?
            ORDER BY c.fecha_cita DESC, c.hora_cita DESC";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$pacienteId]);
    $citas = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $citasFormateadas = array_map(function($cita) {
        return [
            'id' => (int)$cita['id'],
            'paciente' => [
                'id' => (int)$cita['usuario_id'],
                'nombre' => $cita['paciente_nombre'],
                'email' => $cita['paciente_email']
            ],
            'fecha_cita' => $cita['fecha_cita'],
            'hora_cita' => $cita['hora_cita'],
            'especialidad' => $cita['especialidad'],
            'estado' => $cita['estado'] ?? 'programada',
            'fecha_registro' => $cita['fecha_registro'] ?? null
        ];
    }, $citas);
    
    sendJsonResponse($citasFormateadas, 200, 'Citas obtenidas exitosamente');
}

/**
 * Obtener todas las citas
 */
function getAllCitas() {
    global $pdo;
    
    $sql = "SELECT c.*, u.nombre as paciente_nombre, u.email as paciente_email
            FROM citas c 
            INNER JOIN usuarios u ON c.usuario_id = u.id 
            ORDER BY c.fecha_cita DESC, c.hora_cita DESC
            LIMIT 50";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $citas = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $citasFormateadas = array_map(function($cita) {
        return [
            'id' => (int)$cita['id'],
            'paciente' => [
                'id' => (int)$cita['usuario_id'],
                'nombre' => $cita['paciente_nombre'],
                'email' => $cita['paciente_email']
            ],
            'fecha_cita' => $cita['fecha_cita'],
            'hora_cita' => $cita['hora_cita'],
            'especialidad' => $cita['especialidad'],
            'estado' => $cita['estado'] ?? 'programada',
            'fecha_registro' => $cita['fecha_registro'] ?? null
        ];
    }, $citas);
    
    sendJsonResponse($citasFormateadas, 200, 'Todas las citas obtenidas exitosamente');
}

/**
 * Clasificar nivel de glucosa
 */
function clasificarNivelGlucosa($nivel) {
    if ($nivel < 70) return 'bajo';
    if ($nivel > 180) return 'alto';
    return 'normal';
}
?>