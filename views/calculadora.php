<?php
    // VERIFICAR SESIÓN - El usuario debe estar logueado para acceder
    require '../includes/security/check-session.php';
    
    // 1. Definimos la página actual y el título
    $paginaActual = 'calculadora';
    $tituloDeLaPagina = "Calculadora - Asoc. Mexicana de Diabetes"; 
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
            <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="#FFFFFF" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="16" height="20" x="4" y="2" rx="2"></rect><line x1="8" x2="16" y1="6" y2="6"></line><line x1="16" x2="16" y1="14" y2="18"></line><path d="M16 10h.01"></path><path d="M12 10h.01"></path><path d="M8 10h.01"></path><path d="M12 14h.01"></path><path d="M8 14h.01"></path><path d="M12 18h.01"></path><path d="M8 18h.01"></path></svg>
        </div>
        <div class="page-header-text">
            <h1>Calculadora</h1>
            <p>Calcula tu dosis de insulina</p>
        </div>
        <div class="page-header-action">
            <a href="<?php echo $basePath; ?>/views/index.php" class="btn-back" style="color: white; text-decoration: none; padding: 8px; border: 1px solid rgba(255,255,255,0.3); border-radius: 50%; width: 40px; height: 40px; display: flex; align-items: center; justify-content: center;">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 12H5"></path><path d="M12 19l-7-7 7-7"></path></svg>
            </a>
        </div>
    </header>

    <main class="contenedor">

        <?php include '../includes/check-guest-banner.php'; ?>
    
        <form id="form-calculadora" class="calculadora-form">

            <fieldset class="card-form">
                <legend class="card-form-legend">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-activity w-6 h-6" aria-hidden="true" style="color: rgb(124, 179, 66);"><path d="M22 12h-2.48a2 2 0 0 0-1.93 1.46l-2.35 8.36a.25.25 0 0 1-.48 0L9.24 2.18a.25.25 0 0 0-.48 0l-2.35 8.36A2 2 0 0 1 4.49 12H2"></path></svg>
                    <span>Nivel de Glucosa</span>
                </legend>
                
                <div class="form-group">
                    <label for="glucosa">Glucosa (mg/DL)</label>
                    <input type="number" id="glucosa" name="glucosa" class="form-control" placeholder="120" required>
                </div>
                
                <div class="form-group">
                    <label for="momento">Momento de medición</label>
                    <select id="momento" name="momento" class="form-control" required>
                        <option value="" disabled selected>Selecciona el momento</option>
                        <option value="ayunas">En ayunas</option>
                        <option value="despues_comer">Después de comer (2h)</option>
                        <option value="antes_comer">Antes de comer</option>
                        <option value="antes_dormir">Antes de dormir</option>
                    </select>
                </div>
            </fieldset>

            <fieldset class="card-form">
                <legend class="card-form-legend">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-calculator w-7 h-7" aria-hidden="true" style="color: rgb(0, 102, 178);"><rect width="16" height="20" x="4" y="2" rx="2"></rect><line x1="8" x2="16" y1="6" y2="6"></line><line x1="16" x2="16" y1="14" y2="18"></line><path d="M16 10h.01"></path><path d="M12 10h.01"></path><path d="M8 10h.01"></path><path d="M12 14h.01"></path><path d="M8 14h.01"></path><path d="M12 18h.01"></path><path d="M8 18h.01"></path></svg>
                    <span>Calculadora de Insulina</span>
                </legend>
                
                <div class="form-group">
                    <label for="carbohidratos">Carbohidratos a consumir (g)</label>
                    <input type="number" id="carbohidratos" name="carbohidratos" class="form-control" placeholder="45" required>
                </div>
                
                <div class="form-group">
                    <label for="ratio">Ratio insulina:carbohidratos (1:X)</label>
                    <select id="ratio" name="ratio" class="form-control" required>
                        <option value="" disabled selected>Selecciona el ratio</option>
                        <option value="10">1:10</option>
                        <option value="12">1:12</option>
                        <option value="15">1:15</option>
                        <option value="20">1:20</option>
                    </select>
                </div>
            </fieldset>

            <button type="submit" id="btn-calcular" class="btn-calculadora" disabled>
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="16" height="20" x="4" y="2" rx="2"></rect><line x1="8" x2="16" y1="6" y2="6"></line><line x1="16" x2="16" y1="14" y2="18"></line><path d="M16 10h.01"></path><path d="M12 10h.01"></path><path d="M8 10h.01"></path><path d="M12 14h.01"></path><path d="M8 14h.01"></path><path d="M12 18h.01"></path><path d="M8 18h.01"></path></svg>
                <span>Calcular Resultados</span>
            </button>


            <section id="seccion-resultados" class="seccion-resultados oculto">
                
                <h3 class="titulo-resultados">Resultados del Análisis</h3>
                
                <div class="card-resultados">
                    
                    <div class="resultado-item">
                        <span class="resultado-label">IMC:</span>
                        <span id="resultado-imc" class="resultado-valor">-- kg/m²</span>
                    </div>

                    <div class="resultado-item">
                        <span class="resultado-label">Dosis de corrección:</span>
                        <span id="resultado-dosis-correccion" class="resultado-valor">-- unidades</span>
                    </div>

                    <div class="resultado-item">
                        <span class="resultado-label">Dosis por carbohidratos:</span>
                        <span id="resultado-dosis-carbs" class="resultado-valor">-- unidades</span>
                    </div>

                    <div class="resultado-item">
                        <span class="resultado-label">Dosis total sugerida:</span>
                        <span id="resultado-dosis-total" class="resultado-valor resultado-valor--dosis">-- unidades</span>
                    </div>

                    <div class="alert-status">
                        <div class="alert-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-circle-alert w-5 h-5 mt-0.5" aria-hidden="true" style="color: #f59e0b;"><circle cx="12" cy="12" r="10"></circle><line x1="12" x2="12" y1="8" y2="12"></line><line x1="12" x2="12.01" y1="16" y2="16"></line></svg>
                        </div>
                        <div class="alert-text">
                            <p id="estado-glucosa">Los resultados son orientativos. Consulte con su médico.</p>
                        </div>
                    </div>

                    <button type="button" class="btn-guardar">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path><polyline points="17 21 17 13 7 13 7 21"></polyline><polyline points="7 3 7 8 15 8"></polyline></svg>
                        <span>Guardar en Historial</span>
                    </button>

                </div>
            </section>
            <div class="alert-importante">
                <div class="alert-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-circle-alert w-5 h-5 mt-0.5" aria-hidden="true" style="color: #d9534f;"><circle cx="12" cy="12" r="10"></circle><line x1="12" x2="12" y1="8" y2="12"></line><line x1="12" x2="12.01" y1="16" y2="16"></line></svg>
                </div>
                <div class="alert-text">
                    <strong>Importante:</strong>
                    <p>Esta calculadora es solo una herramienta de apoyo. Siempre consulta con tu médico antes de hacer cambios en tu tratamiento.</p>
                </div>
            </div>

        </form>

    </main>
    <?php 
        // 5. Incluimos el pie de página
        include '../includes/layout/footer.php'; 
    ?>

    <script src="<?php echo $basePath; ?>/assets/js/app.js"></script> 
</body>
</html>
