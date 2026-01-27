<?php
// integration_php/api_create_cita.php
// Endpoint ejemplo para crear cita desde el frontend o desde la otra plataforma PHP.
// IMPORTANTE: Ajusta la conexión según tu `includes/db.php` real (PDO o mysqli).

// DEBUG temporary: write raw received body
$raw = file_get_contents('php://input');
file_put_contents(__DIR__.'/debug_raw.txt', $raw, FILE_APPEND);
require_once __DIR__ . '/../includes/db.php';
header('Content-Type: application/json; charset=utf-8');

// Temporary debug logging: write method, headers and raw body to request_debug.log
try {
  $debugPath = __DIR__ . '/request_debug.log';
  $reqMethod = $_SERVER['REQUEST_METHOD'] ?? 'UNKNOWN';
  $allHeaders = function_exists('getallheaders') ? getallheaders() : [];
  $rawBody = file_get_contents('php://input');
  $log = "\n---- " . date('c') . " ----\n";
  $log .= "METHOD: " . $reqMethod . "\n";
  $log .= "HEADERS:\n" . print_r($allHeaders, true) . "\n";
  $log .= "RAW_BODY (first 200 chars):\n" . substr($rawBody, 0, 200) . "\n";
  file_put_contents($debugPath, $log, FILE_APPEND | LOCK_EX);
} catch (Exception $e) {
  // ignore logging errors
}

// Try to decode JSON body. If that fails, try alternative field names and form-encoded bodies.
$raw = file_get_contents('php://input');
$input = json_decode($raw, true);
if (!$input || !is_array($input)) {
  // Try to parse as form-encoded (eg. from a HTML form or incorrect client)
  parse_str($raw, $inputParsed);
  if (!empty($inputParsed)) {
    $input = $inputParsed;
  }
}

if (!$input || !is_array($input) || count($input) === 0) {
  http_response_code(400);
  echo json_encode(['error' => 'JSON inválido o cuerpo vacío', 'received_raw' => substr($raw,0,200)]);
  exit;
}

// Campos esperados (ajusta nombres según tu frontend):
// pacienteId, fechaHora, motivo, medicoId, notas, especialidad
$pacienteId = $input['pacienteId'] ?? $input['paciente_id'] ?? null;
$fechaHora = $input['fechaHora'] ?? $input['fecha_hora'] ?? null;
$motivo = $input['motivo'] ?? '';
$medicoId = $input['medicoId'] ?? null;
$notas = $input['notas'] ?? '';
$especialidad = strtoupper(trim($input['especialidad'] ?? $input['especialidad_name'] ?? 'GENERAL'));

if (!$pacienteId || !$fechaHora) {
  http_response_code(422);
  echo json_encode(['error' => 'Faltan datos requeridos']);
  exit;
}

