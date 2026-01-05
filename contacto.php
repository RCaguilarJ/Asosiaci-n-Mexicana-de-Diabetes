<?php
    // VERIFICAR SESIÓN - El usuario debe estar logueado para acceder
    require 'includes/check-session.php';
    
    // 1. Definimos la página actual y el título
    $paginaActual = 'contacto';
    $tituloDeLaPagina = "Contacto - Asoc. Mexicana de Diabetes"; 
?>
<!DOCTYPE html>
<html lang="es">

<?php 
    // 2. Incluimos el <head>
    include 'includes/head.php'; 
?>

<body>

    <?php 
        // 3. Incluimos el menú deslizante
        include 'includes/menu-drawer.php'; 
    ?>

    <?php 
        // 4. Incluimos el header (barra superior)
        include 'includes/header.php'; 
    ?>

    <header class="page-header">
        <div class="page-header-icon">
            <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 18a2 2 0 0 0-2-2H9a2 2 0 0 0-2 2"></path><rect width="18" height="18" x="3" y="4" rx="2"></rect><circle cx="12" cy="10" r="2"></circle><line x1="8" x2="8" y1="2" y2="4"></line><line x1="16" x2="16" y1="2" y2="4"></line></svg>
        </div>
        <div class="page-header-text">
            <h1>Contacto</h1>
            <p>Estamos aquí para ayudarte</p>
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
            <h2>Síguenos en Redes Sociales</h2>
            <div class="social-links">
                <a href="#" class="social-btn social-btn--facebook">
                    <span>Facebook</span>
                </a>
                <a href="#" class="social-btn social-btn--instagram">
                    <span>Instagram</span>
                </a>
                <a href="#" class="social-btn social-btn--whatsapp">
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
                <input type="text" id="nombre" name="nombre" class="form-control" placeholder="Juan Pérez García" required>
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
            <p>Av. Américas 1500<br>Guadalajara, Jalisco</p>
            <a href="#" class="btn-maps">Abrir en Maps</a>
        </section>

        <section class="emergency-card">
            <h3>Línea de Emergencias</h3>
            <p>Disponible 24/7 para urgencias médicas</p>
            <a href="tel:911" class="btn-emergency">911</a>
        </section>

    </main>
    <?php 
        // 5. Incluimos el pie de página
        include 'includes/footer.php'; 
    ?>

    <script src="assets/js/app.js"></script> 
</body>
</html>