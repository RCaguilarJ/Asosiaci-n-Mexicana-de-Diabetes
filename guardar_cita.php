<?php
session_start();
require 'includes/db.php';

if (!function_exists('resolve_remote_medico_id')) {
    function resolve_remote_medico_id($especialidad) {
        $default = (int)(getenv('REMOTE_DEFAULT_MEDICO_ID') ?: 14);
        $normalized = strtoupper(trim($especialidad ?? ''));

        $mapping = [
            'MEDICINA GENERAL' => getenv('REMOTE_MEDICO_GENERAL_ID'),
            'NUTRICIÓN' => getenv('REMOTE_MEDICO_NUTRICION_ID'),
            'NUTRICION' => getenv('REMOTE_MEDICO_NUTRICION_ID'),
            'ENDOCRINOLOGÍA' => getenv('REMOTE_MEDICO_ENDOCRINOLOGIA_ID'),
            'ENDOCRINOLOGIA' => getenv('REMOTE_MEDICO_ENDOCRINOLOGIA_ID'),
            'PODOLOGÍA' => getenv('REMOTE_MEDICO_PODOLOGIA_ID'),
            'PODOLOGIA' => getenv('REMOTE_MEDICO_PODOLOGIA_ID'),
            'PSICOLOGÍA' => getenv('REMOTE_MEDICO_PSICOLOGIA_ID'),
            'PSICOLOGIA' => getenv('REMOTE_MEDICO_PSICOLOGIA_ID')
        ];

        $candidate = $mapping[$normalized] ?? null;
        if ($candidate === null || $candidate === '') {
            return $default;
        }

        $candidateInt = (int)$candidate;
        return $candidateInt > 0 ? $candidateInt : $default;
    }
}

if (!function_exists('normalize_remote_specialty')) {
    function normalize_remote_specialty($especialidad) {
        $upper = strtoupper(trim($especialidad ?? ''));
        return strtr($upper, [
            'Á' => 'A', 'É' => 'E', 'Í' => 'I', 'Ó' => 'O', 'Ú' => 'U', 'Ü' => 'U',
            'À' => 'A', 'È' => 'E', 'Ì' => 'I', 'Ò' => 'O', 'Ù' => 'U'
        ]);
    }
}

if (!function_exists('resolve_local_specialty_column')) {
    function resolve_local_specialty_column($pdo_local = null, $conn_local = null) {
        static $detected = null;
        if ($detected !== null) {
            return $detected;
        }

        $candidates = ['especialidad', 'tipo_cita', 'especialista'];

        foreach ($candidates as $column) {
            try {
                if ($pdo_local instanceof PDO) {
                    $stmt = $pdo_local->query("SHOW COLUMNS FROM citas LIKE '" . $column . "'");
                    if ($stmt && $stmt->fetch()) {
                        $detected = $column;
                        return $detected;
                    }
                }
            } catch (Exception $e) {
                // ignore and try next candidate
            }

            if ($conn_local && class_exists('mysqli') && $conn_local instanceof mysqli) {
                $result = $conn_local->query("SHOW COLUMNS FROM citas LIKE '" . $column . "'");
                if ($result && $result->fetch_assoc()) {
                    $detected = $column;
                    return $detected;
                }
            }
        }

        $detected = 'especialidad';
        return $detected;
    }
}

