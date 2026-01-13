-- SQL para base de datos sistema_gestion_medica
-- Tabla para historial de cálculos de insulina

CREATE TABLE IF NOT EXISTS historial_calculos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    glucosa DECIMAL(5,2) NOT NULL,
    momento ENUM('ayunas', 'antes_comer', 'despues_comer', 'antes_dormir') NOT NULL,
    carbohidratos DECIMAL(5,2) NOT NULL,
    ratio_insulina INT NOT NULL,
    dosis_correccion DECIMAL(4,2) DEFAULT 0,
    dosis_carbohidratos DECIMAL(4,2) NOT NULL,
    dosis_total DECIMAL(4,2) NOT NULL,
    fecha_calculo TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_usuario_fecha (usuario_id, fecha_calculo),
    INDEX idx_momento (momento),
    INDEX idx_glucosa (glucosa)
);

-- También actualizar la tabla citas si es necesario para que coincida
-- (La tabla citas ya existe pero podemos agregar índices si no los tiene)
ALTER TABLE citas ADD INDEX IF NOT EXISTS idx_estado_fecha (estado, fecha_cita);
ALTER TABLE citas ADD INDEX IF NOT EXISTS idx_usuario_estado (usuario_id, estado);

-- Opcional: Tabla para sincronización de datos entre sistemas
CREATE TABLE IF NOT EXISTS sync_queue (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tabla_origen VARCHAR(50) NOT NULL,
    registro_id INT NOT NULL,
    accion ENUM('INSERT', 'UPDATE', 'DELETE') NOT NULL,
    datos JSON,
    procesado BOOLEAN DEFAULT FALSE,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fecha_procesado TIMESTAMP NULL,
    INDEX idx_procesado (procesado, fecha_creacion)
);