-- Agregar columna medico_id para guardar ID real del especialista del Sistema de Gestión Médica
ALTER TABLE citas ADD COLUMN medico_id INT NULL AFTER especialidad;

-- Agregar columna opcional para CURP del paciente (opcional pero recomendado)
ALTER TABLE citas ADD COLUMN paciente_curp VARCHAR(18) NULL AFTER usuario_id;

-- Agregar índice para optimizar consultas por médico
ALTER TABLE citas ADD INDEX idx_medico_fecha (medico_id, fecha_cita);

-- Agregar índice para CURP si se usa
ALTER TABLE citas ADD INDEX idx_paciente_curp (paciente_curp);

-- Verificar la nueva estructura
DESCRIBE citas;