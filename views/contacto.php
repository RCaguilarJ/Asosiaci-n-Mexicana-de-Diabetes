<?php
    // VERIFICAR SESIÓN - El usuario debe estar logueado para acceder
    require '../includes/security/check-session.php';
    
    // 1. Definimos la página actual y el título
    $paginaActual = 'contacto';
    $tituloDeLaPagina = "Contacto - Asoc. Mexicana de Diabetes"; 
?>
<!DOCTYPE html>
<html lang="es">

<?php 
    // 2. Incluimos el <head>
    include '../includes/layout/head.php'; 
?>

<body>

    <?php 
        // 3. Incluimos el menú deslizante
        include '../includes/layout/menu-drawer.php'; 
    ?>

    <?php 
        // 4. Incluimos el header (barra superior)
        include '../includes/layout/header.php'; 
    ?>

    <header class="page-header">
        <div class="page-header-icon">
            <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 18a2 2 0 0 0-2-2H9a2 2 0 0 0-2 2"></path><rect width="18" height="18" x="3" y="4" rx="2"></rect><circle cx="12" cy="10" r="2"></circle><line x1="8" x2="8" y1="2" y2="4"></line><line x1="16" x2="16" y1="2" y2="4"></line></svg>
        </div>
        <div class="page-header-text">
            <h1>Contacto</h1>
            <p>Estamos aquí para ayudarte</p>
        </div>
        <div class="page-header-action">
            <a href="<?php echo $basePath; ?>/views/index.php" class="btn-back" style="color: white; text-decoration: none; padding: 8px; border: 1px solid rgba(255,255,255,0.3); border-radius: 50%; width: 40px; height: 40px; display: flex; align-items: center; justify-content: center;">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 12H5"></path><path d="M12 19l-7-7 7-7"></path></svg>
            </a>
        </div>
    </header>

    <main class="contenedor">

        <section class="info-grid">
            <div class="info-card">
                <h3>Teléfono</h3>
                <a href="tel:3387654321">33 1234 5678</a>
                <a href="tel:3387654321">33 8765 4321</a>
            </div>
            <div class="info-card">
                <h3>Email</h3>
                <a href="mailto:contacto@amdjalisco.org">contacto@amdjalisco.org</a>
                <a href="mailto:info@amdjalisco.org">info@amdjalisco.org</a>
            </div>
            <div class="info-card">
                <h3>Dirección</h3>
                <p>Av. Américas 1500<br>Guadalajara, Jalisco</p>
            </div>
            <div class="info-card">
                <h3>Horario</h3>
                <p>Lun-Vie: 8:00 AM - 6:00 PM</p>
                <p>Sáb: 9:00 AM - 2:00 PM</p>
            </div>
        </section>

        <section class="social-section">
            <h2>Suíguenos en Redes Sociales</h2>
            <div class="social-links">
                <a href="https://www.facebook.com/AMDJalisco/" class="social-btn social-btn--facebook" aria-label="Suíguenos en Facebook">
                    <span>Facebook</span>
                </a>
                <a href="https://www.youtube.com/channel/UCFeT3xQ6-jZj24IQZfKebvQ" class="social-btn social-btn--instagram" aria-label="Youtube">
                    <span>Youtube</span>
                </a>
                <a href="https://api.whatsapp.com/send/?phone=5213319255036&text=Hola+%2AAsociaci%C3%B3n+Mexicana+de+Diabetes+en+Jalisco+A.C.%2A.+Necesito+m%C3%A1s+informaci%C3%B3n+sobre+Contacto+https%3A%2F%2Fdiabetesjalisco.org%2Fcontacto%2F&type=phone_number&app_absent=0" class="social-btn social-btn--whatsapp" aria-label="Contáctanos por WhatsApp">
                    <span>WhatsApp</span>
                </a>
            </div>
        </section>

        <form class="card-form" id="contact-form">
            <legend class="card-form-legend">
                <span>Envíanos Un Mensaje</span>
            </legend>
            
            <div class="form-group">
                <label for="nombre">Nombre Completo</label>
                <input type="text" id="nombre" name="nombre" class="form-control" placeholder="Juan Perez García" required>
            </div>

            <div class="contact-form-grid">
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" class="form-control" placeholder="correo@ejemplo.com" required>
                </div>
                <div class="form-group">
                    <label for="telefono">Teléfono</label>
                    <input type="tel" id="telefono" name="telefono" class="form-control" placeholder="33 1234 5678">
                </div>
            </div>

            <div class="form-group">
                <label for="asunto">Asunto</label>
                <input type="text" id="asunto" name="asunto" class="form-control" placeholder="¿En qué podemos ayudarte?" required>
            </div>

            <div class="form-group">
                <label for="mensaje">Mensaje</label>
                <textarea id="mensaje" name="mensaje" class="form-control" rows="5" placeholder="Escribe tu mensaje aquí..." required></textarea>
            </div>
            
            <button type="submit" class="btn-calculadora">
                <span>Enviar Mensaje</span>
            </button>
        </form>

        <section class="map-card">
            <h3>AMD Jalisco</h3>
            <p>Olivo 1439, Del Fresno<br>44900 Guadalajara, Jal.</p>
            <div class="map-embed" aria-label="Mapa de Olivo 1439, Del Fresno">
                <iframe
                    src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3733.358046677717!2d-103.36842982475356!3d20.6550073809025!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x8428adfa36461887%3A0x989929a583be2c58!2sOlivo%201439%2C%20Del%20Fresno%2C%2044900%20Guadalajara%2C%20Jal.!5e0!3m2!1ses-419!2smx!4v1773099051161!5m2!1ses-419!2smx"
                    width="600"
                    height="260"
                    style="border:0;"
                    allowfullscreen=""
                    loading="lazy"
                    referrerpolicy="no-referrer-when-downgrade"
                    title="Mapa Olivo 1439, Del Fresno"></iframe>
            </div>
            <a href="https://www.google.com/maps/place/Olivo+1439,+Del+Fresno,+44900+Guadalajara,+Jal./@20.6550074,-103.3684298,17z/data=!3m1!4b1!4m6!3m5!1s0x8428adfa36461887:0x989929a583be2c58!8m2!3d20.6550074!4d-103.3658549!16s%2Fg%2F11c1v8wrc5?entry=ttu&g_ep=EgoyMDI2MDMwNS4wIKXMDSoASAFQAw%3D%3D" class="btn-maps" target="_blank" rel="noopener">Abrir en Maps</a>
        </section>

        <section class="emergency-card">
            <h3>Línea de Emergencias</h3>
            <p>Disponible 24/7 para urgencias médicas</p>
            <a href="tel:911" class="btn-emergency">911</a>
        </section>

    </main>
    <?php 
        // 5. Incluimos el pie de página
        include '../includes/layout/footer.php'; 
    ?>

    <script src="<?php echo $basePath; ?>/assets/js/app.js"></script> 
</body>
</html>



