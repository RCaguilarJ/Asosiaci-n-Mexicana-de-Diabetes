<?php
/**
 * Funciones para gestionar historial rotativo de citas
 * Mantiene solo las últimas 5 citas por usuario, rotando automáticamente
 */

require_once '../includes/db.php';

/**
 * Obtiene el historial de citas del usuario (máximo 5)
 */
function obtenerHistorialCitas($usuario_id) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("
            SELECT id, fecha_cita, especialidad, descripcion, estado, 
                   DATE_FORMAT(fecha_cita, '%Y-%m-%d') as fecha_solo,
                   DATE_FORMAT(fecha_cita, '%H:%i') as hora_solo,
                   DATE_FORMAT(fecha_registro, '%d/%m/%Y %H:%i') as fecha_registro_formatted
            FROM citas 
            WHERE usuario_id = ? 
            ORDER BY fecha_cita DESC 
            LIMIT 5
        ");
        
        $stmt->execute([$usuario_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
        
    } catch (Exception $e) {
        error_log("Error obteniendo historial de citas: " . $e->getMessage());
        return [];
    }
}

/**
 * Limpia citas antiguas manteniendo solo las últimas 5
 * Se ejecuta automáticamente después de cada nueva cita
 */
function limpiarHistorialRotativo($usuario_id) {
    global $pdo;
    
    try {
        // Contar total de citas del usuario
        $countStmt = $pdo->prepare("SELECT COUNT(*) as total FROM citas WHERE usuario_id = ?");
        $countStmt->execute([$usuario_id]);
        $totalCitas = $countStmt->fetch()['total'];
        
        // Si hay más de 5 citas, eliminar las más antiguas
        if ($totalCitas > 5) {
            $deleteStmt = $pdo->prepare("
                DELETE FROM citas 
                WHERE usuario_id = ? 
                AND id NOT IN (
                    SELECT id FROM (
                        SELECT id FROM citas 
                        WHERE usuario_id = ? 
                        ORDER BY fecha_cita DESC 
                        LIMIT 5
                    ) as keep_recent
                )
            ");
            
            $deletedRows = $deleteStmt->execute([$usuario_id, $usuario_id]);
            
            if ($deletedRows) {
                error_log("Historial rotativo: eliminadas " . ($totalCitas - 5) . " citas antiguas para usuario $usuario_id");
            }
        }
        
        return true;
        
    } catch (Exception $e) {
        error_log("Error en limpieza rotativa de historial: " . $e->getMessage());
        return false;
    }
}

/**
 * Obtiene estadísticas del historial
 */
function obtenerEstadisticasHistorial($usuario_id) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("
            SELECT 
                COUNT(*) as total_citas,
                COUNT(CASE WHEN estado = 'completada' THEN 1 END) as completadas,
                COUNT(CASE WHEN estado = 'pendiente' THEN 1 END) as pendientes,
                COUNT(CASE WHEN estado = 'cancelada' THEN 1 END) as canceladas,
                MIN(fecha_cita) as primera_cita,
                MAX(fecha_cita) as ultima_cita
            FROM citas 
            WHERE usuario_id = ?
        ");
        
        $stmt->execute([$usuario_id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
        
    } catch (Exception $e) {
        error_log("Error obteniendo estadísticas de historial: " . $e->getMessage());
        return [
            'total_citas' => 0,
            'completadas' => 0,
            'pendientes' => 0,
            'canceladas' => 0,
            'primera_cita' => null,
            'ultima_cita' => null
        ];
    }
}

/**
 * Formatea una fecha para mostrar de forma amigable
 */
function formatearFechaAmigable($fecha) {
    if (!$fecha) return 'No disponible';
    
    $fechaObj = new DateTime($fecha);
    $hoy = new DateTime();
    $ayer = new DateTime('-1 day');
    $manana = new DateTime('+1 day');
    
    if ($fechaObj->format('Y-m-d') === $hoy->format('Y-m-d')) {
        return 'Hoy ' . $fechaObj->format('H:i');
    } elseif ($fechaObj->format('Y-m-d') === $ayer->format('Y-m-d')) {
        return 'Ayer ' . $fechaObj->format('H:i');
    } elseif ($fechaObj->format('Y-m-d') === $manana->format('Y-m-d')) {
        return 'Mañana ' . $fechaObj->format('H:i');
    } else {
        return $fechaObj->format('d/m/Y H:i');
    }
}
?>