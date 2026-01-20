<?php
    // views/citas.php
    
    // Subimos un nivel (../) para encontrar la carpeta includes
    require '../includes/security/check-session.php'; 
    require '../includes/db.php'; 
    require '../includes/historial_citas.php';

    $paginaActual = 'citas';
    $tituloDeLaPagina = "Agendar Cita - Asoc. Mexicana de Diabetes"; 

    $citasUsuario = [];
    $historialCitas = [];
    $estadisticas = [];
    
    if (isset($_SESSION['usuario_id'])) {
        try {
            // Citas futuras
            $stmt = $pdo->prepare("SELECT * FROM citas WHERE usuario_id = ? AND fecha_cita >= NOW() ORDER BY fecha_cita ASC");
            $stmt->execute([$_SESSION['usuario_id']]);
            $citasUsuario = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Evitar duplicados por misma cita (usuario/medico/fecha/especialidad)
            $citasUnicas = [];
            foreach ($citasUsuario as $cita) {
                $clave = ($cita['usuario_id'] ?? '') . '|' . ($cita['medico_id'] ?? '') . '|' . ($cita['fecha_cita'] ?? '') . '|' . ($cita['especialidad'] ?? '');
                if (!isset($citasUnicas[$clave])) {
                    $citasUnicas[$clave] = $cita;
                }
            }
            $citasUsuario = array_values($citasUnicas);
            
            // Historial rotativo (últimas 5 citas)
            $historialCitas = obtenerHistorialCitas($_SESSION['usuario_id']);
            
            // Estadísticas
            $estadisticas = obtenerEstadisticasHistorial($_SESSION['usuario_id']);
        } catch (Exception $e) {
            // Manejo silencioso
        }
    }
?>
<!DOCTYPE html>
<html lang="es">

<?php include '../includes/layout/head.php'; ?>

