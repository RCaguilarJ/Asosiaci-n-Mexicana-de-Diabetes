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
  if (!empty($db) && $db instanceof PDO) {
    $stmt = $db->prepare("INSERT INTO citas (paciente_id, fecha_hora, motivo, medico_id, notas, especialidad, estado, created_at) VALUES (?, ?, ?, ?, ?, ?, 'Pendiente', NOW())");
    $stmt->execute([$pacienteId, $fechaHora, $motivo, $medicoId, $notas, $especialidad]);
    $citaId = $db->lastInsertId();

    // Mapear especialidad -> rol objetivo
    $map = [
      'GENERAL' => 'DOCTOR',
      'ENDOCRINOLOGIA' => 'ENDOCRINOLOGO',
      'NUTRICION' => 'NUTRIOLOGO',
      'PODOLOGIA' => 'PODOLOGO',
      'PSICOLOGIA' => 'PSICOLOGO'
    ];
    $targetRole = $map[$especialidad] ?? 'DOCTOR';
    $mensaje = "Nueva cita (ID {$citaId}) para especialidad {$especialidad} - Fecha: {$fechaHora}";

    $nstmt = $db->prepare("INSERT INTO notifications (titulo, mensaje, tipo, rol_destino, referencia_tipo, referencia_id, leido, creado_en) VALUES (?, ?, 'cita', ?, 'cita', ?, 0, NOW())");
    $nstmt->execute([$mensaje, $mensaje, $targetRole, $citaId]);

    // --- Intentar escribir copia en la BD central `sistema_gestion_medica` ---
    try {
      $dsn2 = 'mysql:host=127.0.0.1;dbname=sistema_gestion_medica;charset=utf8mb4';
      $user2 = 'root'; // ajustar si tu instalación usa otro usuario
      $pass2 = '';     // ajustar si tu root tiene contraseña
      $db2 = new PDO($dsn2, $user2, $pass2, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

      // Insertar en tabla `cita` (sintaxis para esa BD). Ajustar nombres de columnas si difieren.
      $stmt2 = $db2->prepare("INSERT INTO cita (paciente_id, fecha_hora, motivo, medico_id, notas, especialidad, estado, created_at) VALUES (?, ?, ?, ?, ?, ?, 'Pendiente', NOW())");
      $stmt2->execute([$pacienteId, $fechaHora, $motivo, $medicoId, $notas, $especialidad]);
      $citaId2 = $db2->lastInsertId();

      $mensaje2 = "Nueva cita (ID {$citaId2}) para especialidad {$especialidad} - Fecha: {$fechaHora}";
      $n2 = $db2->prepare("INSERT INTO notifications (titulo, mensaje, tipo, rol_destino, referencia_tipo, referencia_id, leido, creado_en) VALUES (?, ?, 'cita', ?, 'cita', ?, 0, NOW())");
      $n2->execute([$mensaje2, $mensaje2, $targetRole, $citaId2]);
    } catch (Exception $e) {
      error_log('Warning: no se pudo insertar copia en sistema_gestion_medica: ' . $e->getMessage());
    }

    echo json_encode(['success' => true, 'citaId' => $citaId]);
    exit;
  }

  // Fallback para mysqli (si tu includes/db.php define $conn)
  if (!empty($conn)) {
    $sql = "INSERT INTO citas (paciente_id, fecha_hora, motivo, medico_id, notas, especialidad, estado, created_at) VALUES ('" . $conn->real_escape_string($pacienteId) . "', '" . $conn->real_escape_string($fechaHora) . "', '" . $conn->real_escape_string($motivo) . "', '" . $conn->real_escape_string($medicoId) . "', '" . $conn->real_escape_string($notas) . "', '" . $conn->real_escape_string($especialidad) . "', 'Pendiente', NOW())";
    if ($conn->query($sql) === TRUE) {
      $citaId = $conn->insert_id;
      $map = [
        'GENERAL' => 'DOCTOR',
        'ENDOCRINOLOGIA' => 'ENDOCRINOLOGO',
        'NUTRICION' => 'NUTRIOLOGO',
        'PODOLOGIA' => 'PODOLOGO',
        'PSICOLOGIA' => 'PSICOLOGO'
      ];
      $targetRole = $map[$especialidad] ?? 'DOCTOR';
      $mensaje = "Nueva cita (ID {$citaId}) para especialidad {$especialidad} - Fecha: {$fechaHora}";
      $ins = "INSERT INTO notifications (titulo, mensaje, tipo, rol_destino, referencia_tipo, referencia_id, leido, creado_en) VALUES ('" . $conn->real_escape_string($mensaje) . "', '" . $conn->real_escape_string($mensaje) . "', 'cita', '" . $conn->real_escape_string($targetRole) . "', 'cita', {$citaId}, 0, NOW())";
      $conn->query($ins);

      // --- Intentar escribir copia en la BD central `sistema_gestion_medica` usando mysqli ---
      try {
        $db2 = new mysqli('127.0.0.1', 'root', '', 'sistema_gestion_medica');
        if ($db2->connect_errno) throw new Exception('MySQL connect error: ' . $db2->connect_error);
        $sql2 = sprintf("INSERT INTO cita (paciente_id, fecha_hora, motivo, medico_id, notas, especialidad, estado, created_at) VALUES ('%s','%s','%s','%s','%s','%s','Pendiente', NOW())",
          $db2->real_escape_string($pacienteId), $db2->real_escape_string($fechaHora), $db2->real_escape_string($motivo), $db2->real_escape_string($medicoId), $db2->real_escape_string($notas), $db2->real_escape_string($especialidad)
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
      throw new Exception('Error al insertar cita: ' . $conn->error);
    }
  }

  throw new Exception('Conexión a base de datos no encontrada. Revisa includes/db.php');
} catch (Exception $e) {
  http_response_code(500);
  echo json_encode(['error' => $e->getMessage()]);
}

