<?php require_once __DIR__ . '/../base_path.php'; ?>
<header class="site-header">
    <div class="header-contenido">
        
        <button class="menu-btn" aria-label="Abrir menú" aria-expanded="false">
            <span></span>
            <span></span>
            <span></span>
        </button>

        <div class="logo">
            <img src="<?php echo $basePath; ?>/assets/img/logo.png" alt="Logo de la Asociación Mexicana de Diabetes">
        </div>
    </div>

    <nav class="navegacion-principal">
        <a href="index.php" class="<?php echo (isset($paginaActual) && $paginaActual === 'inicio') ? 'activo' : ''; ?>">Inicio</a>
        <a href="blog.php" class="<?php echo (isset($paginaActual) && $paginaActual === 'blog') ? 'activo' : ''; ?>">Blog</a>
        <a href="eventos.php" class="<?php echo (isset($paginaActual) && $paginaActual === 'eventos') ? 'activo' : ''; ?>">Eventos</a>
        <a href="citas.php" class="<?php echo (isset($paginaActual) && $paginaActual === 'citas') ? 'activo' : ''; ?>">Agendar Cita</a>
        <a href="contacto.php" class="<?php echo (isset($paginaActual) && $paginaActual === 'contacto') ? 'activo' : ''; ?>">Contacto</a>
        <a href="perfil.php" class="<?php echo (isset($paginaActual) && $paginaActual === 'perfil') ? 'activo' : ''; ?>">Mi Perfil</a>
    </nav>

</header>
