# Sistema de Sincronización - App Diabetes ↔ Sistema Gestión Médica

## Descripción

Este sistema permite que los usuarios registrados en la aplicación de diabetes aparezcan automáticamente como pacientes en el sistema de gestión médica, con las citas organizadas por especialidad y rol profesional.

## Arquitectura

### Base de Datos

**App Diabetes (diabetes_db):**
- `usuarios` - Usuarios registrados en la app
- `citas` - Citas locales de la app
- `historial_calculos` - Cálculos realizados en la app

**Sistema Gestión Médica (sistema_gestion_medica):**
- `pacientes` - Pacientes sincronizados desde la app
- `citas` - Citas sincronizadas desde la app
- `usuarios` - Profesionales médicos con roles

### Flujo de Sincronización

1. **Registro de Usuario:**
   - Usuario se registra en app diabetes (`views/login.php`)
   - Automáticamente se crea como paciente en sistema médico
   - Se guarda referencia `usuario_id_app` para linking

2. **Agendado de Cita:**
   - Usuario agenda cita en app (`actions/guardar_cita.php`)
   - Cita se guarda localmente
   - Automáticamente se sincroniza con sistema médico
   - Se mapea especialidad a rol profesional

3. **Consulta por Profesional:**
   - Profesional médico consulta pacientes por su especialidad
   - Solo ve pacientes relevantes a su rol
   - APIs filtran automáticamente por especialidad

## Mapeo de Especialidades a Roles

| Especialidad        | Rol Profesional |
|-------------------|-----------------|
| Nutrición         | NUTRI           |
| Endocrinología    | ENDOCRINO       |
| Podología         | PODOLOGO        |
| Psicología        | PSICOLOGO       |
| Medicina General  | DOCTOR          |
| Consulta General  | DOCTOR          |

## Archivos Principales

### Sincronización
- `includes/sync_helper.php` - Funciones de sincronización principales
- `test_sync.php` - Script de pruebas de sincronización

### APIs
- `api/pacientes_por_rol.php` - Consultar pacientes filtrados por rol
- `api/historial_paciente.php` - Historial de citas de un paciente

### Modificados para Sincronización
- `views/login.php` - Registro de usuarios con sync automático
- `actions/guardar_cita.php` - Agendado de citas con sync automático

## Funciones de Sincronización

### `getRemoteConnection()`
Establece conexión con la base de datos del sistema de gestión médica.

### `sincronizarPacienteEnSistemaGestion($datosUsuario)`
Sincroniza un usuario de la app como paciente en el sistema médico.

**Parámetros:**
```php
[
    'nombre' => 'Nombre del Usuario',
    'email' => 'usuario@email.com',
    'telefono' => '555-1234',
    'usuario_id_app' => 123
]
```

### `sincronizarCitaEnSistemaGestion($datosCita)`
Sincroniza una cita de la app con el sistema médico.

**Parámetros:**
```php
[
    'paciente_id' => 456,
    'fecha_hora' => '2024-01-15 10:30:00',
    'tipo_cita' => 'Nutrición',
    'motivo' => 'Control nutricional',
    'estado' => 'pendiente'
]
```

### `mapearEspecialidadARol($especialidad)`
Convierte una especialidad en el rol profesional correspondiente.

### `obtenerPacientesPorRol($rol)`
Obtiene lista de pacientes visible para un rol específico.

## APIs para el Sistema Médico

### GET /api/pacientes_por_rol.php

Obtiene pacientes filtrados por rol del profesional.

**Parámetros:**
- `rol` - Rol del profesional (NUTRI, ENDOCRINO, etc.)
- `especialidad` - Filtro directo por especialidad (opcional)
- `limit` - Límite de resultados (default: 50)
- `offset` - Offset para paginación (default: 0)

**Ejemplo:**
```
GET /api/pacientes_por_rol.php?rol=NUTRI&limit=20&offset=0
```

**Respuesta:**
```json
{
    "success": true,
    "pacientes": [
        {
            "id": 1,
            "nombre": "Juan Pérez",
            "email": "juan@email.com",
            "telefono": "555-1234",
            "fecha_registro": "2024-01-15 10:00:00",
            "usuario_app_id": 123,
            "total_citas": 3,
            "ultima_cita": "2024-01-20 14:30:00",
            "especialidad_principal": "Nutrición",
            "origen": "App Diabetes"
        }
    ],
    "total": 15,
    "limit": 20,
    "offset": 0
}
```

### GET /api/historial_paciente.php

Obtiene el historial de citas de un paciente específico.

**Parámetros:**
- `paciente_id` - ID del paciente (requerido)
- `rol` - Filtrar citas por rol del profesional (opcional)
- `limit` - Límite de resultados (default: 20)
- `offset` - Offset para paginación (default: 0)

**Ejemplo:**
```
GET /api/historial_paciente.php?paciente_id=1&rol=NUTRI
```

## Configuración

### Variables de Entorno (.env)
```
# Sistema de Gestión Médica
REMOTE_DB_HOST=localhost
REMOTE_DB_NAME=sistema_gestion_medica
REMOTE_DB_USER=root
REMOTE_DB_PASS=
```

### Verificación de Funcionamiento

1. **Ejecutar Script de Pruebas:**
   ```
   http://localhost/asosiacionMexicanaDeDiabetes/test_sync.php
   ```

2. **Verificar Registro:**
   - Registrar nuevo usuario en app diabetes
   - Verificar que aparezca en tabla `pacientes` del sistema médico

3. **Verificar Cita:**
   - Agendar cita desde la app
   - Verificar que aparezca en sistema médico
   - Consultar por rol correspondiente

## Manejo de Errores

- Todas las funciones de sincronización son **no-bloqueantes**
- Si falla la sincronización, la operación local continúa
- Los errores se registran en logs sin mostrar al usuario
- Las APIs devuelven errores estructurados en JSON

## Seguridad

- Validación de datos antes de sincronización
- Sanitización de inputs en todas las APIs
- Manejo de excepciones robusto
- Logs de errores para auditoría

## Escalabilidad

- Conexiones de base de datos eficientes
- Paginación en todas las consultas
- Índices optimizados en tablas principales
- Posibilidad de cola de sincronización asíncrona

## Casos de Uso

1. **Nutricionista:** Ve solo pacientes que han agendado citas de nutrición
2. **Endocrinólogo:** Ve solo pacientes con citas de endocrinología
3. **Sistema Integrado:** Historial completo desde app diabetes disponible
4. **Seguimiento:** Profesionales pueden ver evolución de pacientes app

## Monitoreo

- Script de pruebas para verificar conectividad
- Logs de sincronización exitosa/fallida
- Métricas de pacientes sincronizados
- APIs de estado del sistema