-- Script para crear la tabla de citas (compatible con el sistema existente)
CREATE TABLE IF NOT EXISTS citas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    nombre_paciente VARCHAR(100),
    email_paciente VARCHAR(100),
    telefono_paciente VARCHAR(20),
    especialidad VARCHAR(50) NOT NULL,
    fecha_cita DATE NOT NULL,
    hora_cita TIME NOT NULL,
    descripcion TEXT,
    estado ENUM('pendiente', 'confirmada', 'cancelada', 'completada') DEFAULT 'pendiente',
    fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_usuario_fecha (usuario_id, fecha_cita),
    INDEX idx_especialidad_fecha (especialidad, fecha_cita, hora_cita)
);

-- Si ya existe la tabla de usuarios, podemos añadir una foreign key
-- ALTER TABLE citas ADD CONSTRAINT fk_cita_usuario FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE;