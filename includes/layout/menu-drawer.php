<?php
    if (!isset($paginaActual)) { $paginaActual = ''; }
?>
<div class="menu-overlay"></div>

<nav class="offcanvas-menu">
    <div class="menu-drawer-header">
        <div class="menu-drawer-logo">
            <img src="../assets/img/logo.png" alt="Logo AMD Jalisco">
            <span>
                <strong>AMD Jalisco</strong>
                <small>Menú Principal</small>
            </span>
        </div>
        <button class="menu-close-btn" aria-label="Cerrar menú">X</button>
    </div>

    <ul class="menu-drawer-nav">
        <li>
            <a href="index.php" class="menu-drawer-link <?php echo ($paginaActual == 'inicio') ? 'active' : ''; ?>">
                <span class="menu-drawer-link__text">Inicio</span>
            </a>
        </li>
        <li>
            <a href="citas.php" class="menu-drawer-link <?php echo ($paginaActual == 'citas') ? 'active' : ''; ?>"> 
                <span class="menu-drawer-link__text">Agendar Cita</span>
            </a>
        </li>
        <?php if (isset($_SESSION['usuario_id'])): ?>
            <li>
                <a href="../actions/logout.php" class="menu-drawer-link"> 
                    <span class="menu-drawer-link__text">Cerrar Sesión</span>
                </a>
            </li>
        <?php else: ?>
            <li>
                <a href="login.php" class="menu-drawer-link <?php echo ($paginaActual == 'login') ? 'active' : ''; ?>"> 
                    <span class="menu-drawer-link__text">Acceso Usuarios</span>
                </a>
            </li>
        <?php endif; ?>
    </ul>
</nav>