<?php
session_start();
require 'includes/db.php';

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
    $tipo_cita = $_POST['tipo_cita']; // Ej: General, Nutrición...
    $descripcion = trim($_POST['descripcion']);
    
    // Combinar fecha y hora para formato DATETIME (YYYY-MM-DD HH:MM:SS)
    $fechaHora = $fecha . ' ' . $hora . ':00';

    try {
        // ---------------------------------------------------------
        // 1. GUARDAR EN LOCAL (Web Asociación)
        // ---------------------------------------------------------
        $pdo->beginTransaction();

        $stmt = $pdo->prepare("INSERT INTO citas (usuario_id, fecha_cita, tipo_cita, descripcion, estado) VALUES (?, ?, ?, ?, 'pendiente')");
        $stmt->execute([$usuario_id_local, $fechaHora, $tipo_cita, $descripcion]);
        
        $pdo->commit();

        // ---------------------------------------------------------
        // 2. GUARDAR EN REMOTO (Sistema Médico)
        // ---------------------------------------------------------
        $pdoRemote = getRemoteConnection();

        if ($pdoRemote) {
            try {
                // A. Buscar el ID del paciente en el sistema remoto usando su email
                // (Porque el ID local "1" puede ser el ID "50" en el sistema remoto)
                $stmtBusqueda = $pdoRemote->prepare("SELECT id FROM pacientes WHERE email = :email LIMIT 1");
                $stmtBusqueda->execute([':email' => $email_usuario]);
                $pacienteRemoto = $stmtBusqueda->fetch(PDO::FETCH_ASSOC);

                if ($pacienteRemoto) {
                    $idPacienteRemoto = $pacienteRemoto['id'];
                    
                    // B. Definir un Médico por defecto
                    // En tu sistema remoto, el ID 14 es el "Doctor". Usaremos ese por defecto.
                    // Si tu formulario web permitiera elegir doctor, usaríamos esa variable.
                    $idMedicoDefault = 14; 
                    
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
                        ':motivo' => $tipo_cita,         // Usamos el tipo de cita como motivo corto
                        ':notas' => $descripcion,        // La descripción va en notas
                        ':pacienteId' => $idPacienteRemoto,
                        ':medicoId' => $idMedicoDefault,
                        ':creado' => $fechaActual,
                        ':actualizado' => $fechaActual
                    ]);
                } else {
                    // El usuario existe en web pero no en sistema médico (quizás se registró antes de la integración)
                    // Opcional: Podrías intentar crearlo aquí, pero por seguridad solo logueamos el error.
                    error_log("No se encontró al paciente $email_usuario en el sistema remoto al agendar cita.");
                }

            } catch (Exception $eRemote) {
                // Si falla la conexión remota, no le decimos al usuario para no asustarlo,
                // ya que su cita sí se guardó en local.
                error_log("Error sincronizando cita remota: " . $eRemote->getMessage());
            }
        }

        // Redirigir con éxito
        header("Location: citas.php?mensaje=registrado");
        exit;

    } catch (Exception $e) {
        if ($pdo->inTransaction()) {
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