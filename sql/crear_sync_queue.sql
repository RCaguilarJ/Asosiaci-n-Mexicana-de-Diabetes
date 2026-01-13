-- Tabla para trazabilidad de sincronización con Sistema Gestión Médica
CREATE TABLE IF NOT EXISTS sync_queue (
    id INT AUTO_INCREMENT PRIMARY KEY,
    operacion VARCHAR(100) NOT NULL,
    estado ENUM('pendiente', 'completado', 'error', 'reintentar') DEFAULT 'pendiente',
    referencia_id INT,
    error_mensaje TEXT,
    datos_json JSON,
    intentos INT DEFAULT 0,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fecha_procesado TIMESTAMP NULL,
    INDEX idx_operacion_estado (operacion, estado),
    INDEX idx_fecha_creacion (fecha_creacion)
);

-- Indices adicionales para optimización
ALTER TABLE sync_queue ADD INDEX idx_referencia (referencia_id);
ALTER TABLE sync_queue ADD INDEX idx_estado_fecha (estado, fecha_creacion);