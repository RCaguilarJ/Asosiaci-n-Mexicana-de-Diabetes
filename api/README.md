# API de Integración - Sistema de Diabetes
## Conectando Plataforma de Pacientes con Sistema Médico

### URLs Base
```
Desarrollo: http://localhost/asosiacionMexicanaDeDiabetes/api/
Producción: https://tudominio.com/asosiacionMexicanaDeDiabetes/api/
```

### Headers Requeridos
```
Content-Type: application/json
Access-Control-Allow-Origin: http://localhost:5173
```

## Endpoints Disponibles

### 1. Gestión de Pacientes

#### GET - Obtener todos los pacientes
```
GET /pacientes.php?endpoint=pacientes
```

**Respuesta:**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "nombre": "Juan Pérez",
      "email": "juan@email.com",
      "fecha_registro": "2026-01-01 10:00:00",
      "estadisticas": {
        "total_registros_glucosa": 45,
        "total_citas": 3,
        "promedio_glucosa": 125.5,
        "ultimo_registro": "2026-01-07 08:30:00"
      }
    }
  ],
  "timestamp": "2026-01-07 15:30:00",
  "status_code": 200
}
```

#### GET - Detalle completo de un paciente
```
GET /pacientes.php?endpoint=paciente&id=1
```

**Respuesta:**
```json
{
  "success": true,
  "data": {
    "paciente": {
      "id": 1,
      "nombre": "Juan Pérez",
      "email": "juan@email.com",
      "fecha_registro": "2026-01-01 10:00:00"
    },
    "registros_glucosa_recientes": [
      {
        "id": 100,
        "nivel_glucosa": 120.0,
        "fecha_registro": "2026-01-07 08:30:00",
        "notas": null
      }
    ],
    "proximas_citas": [
      {
        "id": 25,
        "fecha_cita": "2026-01-15",
        "hora_cita": "10:00:00",
        "especialidad": "Endocrinología",
        "estado": "programada"
      }
    ],
    "estadisticas_30_dias": {
      "promedio_glucosa": 125.5,
      "minimo_glucosa": 85.0,
      "maximo_glucosa": 180.0,
      "total_registros": 30
    }
  }
}
```

#### GET - Registros de glucosa de un paciente
```
GET /pacientes.php?endpoint=glucosa&paciente_id=1&limite=50&fecha_inicio=2026-01-01&fecha_fin=2026-01-07
```

### 2. Reportes y Estadísticas

#### GET - Dashboard general para médicos
```
GET /reportes.php?endpoint=dashboard
```

**Respuesta:**
```json
{
  "success": true,
  "data": {
    "resumen": {
      "total_pacientes": 150,
      "pacientes_activos": 85,
      "citas_programadas": 25,
      "registros_hoy": 12
    },
    "distribucion_glucosa_7_dias": {
      "bajos": 5,
      "normales": 180,
      "altos": 15
    },
    "alertas_recientes": [
      {
        "paciente_id": 1,
        "paciente_nombre": "Juan Pérez",
        "nivel_glucosa": 250.0,
        "fecha_registro": "2026-01-07 14:30:00",
        "tipo_alerta": "hiperglucemia_severa"
      }
    ]
  }
}
```

#### GET - Estadísticas detalladas de un paciente
```
GET /reportes.php?endpoint=paciente-estadisticas&paciente_id=1&dias=30
```

### 3. Gestión de Citas

#### GET - Citas de un paciente específico
```
GET /pacientes.php?endpoint=citas&paciente_id=1
```

#### GET - Todas las citas recientes
```
GET /pacientes.php?endpoint=citas
```

## Implementación en React (Sistema Médico)

### 1. Servicio de API
Crea un archivo `src/services/diabetesApi.js`:

```javascript
// src/services/diabetesApi.js
const API_BASE_URL = 'http://localhost/asosiacionMexicanaDeDiabetes/api';

class DiabetesApiService {
  async request(endpoint, options = {}) {
    const url = `${API_BASE_URL}/${endpoint}`;
    
    const config = {
      headers: {
        'Content-Type': 'application/json',
        ...options.headers,
      },
      ...options,
    };

    try {
      const response = await fetch(url, config);
      const data = await response.json();
      
      if (!data.success) {
        throw new Error(data.message || 'Error en la API');
      }
      
      return data;
    } catch (error) {
      console.error('Error en API:', error);
      throw error;
    }
  }

  // Obtener todos los pacientes
  async getAllPacientes() {
    return this.request('pacientes.php?endpoint=pacientes');
  }

  // Obtener detalle de un paciente
  async getPacienteDetalle(id) {
    return this.request(`pacientes.php?endpoint=paciente&id=${id}`);
  }

  // Obtener registros de glucosa
  async getRegistrosGlucosa(pacienteId, options = {}) {
    const { limite = 30, fechaInicio, fechaFin } = options;
    let query = `pacientes.php?endpoint=glucosa&paciente_id=${pacienteId}&limite=${limite}`;
    
    if (fechaInicio) query += `&fecha_inicio=${fechaInicio}`;
    if (fechaFin) query += `&fecha_fin=${fechaFin}`;
    
    return this.request(query);
  }

