<?php
// workers/sync_worker.php
// CLI worker: php workers/sync_worker.php
chdir(__DIR__ . '/..');
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/sync_queue.php';
require_once __DIR__ . '/../includes/remote_api.php';

$limit = 10;
try {
    $stmt = $pdo->prepare("SELECT * FROM sync_queue WHERE status = 'pending' AND (next_attempt IS NULL OR next_attempt <= NOW()) ORDER BY created_at ASC LIMIT :lim");
    $stmt->bindValue(':lim', (int)$limit, PDO::PARAM_INT);
    $stmt->execute();
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    echo "Failed fetching queue: " . $e->getMessage() . "\n";
    exit(1);
}

foreach ($rows as $row) {
    $id = $row['id'];
    $tipo = $row['tipo'];
    $payload = json_decode($row['payload'], true) ?: [];

    echo "Processing #$id tipo=$tipo\n";

    // mark as processing
    $pdo->prepare("UPDATE sync_queue SET status='processing' WHERE id = ?")->execute([$id]);

    try {
        $remoteApiBase = getenv('REMOTE_API_URL') ?: null;
        $succeeded = false;
        $errorMsg = '';

        if ($tipo === 'cita') {
            $email = $payload['pacienteEmail'] ?? null;
            $idPacienteRemoto = null;

            if ($remoteApiBase) {
                $found = remote_get_patient_by_email($email);
                if (is_array($found) && !empty($found)) {
                    $idPacienteRemoto = $found['id'] ?? ($found[0]['id'] ?? null);
                }

                if (empty($idPacienteRemoto)) {
                    $created = remote_create_patient(['nombre' => $payload['nombre'] ?? ($payload['pacienteNombre'] ?? 'Paciente'), 'email' => $email]);
                    if ($created) {
                        $idPacienteRemoto = $created['id'] ?? ($created[0]['id'] ?? null);
                    } else {
                        throw new Exception('remote_create_patient failed');
                    }
                }

                if ($idPacienteRemoto) {
                    $payloadCita = [
                        'fechaHora' => $payload['fechaHora'] ?? null,
                        'motivo' => $payload['motivo'] ?? null,
                        'notas' => $payload['notas'] ?? null,
                        'estado' => $payload['estado'] ?? 'Pendiente',
                        'pacienteId' => $idPacienteRemoto,
                            'medicoId' => $payload['medicoId'] ?? (int)(getenv('REMOTE_DEFAULT_MEDICO_ID') ?: 14),
                            'especialidad' => $payload['especialidad'] ?? null,
                        'createdAt' => date('Y-m-d H:i:s'),
                        'updatedAt' => date('Y-m-d H:i:s')
                    ];
                    $createdCita = remote_create_cita($payloadCita);
                    if ($createdCita) {
                        $succeeded = true;
                    } else {
                        throw new Exception('remote_create_cita failed');
                    }
                }
            } else {
                $pdoRemote = getRemoteConnection();
                if (!$pdoRemote) throw new Exception('No remote DB connection');
                $stmtB = $pdoRemote->prepare("SELECT id FROM pacientes WHERE email = :email LIMIT 1");
                $stmtB->execute([':email' => $email]);
                $pr = $stmtB->fetch(PDO::FETCH_ASSOC);
                if ($pr) {
                    $idPacienteRemoto = $pr['id'];
                } else {
                    $colsStmt = $pdoRemote->prepare("SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'pacientes'");
                    $colsStmt->execute();
                    $cols = $colsStmt->fetchAll(PDO::FETCH_COLUMN, 0);
                    $insertCols = [];
                    $params = [];
                    if (in_array('email', $cols)) { $insertCols[]='email'; $params[':email']=$email; }
                    if (in_array('nombre', $cols)) { $insertCols[]='nombre'; $params[':nombre']=$payload['nombre'] ?? 'Paciente'; }
                    if (!empty($insertCols)) {
                        $sqlIns = "INSERT INTO pacientes (".implode(',', $insertCols).") VALUES (".implode(',', array_keys($params)).")";
                        $stmtI = $pdoRemote->prepare($sqlIns);
                        $stmtI->execute($params);
                        $idPacienteRemoto = $pdoRemote->lastInsertId();
                    } else {
                        throw new Exception('No compatible paciente columns');
                    }
                }

                $sql = "INSERT INTO cita (fechaHora, motivo, notas, estado, pacienteId, medicoId, createdAt, updatedAt) VALUES (:fechaHora,:motivo,:notas,:estado,:pacienteId,:medicoId,:creado,:actualizado)";
                $stmtC = $pdoRemote->prepare($sql);
                $stmtC->execute([
                    ':fechaHora' => $payload['fechaHora'],
                    ':motivo' => $payload['motivo'],
                    ':notas' => $payload['notas'],
                    ':estado' => $payload['estado'] ?? 'Pendiente',
                    ':pacienteId' => $idPacienteRemoto,
                    ':medicoId' => $payload['medicoId'] ?? 14,
                    ':creado' => date('Y-m-d H:i:s'),
                    ':actualizado' => date('Y-m-d H:i:s')
                ]);
                $succeeded = true;
            }
        } else {
            throw new Exception('Tipo no soportado: ' . $tipo);
        }

        if ($succeeded) {
            mark_sync_done($id);
            echo "#${id} done\n";
        }

    } catch (Exception $e) {
        $errorMsg = $e->getMessage();
        mark_sync_failed($id, $errorMsg);
        echo "#${id} failed: ${errorMsg}\n";
    }
}

echo "Worker finished.\n";

