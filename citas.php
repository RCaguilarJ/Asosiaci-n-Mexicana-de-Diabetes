<?php
    // VERIFICAR SESIÓN - El usuario debe estar logueado o ser invitado
    require 'includes/check-session.php';
    require 'includes/db.php'; // Conexión

    $paginaActual = 'citas';
    $tituloDeLaPagina = "Agendar Cita - Asoc. Mexicana de Diabetes"; 

    // 2. CONSULTAR CITAS (Backend) - Solo si no es invitado
    $citasUsuario = [];
    if (isset($_SESSION['usuario_id'])) {
        try {
            $stmt = $pdo->prepare("SELECT * FROM citas WHERE usuario_id = ? AND fecha_cita >= CURDATE() ORDER BY fecha_cita ASC, hora_cita ASC");
            $stmt->execute([$_SESSION['usuario_id']]);
            $citasUsuario = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            // Manejo silencioso
        }
    }
?>
<!DOCTYPE html>
<html lang="es">

<?php include 'includes/head.php'; ?>

<body>

    <?php include 'includes/menu-drawer.php'; ?>
    <?php include 'includes/header.php'; ?>

    <header class="page-header">
        <div class="page-header-icon">
            <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="#FFFFFF" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
        </div>
        <div class="page-header-text">
            <h1>Agendar Cita</h1>
            <p>Gestiona tus consultas</p>
        </div>
    </header>

    <main class="contenedor">

        <?php include 'includes/check-guest-banner.php'; ?>

        <h3 class="section-title">Próximas Citas</h3>
        
        <div class="appointments-list">
            
            <?php if (empty($citasUsuario)): ?>
                <div class="appointment-card" style="border-left-color: #ccc;">
                    <div class="appointment-header">
                        <div>
                            <h4 class="doctor-name">No tienes citas próximas</h4>
                            <span class="doctor-specialty">Utiliza el formulario de abajo para agendar una.</span>
                        </div>
                    </div>
                </div>
            <?php else: ?>
                <?php foreach($citasUsuario as $cita): ?>
                    <div class="appointment-card">
                        <div class="appointment-header">
                            <div>
                                <h4 class="doctor-name">Cita Médica</h4>
                                <span class="doctor-specialty"><?php echo htmlspecialchars($cita['especialidad']); ?></span>
                            </div>
                            
                            <?php 
                                $claseEstado = 'status-pending';
                                if($cita['estado'] == 'Confirmada') $claseEstado = 'status-confirmed';
                            ?>
                            <span class="status-badge <?php echo $claseEstado; ?>">
                                <?php echo htmlspecialchars($cita['estado']); ?>
                            </span>
                        </div>
                        
                        <ul class="appointment-details">
                            <li>
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg>
                                <?php echo date("d/m/Y", strtotime($cita['fecha_cita'])); ?>
                            </li>
                            <li>
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
                                <?php echo date("h:i A", strtotime($cita['hora_cita'])); ?>
                            </li>
                            <li>
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path><circle cx="12" cy="10" r="3"></circle></svg>
                                Consultorio General
                            </li>
                        </ul>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>

        </div>

        <form id="form-citas" class="card-form mt-30">
            <legend class="card-form-legend">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg>
                <span>Nueva Cita</span>
            </legend>

            <div class="form-group">
                <label for="nombre">Nombre Completo</label>
                <input type="text" id="nombre" class="form-control" placeholder="Juan Pérez García" value="<?php echo isset($_SESSION['usuario_nombre']) ? $_SESSION['usuario_nombre'] : ''; ?>" required>
            </div>

            <div class="form-grid-2">
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" class="form-control" placeholder="correo@ejemplo.com">
                </div>
                <div class="form-group">
                    <label for="telefono">Teléfono</label>
                    <input type="tel" id="telefono" class="form-control" placeholder="33 1234 5678" required>
                </div>
            </div>

            <div class="form-group">
                <label for="especialidad">Especialidad</label>
                <select id="especialidad" class="form-control" required>
                    <option value="" disabled selected>Selecciona una especialidad</option>
                    <option value="Medicina General">Medicina General</option>
                    <option value="Nutrición">Nutrición</option>
                    <option value="Endocrinología">Endocrinología</option>
                    <option value="Podología">Podología</option>
                    <option value="Psicología">Psicología</option>
                </select>
            </div>

            <div class="form-grid-2">
                <div class="form-group">
                    <label for="fecha">Fecha</label>
                    <input type="date" id="fecha" class="form-control" required>
                </div>
                <div class="form-group">
                    <label for="hora">Hora</label>
                    <select id="hora" class="form-control" required>
                        <option value="" disabled selected>Hora</option>
                        <option value="09:00">09:00 AM</option>
                        <option value="10:00">10:00 AM</option>
                        <option value="11:00">11:00 AM</option>
                        <option value="12:00">12:00 PM</option>
                        <option value="16:00">04:00 PM</option>
                        <option value="17:00">05:00 PM</option>
                    </select>
                </div>
            </div>

            <div class="form-group">
                <label for="notas">Notas Adicionales (Opcional)</label>
                <textarea id="notas" class="form-control" rows="3" placeholder="Describe el motivo de tu consulta..."></textarea>
            </div>

            <button type="submit" class="btn-calculadora">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg>
                <span>Agendar Cita</span>
            </button>
        </form>

        <section class="help-banner">
            <div class="help-icon">
                <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path></svg>
            </div>
            <h3>¿Necesitas Ayuda?</h3>
            <p>Llámanos para agendar tu cita por teléfono</p>
            <a href="tel:3312345678" class="btn-help">33 1234 5678</a>
        </section>

    </main>

    <?php include 'includes/footer.php'; ?>
    <script src="assets/js/app.js"></script> 
</body>
</html>