  // Obtener dashboard de estadísticas
  async getDashboardStats() {
    return this.request('reportes.php?endpoint=dashboard');
  }

  // Obtener estadísticas de un paciente
  async getPacienteEstadisticas(pacienteId, dias = 30) {
    return this.request(`reportes.php?endpoint=paciente-estadisticas&paciente_id=${pacienteId}&dias=${dias}`);
  }

  // Obtener citas
  async getCitas(pacienteId = null) {
    const query = pacienteId 
      ? `pacientes.php?endpoint=citas&paciente_id=${pacienteId}`
      : 'pacientes.php?endpoint=citas';
    return this.request(query);
  }
}

export default new DiabetesApiService();
```

### 2. Hook personalizado para datos de diabetes
```javascript
// src/hooks/useDiabetesData.js
import { useState, useEffect } from 'react';
import diabetesApi from '../services/diabetesApi';

export const useDiabetesData = () => {
  const [pacientes, setPacientes] = useState([]);
  const [dashboardStats, setDashboardStats] = useState(null);
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState(null);

  const cargarPacientes = async () => {
    try {
      setLoading(true);
      const response = await diabetesApi.getAllPacientes();
      setPacientes(response.data);
    } catch (err) {
      setError(err.message);
    } finally {
      setLoading(false);
    }
  };

  const cargarDashboard = async () => {
    try {
      setLoading(true);
      const response = await diabetesApi.getDashboardStats();
      setDashboardStats(response.data);
    } catch (err) {
      setError(err.message);
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    cargarPacientes();
    cargarDashboard();
  }, []);

  return {
    pacientes,
    dashboardStats,
    loading,
    error,
    refetch: {
      cargarPacientes,
      cargarDashboard
    }
  };
};
```

### 3. Componente de ejemplo para mostrar pacientes
```javascript
// src/components/PacientesDiabetes.jsx
import React from 'react';
import { useDiabetesData } from '../hooks/useDiabetesData';

const PacientesDiabetes = () => {
  const { pacientes, loading, error } = useDiabetesData();

  if (loading) return <div>Cargando pacientes...</div>;
  if (error) return <div>Error: {error}</div>;

  return (
    <div className="pacientes-diabetes">
      <h2>Pacientes con Diabetes</h2>
      <div className="pacientes-grid">
        {pacientes.map(paciente => (
          <div key={paciente.id} className="paciente-card">
            <h3>{paciente.nombre}</h3>
            <p>Email: {paciente.email}</p>
            <div className="estadisticas">
              <p>Registros: {paciente.estadisticas.total_registros_glucosa}</p>
              <p>Promedio glucosa: {paciente.estadisticas.promedio_glucosa}</p>
              <p>Último registro: {paciente.estadisticas.ultimo_registro}</p>
            </div>
          </div>
        ))}
      </div>
    </div>
  );
};

export default PacientesDiabetes;
```

### 4. Integración en el Dashboard médico
```javascript
// Agregar a tu Dashboard.jsx existente
import PacientesDiabetes from '../components/PacientesDiabetes';

// Dentro del componente Dashboard
<div className="dashboard-section">
  <PacientesDiabetes />
</div>
```

## Configuración CORS en Apache (si es necesario)
Agregar al `.htaccess` en la carpeta API:
```apache
Header add Access-Control-Allow-Origin "http://localhost:5173"
Header add Access-Control-Allow-Methods "GET,POST,PUT,DELETE,OPTIONS"
Header add Access-Control-Allow-Headers "Content-Type,Authorization,X-Requested-With"
```

## Próximos Pasos
1. Implementar autenticación JWT para mayor seguridad
2. Agregar endpoints para crear/actualizar datos desde el sistema médico
3. Configurar webhooks para notificaciones en tiempo real
4. Implementar filtros avanzados y paginación

## Endpoint: Actualizar rol de usuario

Para que la plataforma médica pueda cambiar el rol de un usuario (por ejemplo, asignar `DOCTOR` o `ADMIN`), usamos:

```
POST /update_role.php
Headers: Authorization: Bearer <TOKEN>
Body JSON: { "user_id": 123, "role": "ADMIN" }
```

Respuesta de ejemplo:
```json
{ "success": true, "message": "Role updated" }
```

Nota: configura el token compartido en `api/config/auth.php` (variable `$API_SHARED_TOKEN`).

Ejemplo de llamada desde React (fetch):
```javascript
const token = 'CHANGE_THIS_TO_A_STRONG_SECRET_TOKEN';
await fetch('http://localhost/asosiacionMexicanaDeDiabetes/api/update_role.php', {
  method: 'POST',
  headers: {
    'Content-Type': 'application/json',
    'Authorization': `Bearer ${token}`
  },
  body: JSON.stringify({ user_id: 123, role: 'ADMIN' })
});
```