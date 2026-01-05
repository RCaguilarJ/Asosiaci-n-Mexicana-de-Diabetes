<?php
// guardar_cita.php
session_start();
require 'includes/db.php';

// 1. Verificar si el usuario está logueado (NO INVITADO)
if (!isset($_SESSION['usuario_id'])) {
    if (isset($_SESSION['es_invitado']) && $_SESSION['es_invitado']) {
        echo json_encode(['success' => false, 'message' => 'Los invitados no pueden agendar citas. Por favor, inicia sesión o crea una cuenta.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Debes iniciar sesión para agendar una cita.']);
    }
    exit;
}

// 2. Recibir los datos (JSON)
$input = json_decode(file_get_contents('php://input'), true);

if ($input) {
    try {
        // Datos del usuario y del formulario
        $usuario_id = $_SESSION['usuario_id'];
        $nombre = $input['nombre'];
        $email = $input['email'];
        $telefono = $input['telefono'];
        $especialidad = $input['especialidad'];
        $fecha = $input['fecha'];
        $hora = $input['hora'];
        $notas = $input['notas'];

        // 3. Insertar en la base de datos
        $stmt = $pdo->prepare("INSERT INTO citas (usuario_id, nombre_paciente, email, telefono, especialidad, fecha_cita, hora_cita, notas) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        
        if ($stmt->execute([$usuario_id, $nombre, $email, $telefono, $especialidad, $fecha, $hora, $notas])) {
            echo json_encode(['success' => true, 'message' => 'Cita agendada correctamente.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error al guardar la cita en la base de datos.']);
        }

    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Error del servidor: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'No se recibieron datos.']);
}
?>