<?php
/**
 * API para consultar pacientes del sistema de gestión médica por rol/especialidad
 * Permite filtrar pacientes según el profesional que los consulta
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
    $rol = $_GET['rol'] ?? '';
    $especialidad = $_GET['especialidad'] ?? '';
    $limit = (int)($_GET['limit'] ?? 50);
    $offset = (int)($_GET['offset'] ?? 0);
    
    if (empty($rol) && empty($especialidad)) {
        http_response_code(400);
        echo json_encode(['error' => 'Se requiere rol o especialidad']);
        exit;
    }
    
    // Conectar a la base de datos del sistema de gestión médica
    $pdo = getRemoteConnection();
    
    if (!$pdo) {
        throw new Exception('No se pudo conectar al sistema de gestión médica');
    }
    
    $whereConditions = [];
    $params = [];
    
    // Filtrar por especialidad si se proporciona
    if (!empty($especialidad)) {
        $whereConditions[] = "c.tipo_cita = ?";
        $params[] = $especialidad;
    }
    
    // Si se proporciona rol, convertir a especialidad
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
    
    // Construir consulta
    $whereClause = !empty($whereConditions) ? 'WHERE ' . implode(' AND ', $whereConditions) : '';
    
    // Consulta principal
    $sql = "
        SELECT DISTINCT
            p.id,
            p.nombre,
            p.email,
            p.telefono,
            p.fecha_creacion,
            p.usuario_id_app,
            COUNT(c.id) as total_citas,
            MAX(c.fecha_hora) as ultima_cita,
            c.tipo_cita as especialidad_principal
        FROM pacientes p
        LEFT JOIN citas c ON p.id = c.paciente_id
        $whereClause
        GROUP BY p.id, p.nombre, p.email, p.telefono, p.fecha_creacion, p.usuario_id_app, c.tipo_cita
        ORDER BY MAX(c.fecha_hora) DESC, p.fecha_creacion DESC
        LIMIT ? OFFSET ?
    ";
    
    $params[] = $limit;
    $params[] = $offset;
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $pacientes = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Contar total para paginación
    $countSql = "
        SELECT COUNT(DISTINCT p.id) as total
        FROM pacientes p
        LEFT JOIN citas c ON p.id = c.paciente_id
        $whereClause
    ";
    
    $countParams = array_slice($params, 0, -2); // Remover limit y offset
    $countStmt = $pdo->prepare($countSql);
    $countStmt->execute($countParams);
    $totalPacientes = $countStmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    // Formatear resultados
    $resultado = [];
    foreach ($pacientes as $paciente) {
        $resultado[] = [
            'id' => (int)$paciente['id'],
            'nombre' => $paciente['nombre'],
            'email' => $paciente['email'],
            'telefono' => $paciente['telefono'] ?: 'No registrado',
            'fecha_registro' => $paciente['fecha_creacion'],
            'usuario_app_id' => $paciente['usuario_id_app'],
            'total_citas' => (int)$paciente['total_citas'],
            'ultima_cita' => $paciente['ultima_cita'],
            'especialidad_principal' => $paciente['especialidad_principal'],
            'origen' => 'App Diabetes'
        ];
    }
    
    echo json_encode([
        'success' => true,
        'pacientes' => $resultado,
        'total' => (int)$totalPacientes,
        'limit' => $limit,
        'offset' => $offset,
        'filtros' => [
            'rol' => $rol,
            'especialidad' => $especialidad
        ]
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'error' => 'Error del servidor: ' . $e->getMessage(),
        'success' => false
    ]);
}
?>