try {
  // Usamos PDO ($db) si está disponible. Ajusta si tu includes/db.php usa mysqli ($conn) u otro nombre.
  if (!empty($pdo) && $pdo instanceof PDO) {
    $stmt = $pdo->prepare("INSERT INTO citas (usuario_id, fecha_cita, hora_cita, especialidad, estado, fecha_registro) VALUES (?, ?, ?, ?, 'pendiente', NOW())");
    $fechaArray = explode(' ', $fechaHora);
    $fecha = $fechaArray[0];
    $hora = isset($fechaArray[1]) ? $fechaArray[1] : '00:00:00';
    $stmt->execute([$pacienteId, $fecha, $hora, $especialidad]);
    $citaId = $pdo->lastInsertId();

    // Mapear especialidad -> rol objetivo
    $map = [
      'GENERAL' => 'DOCTOR',
      'ENDOCRINOLOGIA' => 'ENDOCRINOLOGO',
      'NUTRICION' => 'NUTRIOLOGO',
      'PODOLOGIA' => 'PODOLOGO',
      'PSICOLOGIA' => 'PSICOLOGO'
    ];
    $targetRole = $map[$especialidad] ?? 'DOCTOR';
    $mensaje = "Nueva cita (ID {$citaId}) para especialidad {$especialidad} - Fecha: {$fecha} {$hora}";

    // Crear notificación solo si existe la tabla notifications
    try {
      $nstmt = $pdo->prepare("INSERT INTO notifications (titulo, mensaje, tipo, rol_destino, referencia_tipo, referencia_id, leido, creado_en) VALUES (?, ?, 'cita', ?, 'cita', ?, 0, NOW())");
      $nstmt->execute([$mensaje, $mensaje, $targetRole, $citaId]);
    } catch (Exception $notifEx) {
      error_log('Warning: no se pudo crear notificación: ' . $notifEx->getMessage());
    }

    // --- Intentar escribir copia en la BD central `sistema_gestion_medica` ---
    try {
      $dsn2 = 'mysql:host=127.0.0.1;dbname=sistema_gestion_medica;charset=utf8mb4';
      $user2 = 'root'; // ajustar si tu instalación usa otro usuario
      $pass2 = '';     // ajustar si tu root tiene contraseña
      $db2 = getRemoteConnection();
      if (!$db2) {
        throw new Exception('No se pudo conectar a la BD remota');
      }

      // Insertar en tabla `cita` (sintaxis para esa BD). Ajustar nombres de columnas si difieren.
      $stmt2 = $db2->prepare("INSERT INTO citas (paciente_id, medico_id, fecha_hora, tipo_cita, motivo, estado, fecha_creacion, origen) VALUES (?, ?, ?, ?, ?, ?, NOW(), 'app_diabetes')");
      $stmt2->execute([$pacienteId, $medicoId, $fechaHora, $especialidad, $motivo, 'pendiente']);
      $citaId2 = $db2->lastInsertId();

      $mensaje2 = "Nueva cita (ID {$citaId2}) para especialidad {$especialidad} - Fecha: {$fechaHora}";
      try {
        $n2 = $db2->prepare("INSERT INTO notifications (titulo, mensaje, tipo, rol_destino, referencia_tipo, referencia_id, leido, creado_en) VALUES (?, ?, 'cita', ?, 'cita', ?, 0, NOW())");
        $n2->execute([$mensaje2, $mensaje2, $targetRole, $citaId2]);
      } catch (Exception $notifEx2) {
        error_log('Warning: no se pudo crear notificación remota: ' . $notifEx2->getMessage());
      }
    } catch (Exception $e) {
      error_log('Warning: no se pudo insertar copia en sistema_gestion_medica: ' . $e->getMessage());
    }

    echo json_encode(['success' => true, 'citaId' => $citaId]);
    exit;
  }

  // Fallback para mysqli (si tu includes/db.php define $conn)
  if (!empty($conn)) {
    $fechaArray = explode(' ', $fechaHora);
    $fecha = $conn->real_escape_string($fechaArray[0]);
    $hora = $conn->real_escape_string(isset($fechaArray[1]) ? $fechaArray[1] : '00:00:00');
    
    $sql = "INSERT INTO citas (usuario_id, fecha_cita, hora_cita, especialidad, estado, fecha_registro) VALUES ('" . $conn->real_escape_string($pacienteId) . "', '" . $fecha . "', '" . $hora . "', '" . $conn->real_escape_string($especialidad) . "', 'pendiente', NOW())";
    if ($conn->query($sql) === TRUE) {
      $citaId = (is_object($conn) && property_exists($conn, 'insert_id')) ? $conn->insert_id : null;
      $map = [
        'GENERAL' => 'DOCTOR',
        'ENDOCRINOLOGIA' => 'ENDOCRINOLOGO',
        'NUTRICION' => 'NUTRIOLOGO',
        'PODOLOGIA' => 'PODOLOGO',
        'PSICOLOGIA' => 'PSICOLOGO'
      ];
      $targetRole = $map[$especialidad] ?? 'DOCTOR';
      $mensaje = "Nueva cita (ID {$citaId}) para especialidad {$especialidad} - Fecha: {$fecha} {$hora}";
      try {
        $ins = "INSERT INTO notifications (titulo, mensaje, tipo, rol_destino, referencia_tipo, referencia_id, leido, creado_en) VALUES ('" . $conn->real_escape_string($mensaje) . "', '" . $conn->real_escape_string($mensaje) . "', 'cita', '" . $conn->real_escape_string($targetRole) . "', 'cita', {$citaId}, 0, NOW())";
        $conn->query($ins);
      } catch (Exception $notifEx) {
        error_log('Warning: no se pudo crear notificación mysqli: ' . $notifEx->getMessage());
      }

      // --- Intentar escribir copia en la BD central `sistema_gestion_medica` usando mysqli ---
      try {
        $db2 = new mysqli('127.0.0.1', 'root', '', 'sistema_gestion_medica');
        if ($db2->connect_errno) throw new Exception('MySQL connect error: ' . $db2->connect_error);
        $sql2 = sprintf("INSERT INTO citas (paciente_id, medico_id, fecha_hora, tipo_cita, motivo, estado, fecha_creacion, origen) VALUES ('%s','%s','%s','%s','%s','pendiente', NOW(), 'app_diabetes')",
          $db2->real_escape_string($pacienteId), $db2->real_escape_string($medicoId), $db2->real_escape_string($fechaHora), $db2->real_escape_string($especialidad), $db2->real_escape_string($motivo)
        );
        $db2->query($sql2);
        $citaId2 = $db2->insert_id;
        $mensaje2 = "Nueva cita (ID {$citaId2}) para especialidad {$especialidad} - Fecha: {$fechaHora}";
        $ins2 = sprintf("INSERT INTO notifications (titulo, mensaje, tipo, rol_destino, referencia_tipo, referencia_id, leido, creado_en) VALUES ('%s','%s','cita','%s','cita',%d,0,NOW())",
          $db2->real_escape_string($mensaje2), $db2->real_escape_string($mensaje2), $db2->real_escape_string($targetRole), intval($citaId2)
        );
        $db2->query($ins2);
        $db2->close();
      } catch (Exception $ex) {
        error_log('Warning: no se pudo insertar copia (mysqli) en sistema_gestion_medica: ' . $ex->getMessage());
      }

      echo json_encode(['success' => true, 'citaId' => $citaId]);
      exit;
    } else {
      $errorMsg = is_object($conn) && property_exists($conn, 'error') ? $conn->error : 'desconocido';
      throw new Exception("Error al insertar cita: {$errorMsg}");
    }
  }

  throw new Exception('Conexión a base de datos no encontrada. Revisa includes/db.php');
} catch (Exception $e) {
  http_response_code(500);
  echo json_encode(['error' => $e->getMessage()]);
}

