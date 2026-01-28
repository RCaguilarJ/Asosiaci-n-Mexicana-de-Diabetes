<?php
/**
 * API para consultar citas de pacientes del sistema de gestión médica
 * Permite ver el historial de citas de un paciente específico
 */

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

require_once '../includes/sync_helper.php';

// Verificar método
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['error' => 'Método no permitido']);
    exit;
}

try {
    // Obtener parámetros
    $pacienteId = (int)($_GET['paciente_id'] ?? 0);
    $rol = $_GET['rol'] ?? '';
    $limit = (int)($_GET['limit'] ?? 20);
    $offset = (int)($_GET['offset'] ?? 0);
    
    if ($pacienteId <= 0) {
        http_response_code(400);
        echo json_encode(['error' => 'ID de paciente requerido']);
        exit;
    }
    
    // Conectar a la base de datos del sistema de gestión médica
    $pdo = getRemoteConnection();
    
    if (!$pdo) {
        throw new Exception('No se pudo conectar al sistema de gestión médica');
    }
    
    // Primero verificar que el paciente existe
    $stmtPaciente = $pdo->prepare("SELECT * FROM pacientes WHERE id = ?");
    $stmtPaciente->execute([$pacienteId]);
    $paciente = $stmtPaciente->fetch(PDO::FETCH_ASSOC);
    
    if (!$paciente) {
        http_response_code(404);
        echo json_encode(['error' => 'Paciente no encontrado']);
        exit;
    }
    
    $whereConditions = ["c.paciente_id = ?"];
    $params = [$pacienteId];
    
    // Filtrar por rol si se especifica
    if (!empty($rol)) {
        $especialidadPorRol = [
            'NUTRI' => 'Nutrición',
            'ENDOCRINO' => 'Endocrinología',
            'PODOLOGO' => 'Podología',
            'PSICOLOGO' => 'Psicología',
            'DOCTOR' => ['Medicina General', 'Consulta General']
        ];
        
        if (isset($especialidadPorRol[$rol])) {
            if (is_array($especialidadPorRol[$rol])) {
                $placeholders = str_repeat('?,', count($especialidadPorRol[$rol]) - 1) . '?';
                $whereConditions[] = "c.tipo_cita IN ($placeholders)";
                $params = array_merge($params, $especialidadPorRol[$rol]);
            } else {
                $whereConditions[] = "c.tipo_cita = ?";
                $params[] = $especialidadPorRol[$rol];
            }
        }
    }
    
    $whereClause = implode(' AND ', $whereConditions);
    
    // Consulta de citas
    $sql = "
        SELECT 
            c.id,
            c.fecha_hora,
            c.tipo_cita,
            c.motivo,
            c.estado,
            c.observaciones,
            c.fecha_creacion,
            p.nombre as paciente_nombre,
            p.email as paciente_email
        FROM citas c
        INNER JOIN pacientes p ON c.paciente_id = p.id
        WHERE $whereClause
        ORDER BY c.fecha_hora DESC
        LIMIT ? OFFSET ?
    ";
    
    $params[] = $limit;
    $params[] = $offset;
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $citas = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Contar total de citas
    $countSql = "SELECT COUNT(*) as total FROM citas c WHERE $whereClause";
    $countParams = array_slice($params, 0, -2); // Remover limit y offset
    $countStmt = $pdo->prepare($countSql);
    $countStmt->execute($countParams);
    $totalCitas = $countStmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    // Formatear resultados
    $citasFormateadas = [];
    foreach ($citas as $cita) {
        $citasFormateadas[] = [
            'id' => (int)$cita['id'],
            'fecha_hora' => $cita['fecha_hora'],
            'especialidad' => $cita['tipo_cita'],
            'motivo' => $cita['motivo'],
            'estado' => $cita['estado'],
            'observaciones' => $cita['observaciones'] ?: '',
            'fecha_creacion' => $cita['fecha_creacion']
        ];
    }
    
    echo json_encode([
        'success' => true,
        'paciente' => [
            'id' => (int)$paciente['id'],
            'nombre' => $paciente['nombre'],
            'email' => $paciente['email'],
            'telefono' => $paciente['telefono'] ?: 'No registrado',
            'fecha_registro' => $paciente['fecha_creacion'],
            'usuario_app_id' => $paciente['usuario_id_app']
        ],
        'citas' => $citasFormateadas,
        'total_citas' => (int)$totalCitas,
        'limit' => $limit,
        'offset' => $offset,
        'filtro_rol' => $rol
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'error' => 'Error del servidor: ' . $e->getMessage(),
        'success' => false
    ]);
}
?>