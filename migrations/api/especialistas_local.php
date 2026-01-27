<?php
/**
 * Endpoint local simple para especialistas
 * Simula la respuesta del Sistema de Gestión Médica
 */

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Signature, X-Source');

// Manejar preflight OPTIONS
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Datos de especialistas simulados
$especialistas = [
    // Nutricionistas
    [
        'id' => 101,
        'nombre' => 'Dra. María Fernández',
        'email' => 'maria.fernandez@sistemamedico.com',
        'username' => 'mfernandez',
        'role' => 'NUTRI',
        'especialidad' => 'Nutriología Clínica',
        'disponible' => true,
        'telefono' => '33-1234-5678',
        'horarios' => ['09:00-13:00', '15:00-18:00']
    ],
    [
        'id' => 102,
        'nombre' => 'Lic. Carlos Nutrition',
        'email' => 'carlos.nutrition@sistemamedico.com',
        'username' => 'cnutrition',
        'role' => 'NUTRI',
        'especialidad' => 'Nutrición Deportiva',
        'disponible' => true,
        'telefono' => '33-2345-6789',
        'horarios' => ['08:00-12:00', '14:00-17:00']
    ],
    
    // Endocrinólogos
    [
        'id' => 201,
        'nombre' => 'Dr. José Endocrino',
        'email' => 'jose.endocrino@sistemamedico.com',
        'username' => 'jendocrino',
        'role' => 'ENDOCRINO',
        'especialidad' => 'Endocrinología y Diabetes',
        'disponible' => true,
        'telefono' => '33-3456-7890',
        'horarios' => ['10:00-14:00', '16:00-19:00']
    ],
    [
        'id' => 202,
        'nombre' => 'Dra. Ana Hormona',
        'email' => 'ana.hormona@sistemamedico.com',
        'username' => 'ahormona',
        'role' => 'ENDOCRINO',
        'especialidad' => 'Trastornos Hormonales',
        'disponible' => true,
        'telefono' => '33-4567-8901',
        'horarios' => ['09:00-13:00', '15:00-18:00']
    ],
    
    // Podólogos
    [
        'id' => 301,
        'nombre' => 'Dr. Luis Pies',
        'email' => 'luis.pies@sistemamedico.com',
        'username' => 'lpies',
        'role' => 'PODOLOGO',
        'especialidad' => 'Podología Clínica',
        'disponible' => true,
        'telefono' => '33-5678-9012',
        'horarios' => ['08:00-12:00', '14:00-17:00']
    ],
    
    // Psicólogos
    [
        'id' => 401,
        'nombre' => 'Dra. Carmen Mente',
        'email' => 'carmen.mente@sistemamedico.com',
        'username' => 'cmente',
        'role' => 'PSICOLOGO',
        'especialidad' => 'Psicología Clínica',
        'disponible' => true,
        'telefono' => '33-6789-0123',
        'horarios' => ['10:00-14:00', '16:00-19:00']
    ],
    [
        'id' => 402,
        'nombre' => 'Dr. Rafael Salud',
        'email' => 'rafael.salud@sistemamedico.com',
        'username' => 'rsalud',
        'role' => 'PSICOLOGO',
        'especialidad' => 'Psicología de la Salud',
        'disponible' => true,
        'telefono' => '33-7890-1234',
        'horarios' => ['09:00-13:00', '15:00-18:00']
    ],
    
    // Doctores/Medicina General
    [
        'id' => 501,
        'nombre' => 'Dr. Roberto General',
        'email' => 'roberto.general@sistemamedico.com',
        'username' => 'rgeneral',
        'role' => 'DOCTOR',
        'especialidad' => 'Medicina General',
        'disponible' => true,
        'telefono' => '33-8901-2345',
        'horarios' => ['08:00-14:00', '16:00-20:00']
    ],
    [
        'id' => 502,
        'nombre' => 'Dra. Patricia Medicina',
        'email' => 'patricia.medicina@sistemamedico.com',
        'username' => 'pmedicina',
        'role' => 'DOCTOR',
        'especialidad' => 'Medicina Familiar',
        'disponible' => true,
        'telefono' => '33-9012-3456',
        'horarios' => ['09:00-13:00', '15:00-19:00']
    ]
];

try {
    // Obtener filtro por rol si se especifica
    $role = $_GET['role'] ?? null;
    
    // Filtrar por rol si se especifica
    if ($role) {
        $especialistasFiltrados = array_filter($especialistas, function($esp) use ($role) {
            return $esp['role'] === $role;
        });
        $especialistasFiltrados = array_values($especialistasFiltrados);
    } else {
        $especialistasFiltrados = $especialistas;
    }
    
    // Simular respuesta de la API real
    $response = [
        'success' => true,
        'data' => $especialistasFiltrados,
        'total' => count($especialistasFiltrados),
        'filtered_by_role' => $role ?: 'all',
        'timestamp' => date('c'),
        'source' => 'sistema-gestion-medica-local'
    ];
    
    // Agregar headers de respuesta
    http_response_code(200);
    echo json_encode($response, JSON_PRETTY_PRINT);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Error interno del servidor',
        'message' => $e->getMessage(),
        'timestamp' => date('c')
    ]);
}
?>