-- scripts/create_sync_queue.sql
-- Tabla para encolar operaciones de sincronización remota (pacientes/citas)

CREATE TABLE IF NOT EXISTS sync_queue (
  id INT PRIMARY KEY AUTO_INCREMENT,
  tipo VARCHAR(50) NOT NULL, -- 'paciente' | 'cita' | otros
  payload JSON NOT NULL,
  attempts INT NOT NULL DEFAULT 0,
  max_attempts INT NOT NULL DEFAULT 5,
  last_error TEXT DEFAULT NULL,
  status VARCHAR(20) NOT NULL DEFAULT 'pending', -- pending | processing | done | failed
  next_attempt DATETIME DEFAULT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Index para buscar pendientes

