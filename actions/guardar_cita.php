<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Incluir conexión a la base de datos
require '../includes/db.php';
// require '../includes/sync_helper.php'; // Comentado temporalmente
// require '../includes/historial_citas.php'; // Comentado temporalmente
require '../includes/api_sistema_gestion.php';

// Establecer header para respuesta JSON
header('Content-Type: application/json');

// Configurar manejo de errores para API JSON
ini_set('log_errors', 1);
ini_set('display_errors', 1); // Habilitar para debug temporal
error_reporting(E_ALL);

// Log de inicio
error_log("=== INICIO guardar_cita.php === " . date('Y-m-d H:i:s'));

// Verificar que el usuario esté logueado
if (!isset($_SESSION['usuario_id'])) {
    http_response_code(401);
    echo "No autorizado";
    exit;
}

// Verificar que sea una petición POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo "Método no permitido";
    exit;
}

try {
    error_log("Iniciando procesamiento de cita");
    
    // Obtener datos del formulario
    $usuario_id = $_SESSION['usuario_id'];
    $nombre = trim($_POST['nombre'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $telefono = trim($_POST['telefono'] ?? '');
    $especialidad = trim($_POST['especialidad'] ?? '');
    $medico_id = trim($_POST['medico_id'] ?? '');
    $fecha = trim($_POST['fecha'] ?? '');
    $hora = trim($_POST['hora'] ?? '');
    $descripcion = trim($_POST['descripcion'] ?? '');
    
    error_log("Datos recibidos: usuario_id=$usuario_id, medico_id=$medico_id, especialidad=$especialidad");
    
    // Validaciones básicas
    $errores = [];
    
    if (empty($nombre)) {
        $errores[] = "El nombre es requerido";
    }
    
    if (empty($telefono)) {
        $errores[] = "El teléfono es requerido";
    }
    
    if (empty($especialidad)) {
        $errores[] = "La especialidad es requerida";
    }
    
    if (empty($medico_id)) {
        $errores[] = "Debe seleccionar un especialista";
    }
    
    if (empty($fecha)) {
        $errores[] = "La fecha es requerida";
    }
    
    if (empty($hora)) {
        $errores[] = "La hora es requerida";
    }
    
    // Validar que la fecha no sea en el pasado
    if (!empty($fecha)) {
        $fechaCita = new DateTime($fecha);
        $hoy = new DateTime();
        $hoy->setTime(0, 0, 0);
        
        if ($fechaCita < $hoy) {
            $errores[] = "No se pueden agendar citas en fechas pasadas";
        }
    }
    
    // Si hay errores, retornarlos
    if (!empty($errores)) {
        error_log("Errores de validación: " . implode(', ', $errores));
        http_response_code(400);
        echo json_encode(['error' => implode(', ', $errores)]);
        exit;
    }
    
    error_log("Validaciones pasadas, verificando conflictos de citas");
    
    // Verificar si ya existe una cita en esa fecha y especialidad
    $fechaHora = $fecha . ' ' . $hora . ':00';
    $stmtVerificar = $pdo->prepare("
        SELECT COUNT(*) as total 
        FROM citas 
        WHERE fecha_cita = ? 
        AND especialidad = ?
        AND estado != 'cancelada'
    ");
    $stmtVerificar->execute([$fechaHora, $especialidad]);
    $conflicto = $stmtVerificar->fetch();
    
    if ($conflicto['total'] > 3) {
        error_log("Límite de citas alcanzado: " . $conflicto['total']);
        http_response_code(409);
        echo json_encode(['error' => 'Límite de citas alcanzado para esa fecha y especialidad']);
        exit;
    }
    
    error_log("Verificando estructura de tabla citas");
    
    // Verificar que las columnas existan antes de insertar
    try {
        $columnas = $pdo->query("DESCRIBE citas")->fetchAll(PDO::FETCH_COLUMN);
        error_log("Columnas en tabla citas: " . implode(', ', $columnas));
        
        $columnasNecesarias = ['usuario_id', 'medico_id', 'nombre', 'email', 'telefono', 'fecha_cita', 'especialidad', 'descripcion', 'estado'];
        $columnasFaltantes = array_diff($columnasNecesarias, $columnas);
        
        if (!empty($columnasFaltantes)) {
            error_log("Columnas faltantes: " . implode(', ', $columnasFaltantes));
            throw new Exception("Faltan columnas en tabla citas: " . implode(', ', $columnasFaltantes));
        }
        
    } catch (Exception $e) {
        error_log("Error verificando estructura: " . $e->getMessage());
        throw $e;
    }
    
    // Insertar la cita en la base de datos (usando fecha_cita como DATETIME)
    $fechaHora = $fecha . ' ' . $hora . ':00';
    error_log("Insertando cita con fecha: $fechaHora");
    
    $stmt = $pdo->prepare("
        INSERT INTO citas (
            usuario_id, 
            medico_id,
            nombre,
            email,
            telefono,
            fecha_cita, 
            especialidad, 
            descripcion, 
            estado 
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'pendiente')
    ");
    
    $parametros = [
        $usuario_id,
        $medico_id,
        $nombre,
        $email,
        $telefono,
        $fechaHora,
        $especialidad,
        $descripcion
    ];
    
    error_log("Parámetros de inserción: " . json_encode($parametros));
    
    $resultado = $stmt->execute($parametros);
    
    if (!$resultado) {
        $errorInfo = $stmt->errorInfo();
        error_log("Error en inserción SQL: " . json_encode($errorInfo));
        throw new Exception("Error SQL: " . $errorInfo[2]);
    }
    
    if ($resultado) {
        $citaId = $pdo->lastInsertId();
        error_log("Cita insertada exitosamente con ID: $citaId");
        
        // Limpiar historial rotativo (mantener solo últimas 5 citas)
        try {
            error_log("Ejecutando limpieza de historial");
            // Función simple de limpieza de historial
            $stmtCleanup = $pdo->prepare("
                DELETE FROM citas 
                WHERE usuario_id = ? 
                AND estado = 'completada' 
                AND id NOT IN (
                    SELECT id FROM (
                        SELECT id FROM citas 
                        WHERE usuario_id = ? 
                        AND estado = 'completada'
                        ORDER BY fecha_registro DESC 
                        LIMIT 5
                    ) as t
                )
            ");
            $stmtCleanup->execute([$usuario_id, $usuario_id]);
            error_log("Limpieza de historial completada");
        } catch (Exception $e) {
            error_log("Error en limpieza (ignorado): " . $e->getMessage());
            // Ignorar errores de limpieza
        }
        
        // Sincronizar con el sistema de gestión médica usando API HMAC
        try {
            $api = new ApiSistemaGestionHelper();
            
            // Preparar datos para la API del sistema médico
            $datosCitaAPI = [
                'pacienteId' => $usuario_id,
                'medicoId' => $medico_id,
                'fechaHora' => date('c', strtotime($fecha . ' ' . $hora)), // ISO 8601
                'motivo' => $especialidad . ': ' . $descripcion,
                'notas' => json_encode([
                    'app_diabetes' => true,
                    'nombre_paciente' => $nombre,
                    'email_paciente' => $email,
                    'telefono_paciente' => $telefono,
                    'cita_local_id' => $citaId
                ])
            ];
            
            $resultadoAPI = $api->crearCita($datosCitaAPI);
            
            if ($resultadoAPI['success']) {
                $mensaje = 'Cita agendada exitosamente y sincronizada con el sistema médico';
                $citaRemotaId = $resultadoAPI['cita']['id'] ?? null;
                $medicoAsignado = $resultadoAPI['cita']['medico'] ?? null;
                
                http_response_code(200);
                echo json_encode([
                    'success' => true, 
                    'mensaje' => $mensaje,
                    'citaId' => $citaId,
                    'citaRemotaId' => $citaRemotaId,
                    'medico' => $medicoAsignado,
                    'confirmacion' => "Su cita ha sido confirmada con " . ($medicoAsignado['nombre'] ?? 'el especialista')
                ]);
            } else {
                // Error en API pero cita local guardada
                http_response_code(200);
                echo json_encode([
                    'success' => true, 
                    'mensaje' => 'Cita agendada localmente. ' . $resultadoAPI['error'],
                    'citaId' => $citaId,
                    'warning' => 'Sincronización pendiente'
                ]);
            }
        } catch (Exception $e) {
            // Si falla la API remota, al menos tenemos la cita local
            http_response_code(200);
            echo json_encode([
                'success' => true, 
                'mensaje' => 'Cita agendada localmente (sin sincronización remota)',
                'citaId' => $citaId
            ]);
        }
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Error al guardar la cita en la base de datos']);
    }
    
} catch (Exception $e) {
    error_log("=== ERROR en guardar_cita.php ===");
    error_log("Error: " . $e->getMessage());
    error_log("File: " . $e->getFile());
    error_log("Line: " . $e->getLine());
    error_log("Stack trace: " . $e->getTraceAsString());
    error_log("POST data: " . json_encode($_POST));
    error_log("SESSION data: " . json_encode($_SESSION));
    
    http_response_code(500);
    echo json_encode([
        'error' => 'Error interno del servidor',
        'debug_message' => $e->getMessage(),
        'debug_line' => $e->getLine(),
        'debug_file' => basename($e->getFile()),
        'timestamp' => date('Y-m-d H:i:s')
    ]);
}
?>