<body>

    <?php include '../includes/layout/menu-drawer.php'; ?>
    <?php include '../includes/layout/header.php'; ?>

    <header class="page-header">
        <div class="page-header-icon">
            <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="#FFFFFF" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
        </div>
        <div class="page-header-text">
            <h1>Agendar Cita</h1>
            <p>Gestiona tus consultas</p>
        </div>
        <div class="page-header-action">
            <a href="../views/index.php" class="btn-back" style="color: white; text-decoration: none; padding: 8px; border: 1px solid rgba(255,255,255,0.3); border-radius: 50%; width: 40px; height: 40px; display: flex; align-items: center; justify-content: center;">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 12H5"></path><path d="M12 19l-7-7 7-7"></path></svg>
            </a>
        </div>
    </header>

    <main class="contenedor">

        <?php include '../includes/check-guest-banner.php'; ?>
        
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
                                $estadoNormalizado = strtolower($cita['estado'] ?? 'pendiente');
                                $claseEstado = $estadoNormalizado === 'confirmada' ? 'status-confirmed' : 'status-pending';
                                $estadoLabel = ucfirst($estadoNormalizado);
                            ?>
                            <span class="status-badge <?php echo $claseEstado; ?>">
                                <?php echo htmlspecialchars($estadoLabel); ?>
                            </span>
                        </div>
                        <ul class="appointment-details">
                            <li>
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg>
                                <?php echo date("d/m/Y", strtotime($cita['fecha_cita'])); ?>
                            </li>
                            <li>
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
                                <?php echo date("h:i A", strtotime($cita['fecha_cita'])); ?>
                            </li>
                        </ul>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <form id="form-citas" class="card-form mt-30" autocomplete="off">
            <legend class="card-form-legend">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg>
                <span>Nueva Cita</span>
            </legend>

            <div class="form-group">
                <label for="nombre">Nombre Completo</label>
                <input type="text" id="nombre" name="nombre" class="form-control" placeholder="Juan Pérez García" value="<?php echo isset($_SESSION['usuario_nombre']) ? $_SESSION['usuario_nombre'] : ''; ?>" autocomplete="name" required>
            </div>

            <div class="form-grid-2">
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" class="form-control" placeholder="correo@ejemplo.com" autocomplete="email">
                </div>
                <div class="form-group">
                    <label for="telefono">Teléfono</label>
                    <input type="tel" id="telefono" name="telefono" class="form-control" placeholder="33 1234 5678" autocomplete="tel" required>
                </div>
            </div>

            <div class="form-group">
                <label for="especialidad">Especialidad</label>
                <select id="especialidad" name="especialidad" class="form-control" required>
                    <option value="" disabled selected>Cargando especialidades...</option>
                </select>
                <div class="loading-spinner" id="loading-especialidades" style="display: none;">
                    <span>Cargando especialistas disponibles...</span>
                </div>
            </div>

            <div class="form-group" id="medico-group" style="display: none;">
                <label for="medico_id">Especialista</label>
                <select id="medico_id" name="medico_id" class="form-control" required>
                    <option value="" disabled selected>Selecciona un especialista</option>
                </select>
                <small class="form-help">Selecciona primero una especialidad para ver los especialistas disponibles</small>
            </div>

            <div class="form-grid-2">
                <div class="form-group">
                    <label for="fecha">Fecha</label>
                    <input type="date" id="fecha" name="fecha" class="form-control" required>
                </div>
                <div class="form-group">
                    <label for="hora">Hora</label>
                    <select id="hora" name="hora" class="form-control" required>
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
                <textarea id="notas" name="descripcion" class="form-control" rows="3" placeholder="Describe el motivo de tu consulta..."></textarea>
            </div>

            <button type="submit" class="btn-calculadora">
                Agendar Cita
            </button>
        </form>

        <!-- Historial Rotativo de Citas -->
        <?php if (!empty($historialCitas) || !empty($estadisticas)): ?>
        <section class="historial-section mt-30">
            <div class="card-form">
                <legend class="card-form-legend">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><path d="M12 7v5l3 3"></path></svg>
                    <span>Historial de Citas (Últimas 5)</span>
                </legend>

                <!-- Estadísticas rápidas -->
                <?php if (!empty($estadisticas) && $estadisticas['total_citas'] > 0): ?>
                <div class="stats-grid mb-20" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(120px, 1fr)); gap: 15px;">
                    <div class="stat-card" style="background: #f8f9fa; padding: 15px; border-radius: 8px; text-align: center;">
                        <div style="font-size: 24px; font-weight: bold; color: #0066b2;"><?php echo $estadisticas['total_citas']; ?></div>
                        <div style="font-size: 12px; color: #666;">Total</div>
                    </div>
                    <div class="stat-card" style="background: #f8f9fa; padding: 15px; border-radius: 8px; text-align: center;">
                        <div style="font-size: 24px; font-weight: bold; color: #28a745;"><?php echo $estadisticas['completadas']; ?></div>
                        <div style="font-size: 12px; color: #666;">Completadas</div>
                    </div>
                    <div class="stat-card" style="background: #f8f9fa; padding: 15px; border-radius: 8px; text-align: center;">
                        <div style="font-size: 24px; font-weight: bold; color: #ffc107;"><?php echo $estadisticas['pendientes']; ?></div>
                        <div style="font-size: 12px; color: #666;">Pendientes</div>
                    </div>
                </div>
                <p class="info-note" style="font-size: 13px; color: #666; margin-bottom: 20px; text-align: center;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="vertical-align: middle;"><circle cx="12" cy="12" r="10"></circle><path d="M12 16v-4"></path><path d="M12 8h.01"></path></svg>
                    Se mantienen solo las últimas 5 citas por fecha. Las anteriores se eliminan automáticamente.
                </p>
                <?php endif; ?>

                <!-- Lista del historial -->
                <?php if (!empty($historialCitas)): ?>
                <div class="historial-list">
                    <?php foreach ($historialCitas as $cita): ?>
                    <div class="historial-item" style="border: 1px solid #e9ecef; border-radius: 8px; padding: 15px; margin-bottom: 10px; background: #fff;">
                        <div style="display: flex; justify-content: between; align-items: flex-start; gap: 15px;">
                            <div style="flex: 1;">
                                <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 8px;">
                                    <div style="width: 8px; height: 8px; border-radius: 50%; background: <?php 
                                        echo $cita['estado'] === 'completada' ? '#28a745' : 
                                            ($cita['estado'] === 'pendiente' ? '#ffc107' : '#6c757d'); 
                                    ?>;"></div>
                                    <span style="font-weight: 600; color: #333;"><?php echo htmlspecialchars($cita['especialidad']); ?></span>
                                    <span style="font-size: 12px; color: #666; text-transform: capitalize;"><?php echo $cita['estado']; ?></span>
                                </div>
                                
                                <div style="font-size: 14px; color: #666; margin-bottom: 5px;">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="vertical-align: middle; margin-right: 5px;"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
                                    <?php echo formatearFechaAmigable($cita['fecha_cita']); ?>
                                </div>
                                
                                <?php if (!empty($cita['descripcion'])): ?>
                                <div style="font-size: 13px; color: #888; font-style: italic;">
                                    "<?php echo htmlspecialchars(substr($cita['descripcion'], 0, 100)) . (strlen($cita['descripcion']) > 100 ? '...' : ''); ?>"
                                </div>
                                <?php endif; ?>
                            </div>
                            
                            <div style="font-size: 11px; color: #aaa; text-align: right; white-space: nowrap;">
                                Registrada<br><?php echo $cita['fecha_registro_formatted']; ?>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php else: ?>
                <div style="text-align: center; color: #666; padding: 30px;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" style="margin-bottom: 15px; opacity: 0.5;"><circle cx="12" cy="12" r="10"></circle><path d="M12 7v5l3 3"></path></svg>
                    <p>Aún no tienes citas en tu historial.</p>
                    <p style="font-size: 13px; color: #888;">Agenda tu primera cita arriba.</p>
                </div>
                <?php endif; ?>
            </div>
        </section>
        <?php endif; ?>

        <?php include '../includes/layout/footer.php'; ?>
    </main>
    
    <script src="/asosiacionMexicanaDeDiabetes/assets/js/app.js"></script>
    
    <script>
    // Sistema de citas con especialistas dinámicos
    document.addEventListener('DOMContentLoaded', function() {
        cargarEspecialistas();
        
        // Manejar cambio de especialidad
        document.getElementById('especialidad').addEventListener('change', function() {
            const especialidad = this.value;
            if (especialidad) {
                cargarMedicosPorEspecialidad(especialidad);
            }
        });
        
        // Validaciones del formulario
        const formCitas = document.getElementById('form-citas');
        if (formCitas) {
            formCitas.addEventListener('submit', function(e) {
                e.preventDefault();
                validarYEnviarCita();
            });
        }
    });
    
    async function cargarEspecialistas() {
        const selectEspecialidad = document.getElementById('especialidad');
        const loadingSpinner = document.getElementById('loading-especialidades');
        
        try {
            loadingSpinner.style.display = 'block';
            
            const response = await fetch('/asosiacionMexicanaDeDiabetes/api/get_especialistas.php', {
                method: 'GET',
                headers: {
                    'Content-Type': 'application/json'
                }
            });
            
            if (!response.ok) {
                throw new Error('Error cargando especialistas: ' + response.status);
            }
            
            const data = await response.json();
            
            if (!data.success) {
                throw new Error(data.error || 'Error desconocido');
            }
            
            // Limpiar opciones actuales
            selectEspecialidad.innerHTML = '<option value="" disabled selected>Selecciona una especialidad</option>';
            
            // Agregar especialidades disponibles
            data.roles.forEach(rol => {
                const option = document.createElement('option');
                option.value = rol.nombre_rol;
                option.dataset.role = rol.role;
                option.textContent = `${rol.nombre_rol} (${rol.especialistas.length} disponibles)`;
                selectEspecialidad.appendChild(option);
            });
            
            // Mostrar warning si es fallback local
            if (data.fallback_local) {
                mostrarNotificacion('Usando especialistas locales. Conexión con sistema médico no disponible.', 'warning');
            }
            
        } catch (error) {
            console.error('Error cargando especialistas:', error);
            
            // Fallback manual en caso de error completo
            selectEspecialidad.innerHTML = `
                <option value="" disabled selected>Selecciona una especialidad</option>
                <option value="Medicina General">Medicina General</option>
                <option value="Nutrición">Nutrición</option>
                <option value="Endocrinología">Endocrinología</option>
                <option value="Podología">Podología</option>
                <option value="Psicología">Psicología</option>
            `;
            
            mostrarNotificacion('Error cargando especialistas. Usando lista básica.', 'error');
        } finally {
            loadingSpinner.style.display = 'none';
        }
    }
    
    async function cargarMedicosPorEspecialidad(especialidad) {
        const selectMedico = document.getElementById('medico_id');
        const medicoGroup = document.getElementById('medico-group');
        
        try {
            medicoGroup.style.display = 'block';
            selectMedico.innerHTML = '<option value="" disabled selected>Cargando especialistas...</option>';
            
            // Obtener role del dataset de la opción seleccionada
            const selectEspecialidad = document.getElementById('especialidad');
            const selectedOption = selectEspecialidad.options[selectEspecialidad.selectedIndex];
            const role = selectedOption.dataset.role;
            
            const response = await fetch(`/asosiacionMexicanaDeDiabetes/api/get_especialistas.php?role=${encodeURIComponent(role)}`, {
                method: 'GET',
                headers: {
                    'Content-Type': 'application/json'
                }
            });
            
            const data = await response.json();
            
            if (!data.success) {
                throw new Error(data.error || 'Error cargando médicos');
            }
            
            selectMedico.innerHTML = '<option value="" disabled selected>Selecciona un especialista</option>';
            
            // Agregar médicos del rol específico
            if (data.roles.length > 0) {
                data.roles[0].especialistas.forEach(medico => {
                    const option = document.createElement('option');
                    option.value = medico.id;
                    option.dataset.username = medico.username;
                    option.dataset.email = medico.email;
                    option.textContent = medico.nombre;
                    if (!medico.disponible) {
                        option.disabled = true;
                        option.textContent += ' (No disponible)';
                    }
                    selectMedico.appendChild(option);
                });
            } else {
                selectMedico.innerHTML = '<option value="" disabled>No hay especialistas disponibles</option>';
            }
            
        } catch (error) {
            console.error('Error cargando médicos:', error);
            selectMedico.innerHTML = '<option value="" disabled>Error cargando especialistas</option>';
        }
    }
    
    async function validarYEnviarCita() {
        const form = document.getElementById('form-citas');
        const formData = new FormData(form);
        
        // Validaciones client-side
        const medico_id = formData.get('medico_id');
        const especialidad = formData.get('especialidad');
        const fecha = formData.get('fecha');
        const hora = formData.get('hora');
        
        if (!medico_id) {
            mostrarNotificacion('Debe seleccionar un especialista específico', 'error');
            return;
        }
        
        if (!especialidad) {
            mostrarNotificacion('Debe seleccionar una especialidad', 'error');
            return;
        }
        
        // Validar fecha no sea en el pasado
        const fechaCita = new Date(fecha + ' ' + hora);
        const ahora = new Date();
        
        if (fechaCita <= ahora) {
            mostrarNotificacion('La fecha y hora deben ser futuras', 'error');
            return;
        }
        
        try {
            // Deshabilitar botón durante envío
            const submitBtn = form.querySelector('button[type="submit"]');
            const originalText = submitBtn.textContent; // Definir originalText aquí
            submitBtn.setAttribute('data-original-text', originalText); // Guardar para later
            submitBtn.disabled = true;
            submitBtn.textContent = 'Agendando...';
            
            const response = await fetch('/asosiacionMexicanaDeDiabetes/actions/guardar_cita.php', {
                method: 'POST',
                body: formData
            });
            
            const result = await response.json();
            
            if (result.success) {
                mostrarNotificacion(result.mensaje, 'success');
                
                // Mostrar confirmación específica si hay médico asignado
                if (result.confirmacion) {
                    setTimeout(() => {
                        mostrarNotificacion(result.confirmacion, 'info');
                    }, 2000);
                }
                
                // Limpiar formulario
                form.reset();
                document.getElementById('medico-group').style.display = 'none';
                
                // Recargar página para mostrar historial actualizado
                setTimeout(() => {
                    location.reload();
                }, 3000);
                
            } else {
                mostrarNotificacion(result.error || 'Error agendando cita', 'error');
            }
            
        } catch (error) {
            console.error('Error enviando cita:', error);
            mostrarNotificacion('Error de conexión. Intente nuevamente.', 'error');
        } finally {
            // Rehabilitar botón
            const submitBtn = form.querySelector('button[type="submit"]');
            if (submitBtn) {
                submitBtn.disabled = false;
                submitBtn.textContent = submitBtn.getAttribute('data-original-text') || 'Agendar Cita';
            }
        }
    }
    
    function mostrarNotificacion(mensaje, tipo) {
        // Crear elemento de notificación
        const notification = document.createElement('div');
        notification.className = `notification notification-${tipo}`;
        notification.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 15px 20px;
            border-radius: 8px;
            color: white;
            font-weight: 500;
            z-index: 10000;
            max-width: 400px;
            word-wrap: break-word;
            animation: slideIn 0.3s ease;
        `;
        
        // Colores según tipo
        const colores = {
            success: '#28a745',
            error: '#dc3545',
            warning: '#ffc107',
            info: '#17a2b8'
        };
        
        notification.style.backgroundColor = colores[tipo] || colores.info;
        notification.textContent = mensaje;
        
        document.body.appendChild(notification);
        
        // Auto-remover después de 5 segundos
        setTimeout(() => {
            if (notification.parentNode) {
                notification.remove();
            }
        }, 5000);
    }
    </script>
    
    <style>
    .loading-spinner {
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: 14px;
        color: #666;
        margin-top: 5px;
    }
    
    .form-help {
        font-size: 12px;
        color: #666;
        margin-top: 4px;
        display: block;
    }
    
    @keyframes slideIn {
        from {
            transform: translateX(100%);
            opacity: 0;
        }
        to {
            transform: translateX(0);
            opacity: 1;
        }
    }
    </style>
</body>
</html>