// Verificar si el usuario está logueado
if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usuario_id_local = $_SESSION['usuario_id'];
    $email_usuario = $_SESSION['usuario_email']; // Necesitamos el email para buscarlo en el remoto
    
    // Recibir datos del formulario
    $fecha = $_POST['fecha'];
    $hora = $_POST['hora'];
    $especialidad = trim($_POST['especialidad'] ?? ($_POST['tipo_cita'] ?? ''));
    $descripcion = trim($_POST['descripcion']);

    if ($especialidad === '') {
        $especialidad = 'Medicina General';
    }
    $especialidadKey = normalize_remote_specialty($especialidad);
    
    // Combinar fecha y hora para formato DATETIME (YYYY-MM-DD HH:MM:SS)
    $fechaHora = $fecha . ' ' . $hora . ':00';

    try {
        // ---------------------------------------------------------
        // 1. GUARDAR EN LOCAL (Web Asociación)
        // ---------------------------------------------------------
        if (!is_object($pdo)) {
            throw new Exception("Conexión a la base de datos local no disponible.");
        }

        $pdo->beginTransaction();

        $specialtyColumn = resolve_local_specialty_column($pdo, isset($conn) ? $conn : null);
        $sqlInsert = "INSERT INTO citas (usuario_id, fecha_cita, {$specialtyColumn}, descripcion, estado) VALUES (?, ?, ?, ?, 'pendiente')";
        $stmt = $pdo->prepare($sqlInsert);
        $stmt->execute([$usuario_id_local, $fechaHora, $especialidad, $descripcion]);
        
        $pdo->commit();

        // ---------------------------------------------------------
        // 2. SINCRONIZACIÓN REMOTA: preferir API HTTP si REMOTE_API_URL está configurada
        // ---------------------------------------------------------
        $remoteApiBase = getenv('REMOTE_API_URL') ?: null;

        if ($remoteApiBase) {
            require_once __DIR__ . '/includes/remote_api.php';
            try {
                $found = remote_get_patient_by_email($email_usuario);
                if (is_array($found) && !empty($found)) {
                    $idPacienteRemoto = $found['id'] ?? ($found[0]['id'] ?? null);
                }

                if (empty($idPacienteRemoto)) {
                    $localStmt = $pdo->prepare("SELECT nombre, email FROM usuarios WHERE id = ? LIMIT 1");
                    $localStmt->execute([$usuario_id_local]);
                    $localUser = $localStmt->fetch(PDO::FETCH_ASSOC);
                    if ($localUser) {
                        $created = remote_create_patient(['nombre' => $localUser['nombre'], 'email' => $localUser['email']]);
                        if ($created) {
                            $idPacienteRemoto = $created['id'] ?? ($created[0]['id'] ?? null);
                        }
                    }
                }

                if (!empty($idPacienteRemoto)) {
                    $remoteMedicoId = resolve_remote_medico_id($especialidad);
                    $fechaActual = date('Y-m-d H:i:s');
                    $payload = [
                        'fechaHora' => $fechaHora,
                        'motivo' => $especialidad,
                        'notas' => $descripcion,
                        'estado' => 'Pendiente',
                        'pacienteId' => $idPacienteRemoto,
                        'medicoId' => $remoteMedicoId,
                        'especialidad' => $especialidadKey,
                        'createdAt' => $fechaActual,
                        'updatedAt' => $fechaActual
                    ];
                    $createdCita = remote_create_cita($payload);
                    if ($createdCita) {
                        error_log('Cita sincronizada vía API, pacienteId=' . $idPacienteRemoto);
                    } else {
                        error_log('Fallo al crear cita vía API para pacienteId=' . $idPacienteRemoto);
                        require_once __DIR__ . '/includes/sync_queue.php';
                        enqueue_sync('cita', [
                            'fechaHora' => $fechaHora,
                            'motivo' => $especialidad,
                            'notas' => $descripcion,
                            'pacienteEmail' => $email_usuario,
                            'pacienteIdLocal' => $usuario_id_local,
                            'medicoId' => $remoteMedicoId,
                            'especialidad' => $especialidadKey
                        ], $pdo, 'API createCita failed');
                    }
                } else {
                    error_log('No se obtuvo idPacienteRemoto tras intento API; encolando creación de paciente y cita.');
                    require_once __DIR__ . '/includes/sync_queue.php';
                    enqueue_sync('cita', [
                        'fechaHora' => $fechaHora,
                        'motivo' => $especialidad,
                        'notas' => $descripcion,
                        'pacienteEmail' => $email_usuario,
                        'pacienteIdLocal' => $usuario_id_local,
                        'medicoId' => resolve_remote_medico_id($especialidad),
                        'especialidad' => $especialidadKey
                    ], $pdo, 'No paciente id after API attempt');
                }
            } catch (Exception $eApi) {
                error_log('Error sincronizando via API remota: ' . $eApi->getMessage());
                require_once __DIR__ . '/includes/sync_queue.php';
                enqueue_sync('cita', [
                    'fechaHora' => $fechaHora,
                    'motivo' => $especialidad,
                    'notas' => $descripcion,
                    'pacienteEmail' => $email_usuario,
                    'pacienteIdLocal' => $usuario_id_local,
                    'medicoId' => resolve_remote_medico_id($especialidad),
                    'especialidad' => $especialidadKey
                ], $pdo, 'Exception API sync: ' . $eApi->getMessage());
            }
        } else {
            // Fallback: sincronizar por conexión directa a BD remota
            $pdoRemote = getRemoteConnection();

            if ($pdoRemote) {
                try {
                    // A. Buscar el ID del paciente en el sistema remoto usando su email
                    $stmtBusqueda = $pdoRemote->prepare("SELECT id FROM pacientes WHERE email = :email LIMIT 1");
                    $stmtBusqueda->execute([':email' => $email_usuario]);
                    $pacienteRemoto = $stmtBusqueda->fetch(PDO::FETCH_ASSOC);

                    if ($pacienteRemoto) {
                        $idPacienteRemoto = $pacienteRemoto['id'];
                        
                        // B. Definir un Médico por defecto
                        $idMedicoDefault = resolve_remote_medico_id($especialidad);
                        
                        $fechaActual = date('Y-m-d H:i:s');
                        
                        // C. Insertar la cita en la tabla 'cita' del sistema remoto
                        $sqlRemote = "INSERT INTO cita (
                            fechaHora, 
                            motivo, 
                            notas, 
                            estado, 
                            pacienteId, 
                            medicoId, 
                            createdAt, 
                            updatedAt
                        ) VALUES (
                            :fechaHora, 
                            :motivo, 
                            :notas, 
                            'Pendiente', 
                            :pacienteId, 
                            :medicoId, 
                            :creado, 
                            :actualizado
                        )";

                        $stmtRemote = $pdoRemote->prepare($sqlRemote);
                        $stmtRemote->execute([
                            ':fechaHora' => $fechaHora,
                            ':motivo' => $especialidad,
                            ':notas' => $descripcion,
                            ':pacienteId' => $idPacienteRemoto,
                            ':medicoId' => $idMedicoDefault,
                            ':creado' => $fechaActual,
                            ':actualizado' => $fechaActual
                        ]);
                    } else {
                        error_log("No se encontró al paciente $email_usuario en el sistema remoto al agendar cita. Encolando para reintento.");
                        require_once __DIR__ . '/includes/sync_queue.php';
                        enqueue_sync('cita', [
                            'fechaHora' => $fechaHora,
                            'motivo' => $especialidad,
                            'notas' => $descripcion,
                            'pacienteEmail' => $email_usuario,
                            'pacienteIdLocal' => $usuario_id_local,
                            'medicoId' => $idMedicoDefault,
                            'especialidad' => $especialidadKey
                        ], $pdo, 'Paciente no encontrado en DB remota');
                    }

                } catch (Exception $eRemote) {
                    error_log("Error sincronizando cita remota: " . $eRemote->getMessage());
                    require_once __DIR__ . '/includes/sync_queue.php';
                    enqueue_sync('cita', [
                        'fechaHora' => $fechaHora,
                            'motivo' => $especialidad,
                        'notas' => $descripcion,
                        'pacienteEmail' => $email_usuario,
                        'pacienteIdLocal' => $usuario_id_local,
                            'medicoId' => $idMedicoDefault,
                            'especialidad' => $especialidadKey
                    ], $pdo, 'Exception DB sync: ' . $eRemote->getMessage());
                }
            }
        }

        // Redirigir con éxito
        header("Location: citas.php?mensaje=registrado");
        exit;

    } catch (Exception $e) {
        if (isset($pdo) && $pdo instanceof PDO && $pdo->inTransaction()) {
            $pdo->rollBack();
        }
        die("Error al guardar la cita: " . $e->getMessage());
    }
} else {
    // Si intentan entrar directo sin POST
    header("Location: citas.php");
    exit;
}
?>