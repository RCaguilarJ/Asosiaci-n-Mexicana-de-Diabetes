<?php
// actions/guardar_cita.php
session_start();

// Rutas corregidas para encontrar los includes desde la carpeta actions/
require '../includes/db.php'; 
// Si usas funciones externas, ajusta también sus rutas, ejemplo:
// require_once '../includes/functions/remote_api.php'; 

// ... (El resto de tus funciones resolve_remote_medico_id, etc. se quedan igual) ...

if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../views/login.php"); // Corregido
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // ... (Toda tu lógica de validación e inserción SQL se queda igual) ...

    try {
        // ... (Lógica de inserción DB Local) ...
        
        // ... (Lógica de sincronización API/DB Remota) ...
        // Asegúrate de que los require_once dentro del bloque también usen ../
        // ejemplo: require_once '../includes/functions/remote_api.php';

        // REDIRECCIÓN FINAL EXITOSA (Importante)
        // Debe apuntar a la carpeta views
        header("Location: ../views/citas.php?mensaje=registrado");
        exit;

    } catch (Exception $e) {
        if (isset($pdo) && $pdo->inTransaction()) {
            $pdo->rollBack();
        }
        die("Error al guardar la cita: " . $e->getMessage());
    }
} else {
    // Si entran directo
    header("Location: ../views/citas.php");
    exit;
}
?>