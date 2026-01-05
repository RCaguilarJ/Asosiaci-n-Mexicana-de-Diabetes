-- Script para verificar/agregar la columna password si falta
-- Ejecuta esto solo si la tabla usuarios no tiene la columna password

-- Agregar la columna password si no existe
ALTER TABLE usuarios ADD COLUMN password VARCHAR(255) NOT NULL DEFAULT '';


-- Crear un usuario de ejemplo (contraseña: test123)
-- INSERT INTO usuarios (nombre, email, password, activo) 
-- VALUES ('Usuario Test', 'test@ejemplo.com', '$2y$10$abcdefghijklmnopqrstuvwxyz1234567890', 1);
