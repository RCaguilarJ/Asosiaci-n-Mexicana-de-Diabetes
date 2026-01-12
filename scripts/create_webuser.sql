-- scripts/create_webuser.sql
-- Reemplaza 'strong_password_here' por una contraseña fuerte y única.

CREATE USER 'webuser'@'localhost' IDENTIFIED BY 'strong_password_here';
GRANT SELECT, INSERT, UPDATE, DELETE ON diabetes_db.* TO 'webuser'@'localhost';
FLUSH PRIVILEGES;

-- Opcional: limitar hosts (ej: '10.0.0.%') o usar autenticación más estricta.
-- Después de crear el usuario, configura las variables de entorno:
-- setx DB_USER "webuser"
-- setx DB_PASS "<la_contraseña_fuerte>"
-- setx DB_HOST "localhost"
-- setx DB_NAME "diabetes_db"