?>
        try {
            $stmt = $pdo->prepare("SELECT * FROM sync_queue WHERE status = 'pending' AND (next_attempt IS NULL OR next_attempt <= NOW()) ORDER BY created_at ASC LIMIT :lim");
            $stmt->bindValue(':lim', (int)$limit, PDO::PARAM_INT);
            $stmt->execute();
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            echo "Failed fetching queue: " . $e->getMessage() . "\n";
            exit(1);
        }

        foreach ($rows as $row) {
            $id = $row['id'];
            $tipo = $row['tipo'];
            $payload = json_decode($row['payload'], true) ?: [];

            echo "Processing #$id tipo=$tipo\n";

            // mark as processing
            $pdo->prepare("UPDATE sync_queue SET status='processing' WHERE id = ?")->execute([$id]);

            try {
                $remoteApiBase = getenv('REMOTE_API_URL') ?: null;
                $succeeded = false;
                $errorMsg = '';

                if ($tipo === 'cita') {
                    // ensure paciente exists remotely
                    $email = $payload['pacienteEmail'] ?? null;
                    $idPacienteRemoto = null;

                    if ($remoteApiBase) {
                        $found = remote_get_patient_by_email($email);
                        if (is_array($found) && !empty($found)) {
                            $idPacienteRemoto = $found['id'] ?? ($found[0]['id'] ?? null);
                        }

                        if (empty($idPacienteRemoto)) {
                            // attempt create
                            $created = remote_create_patient(['nombre' => $payload['nombre'] ?? ($payload['pacienteNombre'] ?? 'Paciente'), 'email' => $email]);
                            if ($created) {
                                $idPacienteRemoto = $created['id'] ?? ($created[0]['id'] ?? null);
                            } else {
                                throw new Exception('remote_create_patient failed');
                            }
                        }

                        if ($idPacienteRemoto) {
                            $payloadCita = [
                                'fechaHora' => $payload['fechaHora'] ?? null,
                                'motivo' => $payload['motivo'] ?? null,
                                'notas' => $payload['notas'] ?? null,
                                'estado' => $payload['estado'] ?? 'Pendiente',
                                'pacienteId' => $idPacienteRemoto,
                                    'medicoId' => $payload['medicoId'] ?? (int)(getenv('REMOTE_DEFAULT_MEDICO_ID') ?: 14),
                                    'especialidad' => $payload['especialidad'] ?? null,
                                'createdAt' => date('Y-m-d H:i:s'),
                                'updatedAt' => date('Y-m-d H:i:s')
                            ];
                            $createdCita = remote_create_cita($payloadCita);
                            if ($createdCita) {
                                $succeeded = true;
                            } else {
                                throw new Exception('remote_create_cita failed');
                            }
                        }
                    } else {
                        // Fallback DB direct
                        $pdoRemote = getRemoteConnection();
                        if (!$pdoRemote) throw new Exception('No remote DB connection');
                        // find paciente
                        $stmtB = $pdoRemote->prepare("SELECT id FROM pacientes WHERE email = :email LIMIT 1");
                        $stmtB->execute([':email' => $email]);
                        $pr = $stmtB->fetch(PDO::FETCH_ASSOC);
                        if ($pr) {
                            $idPacienteRemoto = $pr['id'];
                        } else {
                            // create minimal
                            $colsStmt = $pdoRemote->prepare("SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'pacientes'");
                            $colsStmt->execute();
                            $cols = $colsStmt->fetchAll(PDO::FETCH_COLUMN, 0);
                            $insertCols = [];
                            $params = [];
                            if (in_array('email', $cols)) { $insertCols[]='email'; $params[':email']=$email; }
                            if (in_array('nombre', $cols)) { $insertCols[]='nombre'; $params[':nombre']=$payload['nombre'] ?? 'Paciente'; }
                            if (!empty($insertCols)) {
                                $sqlIns = "INSERT INTO pacientes (".implode(',', $insertCols).") VALUES (".implode(',', array_keys($params)).")";
                                $stmtI = $pdoRemote->prepare($sqlIns);
                                $stmtI->execute($params);
                                $idPacienteRemoto = $pdoRemote->lastInsertId();
                            } else {
                                throw new Exception('No compatible paciente columns');
                            }
                        }

                        // insert cita
                        $sql = "INSERT INTO cita (fechaHora, motivo, notas, estado, pacienteId, medicoId, createdAt, updatedAt) VALUES (:fechaHora,:motivo,:notas,:estado,:pacienteId,:medicoId,:creado,:actualizado)";
                        $stmtC = $pdoRemote->prepare($sql);
                        $stmtC->execute([
                            ':fechaHora' => $payload['fechaHora'],
                            ':motivo' => $payload['motivo'],
                            ':notas' => $payload['notas'],
                            ':estado' => $payload['estado'] ?? 'Pendiente',
                            ':pacienteId' => $idPacienteRemoto,
                            ':medicoId' => $payload['medicoId'] ?? 14,
                            ':creado' => date('Y-m-d H:i:s'),
                            ':actualizado' => date('Y-m-d H:i:s')
                        ]);
                        $succeeded = true;
                    }
                } else {
                    throw new Exception('Tipo no soportado: ' . $tipo);
                }

                if ($succeeded) {
                    mark_sync_done($id);
                    echo "#${id} done\n";
                }

            } catch (Exception $e) {
                $errorMsg = $e->getMessage();
                mark_sync_failed($id, $errorMsg);
                echo "#${id} failed: ${errorMsg}\n";
            }
        }

        echo "Worker finished.\n";

        ?>
+                // insert cita
