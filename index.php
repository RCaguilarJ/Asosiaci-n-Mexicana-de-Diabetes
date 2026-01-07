<?php
    require 'includes/check-session.php'; // Verificación de seguridad
    require 'includes/db.php'; // Conexión a BD

    $paginaActual = 'inicio';
    $tituloDeLaPagina = "Inicio - Asoc. Mexicana de Diabetes"; 
    
    // Obtener nombre del usuario (o "Invitado")
    $nombreUsuario = $_SESSION['usuario_nombre'] ?? 'Usuario';
    $esInvitado = isset($_SESSION['es_invitado']) && $_SESSION['es_invitado'];

    // VARIABLES PARA DATOS (Valores por defecto)
    $ultimaGlucosa = '--';
    $estadoGlucosaColor = '#9e9e9e'; // Gris por defecto
    $proximaCitaTexto = 'Sin citas próximas';
    $proximaCitaHora = '';

    // SI ES USUARIO REGISTRADO: Consultar la base de datos
    if (!$esInvitado && isset($_SESSION['usuario_id'])) {
        $userId = $_SESSION['usuario_id'];

        try {
            // 1. Obtener ÚLTIMA GLUCOSA registrada
            $stmtG = $pdo->prepare("SELECT nivel_glucosa FROM registros_glucosa WHERE usuario_id = ? ORDER BY fecha_registro DESC LIMIT 1");
            $stmtG->execute([$userId]);
            $glucosa = $stmtG->fetch(PDO::FETCH_ASSOC);

            if ($glucosa) {
                $ultimaGlucosa = $glucosa['nivel_glucosa'];
                // Color según nivel (simple)
                if($ultimaGlucosa > 180) $estadoGlucosaColor = '#dc3545'; // Rojo
                else if($ultimaGlucosa < 70) $estadoGlucosaColor = '#ffc107'; // Amarillo
                else $estadoGlucosaColor = '#28a745'; // Verde
            }

            // 2. Obtener PRÓXIMA CITA
            $stmtC = $pdo->prepare("SELECT fecha_cita, hora_cita, especialidad FROM citas WHERE usuario_id = ? AND fecha_cita >= CURDATE() ORDER BY fecha_cita ASC, hora_cita ASC LIMIT 1");
            $stmtC->execute([$userId]);
            $cita = $stmtC->fetch(PDO::FETCH_ASSOC);

            if ($cita) {
                // Formatear fecha (Ej: 14 Nov)
                setlocale(LC_TIME, 'es_ES.UTF-8', 'spanish');
                $fechaObj = new DateTime($cita['fecha_cita']);
                $proximaCitaTexto = $fechaObj->format('d/m/Y') . ' - ' . $cita['especialidad'];
                $proximaCitaHora = date('h:i A', strtotime($cita['hora_cita']));
            }

        } catch (Exception $e) {
            // Si hay error en BD, se queda con valores por defecto
            error_log("Error cargando datos dashboard: " . $e->getMessage());
        }
    }
?>
<!DOCTYPE html>
<html lang="es">
<?php include 'includes/head.php'; ?>
<body>

    <?php include 'includes/menu-drawer.php'; ?>
    <?php include 'includes/header.php'; ?>

    <section class="welcome-hero">
        <div class="welcome-hero-content">
            <div>
                <h1>Hola, <?php echo htmlspecialchars($nombreUsuario); ?></h1>
                <?php if($esInvitado): ?>
                    <p>Estás en modo invitado. Tus datos no se guardarán.</p>
                <?php else: ?>
                    <p>Aquí está el resumen de tu salud hoy.</p>
                <?php endif; ?>
            </div>
            <div class="welcome-icon">
                <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 14c1.49-1.46 3-3.21 3-5.5A5.5 5.5 0 0 0 16.5 3c-1.76 0-3 .5-4.5 2-1.5-1.5-2.74-2-4.5-2A5.5 5.5 0 0 0 2 8.5c0 2.3 1.5 4.05 3 5.5l7 7Z"/></svg>
            </div>
        </div>
    </section>

    <main class="contenedor">

        <?php include 'includes/check-guest-banner.php'; ?>

        <div class="status-cards-container">
            
            <div class="status-card">
                <div class="status-icon-container" style="background-color: #e3f2fd; color: #0d47a1;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 12h-2.48a2 2 0 0 0-1.93 1.46l-2.35 8.36a.25.25 0 0 1-.48 0L9.24 2.18a.25.25 0 0 0-.48 0l-2.35 8.36A2 2 0 0 1 4.49 12H2"/></svg>
                </div>
                <div class="status-text">
                    <span class="status-title">Última Glucosa</span>
                    <span class="status-value"><?php echo $ultimaGlucosa; ?> <small style="font-size:14px; font-weight:normal;">mg/dL</small></span>
                </div>
                <div class="status-bar" style="background-color: <?php echo $estadoGlucosaColor; ?>;"></div>
            </div>

            <div class="status-card">
                <div class="status-icon-container" style="background-color: #f3e5f5; color: #7b1fa2;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg>
                </div>
                <div class="status-text">
                    <span class="status-title">Próxima Cita</span>
                    <span class="status-value" style="font-size: 16px;"><?php echo $proximaCitaTexto; ?></span>
                    <?php if($proximaCitaHora): ?>
                        <small style="color:#666; display:block;"><?php echo $proximaCitaHora; ?></small>
                    <?php endif; ?>
                </div>
                <div class="status-bar" style="background-color: #9c27b0;"></div>
            </div>
        </div>

        <section class="quick-actions">
            <h2>Acciones Rápidas</h2>
            <div class="actions-grid">
                <a href="calculadora.php" class="action-button action-button--blue">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="16" height="20" x="4" y="2" rx="2"></rect><line x1="8" x2="16" y1="6" y2="6"></line><line x1="16" x2="16" y1="14" y2="18"></line><path d="M16 10h.01"></path><path d="M12 10h.01"></path><path d="M8 10h.01"></path><path d="M12 14h.01"></path><path d="M8 14h.01"></path><path d="M12 18h.01"></path><path d="M8 18h.01"></path></svg>
                    <span>Calculadora</span>
                </a>
                <a href="eventos.php" class="action-button action-button--eventos">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg>
                    <span>Eventos</span>
                </a>
                <a href="blog.php" class="action-button" style="color:#0097d8; border-color:#0097d8; background:#e0f7fa;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"></path><path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"></path></svg>
                    <span>Blog</span>
                </a>
                <a href="contacto.php" class="action-button action-button--contacto">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path></svg>
                    <span>Contacto</span>
                </a>
            </div>
        </section>

        <section class="nutrition-tip-card">
            <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 14h3a2 2 0 0 1 2 2v3a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2v-3a2 2 0 0 1 2-2z"></path><path d="M3 3h3a2 2 0 0 1 2 2v3a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2z"></path><path d="M14 14h3a2 2 0 0 1 2 2v3a2 2 0 0 1-2 2h-3a2 2 0 0 1-2-2v-3a2 2 0 0 1 2-2z"></path><path d="M14 3h3a2 2 0 0 1 2 2v3a2 2 0 0 1-2 2h-3a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2z"></path></svg>
            <h3>Tip Saludable del Día</h3>
            <p>Sustituir los jugos de fruta por la fruta entera te ayuda a consumir más fibra y controlar mejor tu nivel de glucosa.</p>
            <a href="blog.php" class="btn-ver-mas">Leer más consejos</a>
        </section>

    </main>
    <?php include 'includes/footer.php'; ?>
    <script src="assets/js/app.js"></script> 
</body>
</html>