<?php
// sync_helper.php - Funciones para sincronización con sistema-gestion-medica

// Incluir db.php para usar la función getRemoteConnection() existente
require_once __DIR__ . '/db.php';

function mapearEspecialidadARol($especialidad) {
    $mapeo = [
        'Nutrición' => 'NUTRI',
        'Endocrinología' => 'ENDOCRINO', 
        'Podología' => 'PODOLOGO',
        'Psicología' => 'PSICOLOGO',
        'Medicina General' => 'DOCTOR'
    ];
    
    return $mapeo[$especialidad] ?? 'DOCTOR';
}

/**
 * Valida que existe un médico con el ID especificado antes de sincronizar
 */
function validarMedicoExiste($medicoId) {
    if (!$medicoId) {
        error_log("SYNC ERROR: medico_id faltante para sincronización. Se requiere seleccionar un especialista específico.");
        return false;
    }
    
    $remotePdo = getRemoteConnection();
    if (!$remotePdo) return false;
    
    try {
        $stmt = $remotePdo->prepare("SELECT id, nombre, email, rol FROM users WHERE id = ? AND estado = 'activo'");
        $stmt->execute([$medicoId]);
        $medico = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$medico) {
            error_log("SYNC ERROR: Médico con ID $medicoId no encontrado o inactivo. Se requiere asignar un especialista válido.");
            return false;
        }
        
        error_log("SYNC SUCCESS: Médico válido encontrado - ID: {$medico['id']}, Nombre: {$medico['nombre']}, Rol: {$medico['rol']}");
        return $medico;
        
    } catch (Exception $e) {
        error_log("SYNC ERROR validando médico: " . $e->getMessage());
        return false;
    }
}

function sincronizarPacienteEnSistemaGestion($usuarioData) {
    $remotePdo = getRemoteConnection();
    if (!$remotePdo) return false;
    
    try {
        // Verificar si ya existe
        $checkStmt = $remotePdo->prepare("SELECT id FROM pacientes WHERE email = ?");
        $checkStmt->execute([$usuarioData['email']]);
        
        if ($checkStmt->rowCount() > 0) {
            return true; // Ya existe
        }
        
        // Insertar nuevo paciente
        $insertStmt = $remotePdo->prepare("
            INSERT INTO pacientes (
                nombre, 
                apellidos, 
                email, 
                telefono, 
                fecha_nacimiento,
                sexo,
                usuario_web_id,
                fecha_registro,
                estado
            ) VALUES (?, ?, ?, ?, ?, ?, ?, NOW(), 'activo')
        ");
        
        $nombreCompleto = explode(' ', $usuarioData['nombre'], 2);
        $nombre = $nombreCompleto[0];
        $apellidos = isset($nombreCompleto[1]) ? $nombreCompleto[1] : '';
        
        $insertStmt->execute([
            $nombre,
            $apellidos,
            $usuarioData['email'],
            $usuarioData['telefono'] ?? '',
            $usuarioData['fecha_nacimiento'] ?? null,
            $usuarioData['sexo'] ?? 'no_especificado',
            $usuarioData['usuario_id']
        ]);
        
        return $remotePdo->lastInsertId();
        
    } catch (Exception $e) {
        error_log("Error sincronizando paciente: " . $e->getMessage());
        return false;
    }
}

function sincronizarCitaEnSistemaGestion($citaData, $usuarioData) {
    $remotePdo = getRemoteConnection();
    if (!$remotePdo) {
        error_log("SYNC ERROR: No se pudo conectar a la base de datos remota");
        return false;
    }
    
    try {
        // Validar que se proporcione medico_id específico
        if (!isset($citaData['medico_id']) || empty($citaData['medico_id'])) {
            error_log("SYNC ERROR: medico_id requerido para sincronización. Cita local ID: " . ($citaData['cita_local_id'] ?? 'N/A'));
            return false;
        }
        
        // Validar que el médico existe y está activo
        $medico = validarMedicoExiste($citaData['medico_id']);
        if (!$medico) {
            return false; // Error ya registrado en validarMedicoExiste
        }
        
        // Asegurar que el paciente existe
        $pacienteId = sincronizarPacienteEnSistemaGestion($usuarioData);
        if (!$pacienteId) {
            error_log("SYNC ERROR: No se pudo sincronizar el paciente");
            return false;
        }
        
        // Insertar cita con médico específico
        $citaStmt = $remotePdo->prepare("
            INSERT INTO citas (
                paciente_id,
                medico_id,
                fecha_hora,
                tipo_cita,
                motivo,
                estado,
                fecha_creacion,
                origen
            ) VALUES (?, ?, ?, ?, ?, ?, NOW(), 'app_diabetes')
        ");
        
        $resultado = $citaStmt->execute([
            $pacienteId,
            $citaData['medico_id'],  // Usar el ID específico del médico
            $citaData['fecha_hora'],
            $citaData['especialidad'],
            $citaData['motivo'],
            $citaData['estado']
        ]);
        
        if ($resultado) {
            $citaId = $remotePdo->lastInsertId();
            error_log("SYNC SUCCESS: Cita sincronizada - ID: $citaId, Médico: {$medico['id']} ({$medico['nombre']}), Paciente: $pacienteId");
            return $citaId;
        } else {
            error_log("SYNC ERROR: Falló la inserción de la cita");
            return false;
        }
        
    } catch (Exception $e) {
        error_log("Error sincronizando cita: " . $e->getMessage());
        return false;
    }
}

function obtenerPacientesPorRol($rol) {
    $remotePdo = getRemoteConnection();
    if (!$remotePdo) return [];
    
    try {
        $stmt = $remotePdo->prepare("
            SELECT 
                p.id,
                p.nombre,
                p.apellidos,
                p.email,
                p.telefono,
                c.fecha_hora,
                c.especialidad,
                c.estado,
                c.descripcion
            FROM pacientes p
            INNER JOIN cita c ON p.id = c.paciente_id
            LEFT JOIN users u ON c.medico_id = u.id
            WHERE (u.rol = ? OR ? = 'ADMIN')
            AND c.estado = 'Pendiente'
            AND c.fecha_hora >= NOW()
            ORDER BY c.fecha_hora ASC
        ");
        
        $stmt->execute([$rol, $rol]);
        return $stmt->fetchAll();
        
    } catch (Exception $e) {
        error_log("Error obteniendo pacientes: " . $e->getMessage());
        return [];
    }
}
?>