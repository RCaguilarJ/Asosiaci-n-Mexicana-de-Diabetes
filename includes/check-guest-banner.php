<?php
/**
 * check-guest-banner.php
 * Muestra un banner si el usuario está en modo invitado
 * 
 * USO: Incluir después de check-session.php en las páginas donde se pueda guardar datos
 *      <?php include 'includes/check-guest-banner.php'; ?>
 */

if (isset($_SESSION['es_invitado']) && $_SESSION['es_invitado']) {
    echo '
    <div style="
        background: linear-gradient(135deg, #6c757d 0%, #5a6268 100%);
        color: white;
        padding: 15px 20px;
        border-radius: 8px;
        margin-bottom: 20px;
        display: flex;
        align-items: center;
        gap: 12px;
        border-left: 4px solid #fff;
    ">
        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <circle cx="12" cy="12" r="10"></circle>
            <line x1="12" y1="16" x2="12" y2="12"></line>
            <line x1="12" y1="8" x2="12.01" y2="8"></line>
        </svg>
        <div style="flex: 1;">
            <strong>Modo Invitado</strong> - Estás viendo una versión de prueba. Los datos que generes aquí <strong>no serán guardados</strong>. 
            <a href="login.php" style="color: #fff; text-decoration: underline; font-weight: bold;">Inicia sesión o crea una cuenta</a> para guardar tus datos.
        </div>
    </div>
    ';
}
?>
