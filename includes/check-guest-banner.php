<?php
require_once __DIR__ . '/base_path.php';
/**
 * Banner informativo para usuarios invitados o no logueados
 */

// Verificar si es usuario invitado o no está logueado
$esInvitado = isset($_SESSION['es_invitado']) && $_SESSION['es_invitado'];
$usuarioCompleto = isset($_SESSION['usuario_id']) && !empty($_SESSION['usuario_id']) && !$esInvitado;

if (!$usuarioCompleto): ?>
    <div class="guest-banner" style="
        background: linear-gradient(135deg, #007bff, #0056b3);
        color: white;
        padding: 15px 20px;
        border-radius: 15px;
        margin-bottom: 25px;
        text-align: center;
        box-shadow: 0 4px 12px rgba(0, 123, 255, 0.3);
    ">S
        <div style="display: flex; align-items: center; justify-content: center; gap: 12px; flex-wrap: wrap;">
            <div>
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M9 12l2 2 4-4"/>
                    <circle cx="12" cy="12" r="10"/>
                </svg>
            </div>
            <div style="flex-grow: 1; text-align: left; min-width: 200px;">
                <strong style="display: block; font-size: 16px; margin-bottom: 4px;">
                    <?php if ($esInvitado): ?>
                        ¡Sesión de Invitado Activa!
                    <?php else: ?>
                        ¡Crea tu cuenta gratuita!
                    <?php endif; ?>
                </strong>
                <small style="opacity: 0.9; font-size: 13px;">
                    <?php if ($esInvitado): ?>
                        Estás navegando como invitado. Los datos no se guardarán permanentemente.
                    <?php else: ?>
                        Registra tus datos de salud y accede a funciones personalizadas.
                    <?php endif; ?>
                </small>
            </div>
            <div>
                <a href="<?php echo $basePath; ?>/views/login.php" 
                   style="
                       background: rgba(255, 255, 255, 0.2);
                       color: white;
                       padding: 8px 16px;
                       border-radius: 20px;
                       text-decoration: none;
                       font-weight: 500;
                       font-size: 14px;
                       border: 1px solid rgba(255, 255, 255, 0.3);
                       transition: all 0.2s ease;
                   "
                   onmouseover="this.style.background='rgba(255,255,255,0.3)'"
                   onmouseout="this.style.background='rgba(255,255,255,0.2)'">
                    <?php if ($esInvitado): ?>
                        Crear Cuenta
                    <?php else: ?>
                        Iniciar Sesión
                    <?php endif; ?>
                </a>
            </div>
        </div>
    </div>
<?php endif; ?>
