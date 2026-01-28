<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Incluir conexión a la base de datos local y el helper de la API
require '../includes/db.php';
require '../includes/api_sistema_gestion.php';

// Establecer header para respuesta JSON
header('Content-Type: application/json');

// Configuración de errores para depuración (puedes desactivarlo en producción)
ini_set('log_errors', 1);
error_reporting(E_ALL);

// 1. VERIFICACIONES DE SEGURIDAD
if (!isset($_SESSION['usuario_id'])) {
    http_response_code(401);
    echo json_encode(['error' => "No autorizado. Inicie sesión nuevamente."]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => "Método no permitido"]);
    exit;
}

try {
    // 2. OBTENER DATOS DEL FORMULARIO
    $usuario_id = $_SESSION['usuario_id'];
    $nombre = trim($_POST['nombre'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $telefono = trim($_POST['telefono'] ?? '');
    $especialidad = trim($_POST['especialidad'] ?? '');
    $medico_id = trim($_POST['medico_id'] ?? '');
    $fecha = trim($_POST['fecha'] ?? '');
    $hora = trim($_POST['hora'] ?? '');
    $descripcion = trim($_POST['descripcion'] ?? '');
    
    // 3. VALIDACIONES BÁSICAS
    $errores = [];
    
    if (empty($nombre)) $errores[] = "El nombre es requerido";
    if (empty($medico_id)) $errores[] = "Debe seleccionar un especialista";
    if (empty($fecha)) $errores[] = "La fecha es requerida";
    if (empty($hora)) $errores[] = "La hora es requerida";
    
    // Validar fecha futura
    if (!empty($fecha)) {
        $fechaCita = new DateTime($fecha);
        $hoy = new DateTime();
        $hoy->setTime(0, 0, 0);
        if ($fechaCita < $hoy) {
            $errores[] = "No se pueden agendar citas en fechas pasadas";
        }
    }
    
    if (!empty($errores)) {
        http_response_code(400);
        echo json_encode(['error' => implode(', ', $errores)]);
        exit;
    }
    
    // 4. VERIFICAR DISPONIBILIDAD LOCAL (Opcional, evita duplicados en BD local)
    $fechaHora = $fecha . ' ' . $hora . ':00';
    $stmtCheck = $pdo->prepare("SELECT id FROM citas WHERE usuario_id = ? AND medico_id = ? AND fecha_cita = ? AND estado != 'cancelada' LIMIT 1");
    $stmtCheck->execute([$usuario_id, $medico_id, $fechaHora]);
    if ($stmtCheck->fetchColumn()) {
        echo json_encode([
            'success' => true,
            'mensaje' => 'Cita registrada exitosamente. Espere a ser confirmada.'
        ]);
        exit;
    }
    
    // 5. INSERTAR CITA EN BASE DE DATOS LOCAL (PHP)
    $stmt = $pdo->prepare("
        INSERT INTO citas (
            usuario_id, medico_id, nombre, email, telefono,
            fecha_cita, especialidad, descripcion, estado 
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'pendiente')
    ");
    
    $resultado = $stmt->execute([
        $usuario_id,
        $medico_id,
        $nombre,
        $email,
        $telefono,
        $fechaHora,
        $especialidad,
        $descripcion
    ]);
    
    if ($resultado) {
        $citaId = $pdo->lastInsertId();
        
        // ---------------------------------------------------------
        // 6. SINCRONIZACIÓN CON EL SISTEMA ADMINISTRATIVO (NODE.JS)
        // ---------------------------------------------------------
        try {
            $api = new ApiSistemaGestionHelper();
            
            // Preparamos los datos EXACTAMENTE como los necesita Node.js
            // para crear el paciente y asignarlo al doctor.
            $datosCitaAPI = [
                // Datos requeridos por la API
                'pacienteId' => $usuario_id,   // ID del usuario
                'medicoId' => $medico_id,      // ID del Doctor
                'fechaHora' => $fechaHora,     // Fecha y hora completa
                'motivo' => $descripcion,      // Motivo de la cita

                // Datos adicionales
                'especialidad' => $especialidad,
                'fecha' => $fecha,
                'hora' => $hora,
                'nombrePaciente' => $nombre,
                'emailPaciente' => $email,
                'telefonoPaciente' => $telefono,
                'curpPaciente' => 'TEMP-' . $usuario_id . '-' . date('ymd'),
                'origen' => 'portal_paciente_php',
                'cita_local_id' => $citaId
            ];
            
            // Enviamos los datos a Node.js
            $resultadoAPI = $api->crearCita($datosCitaAPI);
            
            $mensaje = 'Su cita fue registrada exitosamente';
            
            if ($resultadoAPI['success']) {
                $mensaje .= ' y enviada al especialista.';
                $citaRemotaId = $resultadoAPI['cita']['id'] ?? null;
                $estadoRemoto = strtolower($resultadoAPI['cita']['estado'] ?? '');
                if ($estadoRemoto) {
                    $stmtEstado = $pdo->prepare("UPDATE citas SET estado = ? WHERE id = ?");
                    $stmtEstado->execute([$estadoRemoto, $citaId]);
                }
            } else {
                // Si falla Node.js, avisamos pero no detenemos el proceso local
                $mensaje .= ' (Sincronización pendiente).';
            }
            
            echo json_encode([
                'success' => true, 
                'mensaje' => $mensaje,
                'citaId' => $citaId
            ]);
            
        } catch (Exception $e) {
            // Si la API falla por completo (servidor apagado, etc.)
            error_log("Error al contactar Node.js: " . $e->getMessage());
            echo json_encode([
                'success' => true, 
                'mensaje' => 'Cita guardada localmente.',
                'citaId' => $citaId,
                'warning' => 'No se pudo contactar con el sistema médico.'
            ]);
        }
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Error al guardar la cita en la base de datos local']);
    }
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Error interno del servidor: ' . $e->getMessage()]);
}
