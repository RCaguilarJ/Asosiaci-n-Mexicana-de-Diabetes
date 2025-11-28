<?php
// guardar_calculo.php
session_start();
require 'includes/db.php';

// 1. Verificar si el usuario está logueado
if (!isset($_SESSION['usuario_id'])) {
    echo json_encode(['success' => false, 'message' => 'No hay sesión iniciada']);
    exit;
}

// 2. Recibir los datos (JSON)
$input = json_decode(file_get_contents('php://input'), true);

if ($input) {
    try {
        $usuario_id = $_SESSION['usuario_id'];
        $glucosa = $input['glucosa'];
        $momento = $input['momento'];
        $dosis = $input['dosis'];

        // 3. Insertar en la base de datos
        $stmt = $pdo->prepare("INSERT INTO registros_glucosa (usuario_id, nivel_glucosa, momento_medicion, dosis_insulina) VALUES (?, ?, ?, ?)");
        
        if ($stmt->execute([$usuario_id, $glucosa, $momento, $dosis])) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error al guardar en BD']);
        }

    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Datos no recibidos']);
}
?>