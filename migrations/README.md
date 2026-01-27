# Migraciones de Base de Datos

Este directorio contiene scripts de migración para la base de datos del sistema de la Asociación Mexicana de Diabetes.

## 📋 Propósito

Las migraciones son scripts que modifican la estructura de la base de datos de forma controlada y reproducible. Cada migración puede:
- Crear nuevas tablas
- Agregar o modificar columnas
- Crear índices
- Modificar datos existentes

## 🚀 Cómo ejecutar las migraciones

### Opción 1: Ejecutar directamente desde el navegador

1. Navega a la URL de la migración:
   ```
   http://localhost/migrations/[nombre-archivo].php
   ```

2. Ejemplo:
   ```
   http://localhost/migrations/create_sync_queue_table.php
   ```

3. El script mostrará:
   - Estado de la migración (tabla existe o fue creada)
   - Estructura de la tabla
   - Mensajes de éxito o error

### Opción 2: Ejecutar desde línea de comandos

```bash
php migrations/create_sync_queue_table.php
```

## 📝 Migraciones disponibles

### `create_sync_queue_table.php`

**Propósito:** Crea la tabla `sync_queue` para registrar operaciones de sincronización con el Sistema de Gestión Médica.

**Qué hace:**
- Verifica si la tabla `sync_queue` existe
- Si no existe, la crea con la estructura completa:
  - `id`: Identificador único autoincremental
  - `operacion`: Tipo de operación (ej: crear_cita, obtener_especialistas)
  - `estado`: Estado de la operación (pendiente, completado, error)
  - `referencia_id`: ID de referencia de la operación
  - `error_mensaje`: Mensaje de error si la operación falla
  - `datos_json`: Datos adicionales en formato JSON
  - `fecha_creacion`: Timestamp de creación
  - `fecha_actualizacion`: Timestamp de última actualización
- Crea índices para optimizar consultas:
  - `idx_operacion`: Índice en columna operacion
  - `idx_estado`: Índice en columna estado
  - `idx_fecha`: Índice en columna fecha_creacion

**Cuándo ejecutarla:**
- Al configurar el sistema por primera vez
- Si el sistema reporta errores de tabla `sync_queue` no encontrada
- Después de restaurar una base de datos sin esta tabla

**Es idempotente:** ✅ Sí - Puede ejecutarse múltiples veces sin causar errores. Si la tabla ya existe, solo mostrará su estructura actual.

## ⚠️ Recomendaciones de uso

### Antes de ejecutar una migración

1. **Hacer respaldo de la base de datos:**
   ```bash
   mysqldump -u root -p sistema_gestion_medica > backup_$(date +%Y%m%d_%H%M%S).sql
   ```

2. **Verificar las credenciales de base de datos:**
   - Asegurarse de que el archivo `.env` o las variables de entorno estén correctamente configuradas
   - Verificar que el usuario de base de datos tenga permisos de CREATE TABLE y ALTER TABLE

3. **Ejecutar en ambiente de desarrollo primero:**
   - Probar la migración en un ambiente local o de desarrollo
   - Verificar que todo funcione correctamente
   - Solo entonces ejecutar en producción

### Durante la ejecución

1. **No interrumpir el proceso:**
   - Dejar que la migración se complete
   - No cerrar el navegador o terminal mientras se ejecuta

2. **Revisar los mensajes:**
   - Leer cuidadosamente los mensajes de éxito o error
   - Verificar que la estructura creada sea la esperada

### Después de ejecutar

1. **Verificar la creación:**
   - Revisar que la tabla se haya creado correctamente
   - Verificar que los índices estén presentes
   - Probar la funcionalidad que depende de la tabla

2. **Probar la aplicación:**
   - Ejecutar pruebas de funcionalidad
   - Verificar que no haya errores en los logs
   - Asegurarse de que las citas se guarden correctamente

## 🔧 Solución de problemas

### Error: "Access denied"
- Verificar las credenciales en `.env` o variables de entorno
- Asegurarse de que el usuario tenga permisos CREATE TABLE

### Error: "Table already exists"
- La migración ya fue ejecutada
- Verificar la estructura actual de la tabla
- No es necesario ejecutar nuevamente

### Error: "Cannot connect to database"
- Verificar que MySQL esté corriendo
- Verificar host, puerto y credenciales
- Revisar el archivo `includes/db.php`

### La tabla no se creó
- Revisar los logs de error
- Verificar permisos del usuario de base de datos
- Intentar ejecutar el SQL manualmente

## 📚 Recursos adicionales

- **Documentación de PDO:** https://www.php.net/manual/es/book.pdo.php
- **Guía de MySQL:** https://dev.mysql.com/doc/

## 🔒 Seguridad

- **Nunca** ejecutar migraciones de fuentes no confiables
- **Siempre** revisar el código de la migración antes de ejecutarla
- **Mantener** respaldos de la base de datos
- **No** compartir credenciales de base de datos

## 📞 Soporte

Si tienes problemas con las migraciones:
1. Revisar los logs de error en el servidor
2. Consultar esta documentación
3. Contactar al equipo de desarrollo
