<?php
// api/get_especialistas.php
header('Content-Type: application/json; charset=utf-8');

require_once '../includes/db.php';

try {
    $roleFilter = $_GET['role'] ?? null;
    $whereClause = "WHERE estatus = 'Activo'";
    $params = [];

    // Mapeo de roles para la consulta
    if ($roleFilter) {
        // Normalizamos el rol (ej: si llega 'Podologia' lo convertimos a 'PODOLOGO')
        $rolNormalizado = strtoupper($roleFilter);
        if(strpos($rolNormalizado, 'NUTRI') !== false) $rolNormalizado = 'NUTRI';
        elseif(strpos($rolNormalizado, 'PSIC') !== false) $rolNormalizado = 'PSICOLOGO';
        elseif(strpos($rolNormalizado, 'ENDO') !== false) $rolNormalizado = 'ENDOCRINOLOGO';
        elseif(strpos($rolNormalizado, 'POD') !== false) $rolNormalizado = 'PODOLOGO';
        elseif(strpos($rolNormalizado, 'DOC') !== false || strpos($rolNormalizado, 'MED') !== false) $rolNormalizado = 'DOCTOR';
        
        $whereClause .= " AND role = ?";
        $params[] = $rolNormalizado;
    } else {
        // Si no hay filtro, traer solo personal médico (excluir admin y pacientes)
        $whereClause .= " AND role IN ('DOCTOR', 'NUTRI', 'PSICOLOGO', 'ENDOCRINOLOGO', 'PODOLOGO')";
    }

    $stmt = $pdo->prepare("SELECT id, nombre, email, role, username FROM users $whereClause ORDER BY nombre ASC");
    $stmt->execute($params);
    $usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Agrupar por rol para el frontend
    $rolesMap = [
        'DOCTOR' => 'Medicina General',
        'NUTRI' => 'Nutrición',
        'ENDOCRINOLOGO' => 'Endocrinología',
        'PODOLOGO' => 'Podología',
        'PSICOLOGO' => 'Psicología'
    ];

    $resultado = [];
    $rolesEncontrados = [];

    foreach ($usuarios as $user) {
        $rolKey = $user['role'];
        if (!isset($rolesEncontrados[$rolKey])) {
            $rolesEncontrados[$rolKey] = [
                'role' => $rolKey,
                'nombre_rol' => $rolesMap[$rolKey] ?? $rolKey,
                'especialistas' => []
            ];
        }
        $rolesEncontrados[$rolKey]['especialistas'][] = [
            'id' => $user['id'],
            'nombre' => $user['nombre'],
            'email' => $user['email'],
            'username' => $user['username'],
            'disponible' => true
        ];
    }

    echo json_encode([
        'success' => true,
        'roles' => array_values($rolesEncontrados)
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>