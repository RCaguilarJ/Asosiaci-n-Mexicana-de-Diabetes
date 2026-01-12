<?php
// includes/remote_api.php
// Cliente HTTP ligero para sincronizar pacientes y citas con la plataforma médica.
// Usa las variables de entorno REMOTE_API_URL y REMOTE_API_TOKEN.

function remote_api_request($method, $path, $data = null) {
    $base = rtrim(getenv('REMOTE_API_URL') ?: '', '/');
    $token = getenv('REMOTE_API_TOKEN') ?: null;

    if (!$base) {
        error_log('remote_api_request: REMOTE_API_URL no configurada');
        return null;
    }

    $url = $base . '/' . ltrim($path, '/');
    // Algunos entornos (Apache + PHP) no pasan correctamente el header
    // Authorization a PHP; además añadimos el token como parámetro `?token=`
    // para asegurar que `api/config/auth.php` lo pueda leer via $_GET.
    if ($token) {
        $sep = (strpos($url, '?') === false) ? '?' : '&';
        $url .= $sep . 'token=' . urlencode($token);
    }

    $ch = curl_init();
    $headers = ['Accept: application/json'];
    if ($token) $headers[] = 'Authorization: Bearer ' . $token;

    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);

    if (strtoupper($method) === 'POST') {
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        $headers[] = 'Content-Type: application/json';
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    } elseif (strtoupper($method) === 'PUT') {
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        $headers[] = 'Content-Type: application/json';
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    }

    $resp = curl_exec($ch);
    $err = curl_error($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($err) {
        error_log('remote_api_request curl error: ' . $err . ' url=' . $url);
        return null;
    }

    $decoded = json_decode($resp, true);

    // Debugging: when the response is not valid JSON or status is not 2xx,
    // persist a short capture to a local debug file to help diagnose API errors
    // (e.g., Xdebug HTML traces, auth failures, or token mismatches).
    if (($code < 200 || $code >= 300) || ($resp !== null && $decoded === null)) {
        $dbg = __DIR__ . '/../worker_debug.log';
        $snippet = is_string($resp) ? substr($resp, 0, 4000) : var_export($resp, true);
        $entry = date('Y-m-d H:i:s') . " URL=" . $url . " STATUS=" . $code . "\n" . $snippet . "\n----\n";
        @file_put_contents($dbg, $entry, FILE_APPEND | LOCK_EX);
    }

    return ['status' => $code, 'body' => $decoded, 'raw' => $resp];
}

function remote_get_patient_by_email($email) {
    // Query the pacientes list endpoint and search for the email (API exposes 'endpoint=pacientes')
    $query = 'api/pacientes.php?endpoint=pacientes';
    $res = remote_api_request('GET', $query);
    if (!$res) return null;
    if ($res['status'] >= 200 && $res['status'] < 300 && is_array($res['body'])) {
        $list = $res['body'];
        foreach ($list as $item) {
            $itemEmail = $item['email'] ?? $item['correo'] ?? null;
            if ($itemEmail && strcasecmp(trim($itemEmail), trim($email)) === 0) {
                return $item;
            }
        }
    }
    return null;
}

function remote_create_patient($data) {
    // Try a POST to an API endpoint if available (some deployments expose a create-paciente endpoint).
    $res = remote_api_request('POST', 'api/pacientes.php', $data);
    if ($res && $res['status'] >= 200 && $res['status'] < 300) {
        return $res['body'];
    }

    // If API doesn't support creating pacientes, attempt a direct DB insert as a last-resort
    // (useful in local testing when REMOTE_API_URL points to the same app).
    try {
        require_once __DIR__ . '/db.php';
        if (isset($pdo) && $pdo instanceof PDO) {
            $nombre = $data['nombre'] ?? ($data['nombre_completo'] ?? 'Paciente');
            $email = $data['email'] ?? null;
            if (!$email) return null;
            $stmt = $pdo->prepare('SELECT id FROM usuarios WHERE email = :email LIMIT 1');
            $stmt->execute([':email' => $email]);
            $exists = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($exists) return $exists;
            $ins = $pdo->prepare('INSERT INTO usuarios (nombre, email, fecha_registro) VALUES (:nombre, :email, NOW())');
            $ins->execute([':nombre' => $nombre, ':email' => $email]);
            $id = $pdo->lastInsertId();
            return ['id' => $id, 'nombre' => $nombre, 'email' => $email];
        }
    } catch (Exception $e) {
        error_log('remote_create_patient DB fallback failed: ' . $e->getMessage());
    }

    error_log('remote_create_patient failed: ' . ($res['raw'] ?? 'no response'));
    return null;
}

function remote_create_cita($data) {
    // Use the explicit create_cita endpoint which accepts POST JSON
    $res = remote_api_request('POST', 'api/create_cita.php', $data);
    if (!$res) return null;
    if ($res['status'] >= 200 && $res['status'] < 300) {
        return $res['body'];
    }
    error_log('remote_create_cita failed: ' . $res['raw']);
    return null;
}

?>
