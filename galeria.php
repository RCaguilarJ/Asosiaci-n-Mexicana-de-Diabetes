<?php
    // VERIFICAR SESIÓN - El usuario debe estar logueado para acceder
    require 'includes/check-session.php';
    
    $paginaActual = 'galeria'; // Recuerda actualizar esto en menu-drawer.php si quieres que se ilumine
    $tituloDeLaPagina = "Galería de Videos - Asoc. Mexicana de Diabetes"; 

    // ===========================================================
    // DATOS DE LOS VIDEOS (Manual)
    // ===========================================================
    // Solo necesitas el ID del video de YouTube (lo que va después de v=)
    
    $videos = [
        [
            'id' => 'TnweCbhzrQQ', // ID real de un video de AMDJalisco
            'titulo' => 'Introducción a la Diabetes Tipo 2',
            'desc' => 'Conoce los conceptos básicos sobre la diabetes tipo 2 y cómo manejarla efectivamente.',
            'categoria' => 'Prevención',
            'clase_cat' => 'cat-prevencion',
            'duracion' => '15:30',
            'vistas' => '12.5k'
        ],
        [
            'id' => 'XOE3QHKWIAA', 
            'titulo' => 'Plan de Alimentación para Diabéticos',
            'desc' => 'Aprende a crear un plan de alimentación balanceado que te ayude a controlar tu glucosa.',
            'categoria' => 'Nutrición',
            'clase_cat' => 'cat-nutricion',
            'duracion' => '22:15',
            'vistas' => '18.2k'
        ],
        [
            'id' => '1lwhlYtsrwo', 
            'titulo' => 'Ejercicios Recomendados',
            'desc' => 'Rutina de ejercicios seguros y efectivos para personas con diabetes.',
            'categoria' => 'Ejercicio',
            'clase_cat' => 'cat-ejercicio',
            'duracion' => '10:45',
            'vistas' => '9.8k'
        ],
        [
            'id' => '0JXKRapCUWo', 
            'titulo' => 'Cómo Aplicar Insulina Correctamente',
            'desc' => 'Guía paso a paso sobre la técnica correcta de aplicación de insulina.',
            'categoria' => 'Medicamentos',
            'clase_cat' => 'cat-medicamentos',
            'duracion' => '12:20',
            'vistas' => '15.6k'
        ],
        [
            'id' => 'QtbuwCIjoYA', 
            'titulo' => 'Monitoreo de Glucosa en Casa',
            'desc' => 'Aprende a usar correctamente tu glucómetro y llevar un registro adecuado.',
            'categoria' => 'Prevención',
            'clase_cat' => 'cat-prevencion',
            'duracion' => '14:55',
            'vistas' => '11.3k'
        ]
    ];
?>
<!DOCTYPE html>
<html lang="es">

<?php include 'includes/head.php'; ?>

<body>

    <?php include 'includes/menu-drawer.php'; ?>
    <?php include 'includes/header.php'; ?>

    <header class="page-header page-header--cyan">
        <div class="page-header-icon">
            <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="#FFFFFF" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M23 7l-7 5 7 5V7z"></path><rect x="1" y="5" width="15" height="14" rx="2" ry="2"></rect></svg>
        </div>
        <div class="page-header-text">
            <h1>Galería de Videos</h1>
            <p>Contenido educativo sobre diabetes</p>
        </div>
    </header>

    <div class="category-scroll-container">
        <div class="category-filter">
            <button class="cat-btn active">Todos</button>
            <button class="cat-btn">Nutrición</button>
            <button class="cat-btn">Ejercicio</button>
            <button class="cat-btn">Medicamentos</button>
            <button class="cat-btn">Prevención</button>
        </div>
    </div>

    <main class="contenedor">

        <div class="video-list">
            
            <?php foreach($videos as $video): ?>
            <article class="video-card">
                
                <div class="video-thumbnail-container">
                    <img src="https://img.youtube.com/vi/<?php echo $video['id']; ?>/hqdefault.jpg" alt="<?php echo $video['titulo']; ?>" class="video-thumb">
                    
                    <span class="video-cat-badge <?php echo $video['clase_cat']; ?>">
                        <?php echo $video['categoria']; ?>
                    </span>

                    <div class="play-overlay">
                        <div class="play-circle">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="white" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="5 3 19 12 5 21 5 3"></polygon></svg>
                        </div>
                    </div>

                    <span class="duration-badge">
                        <svg xmlns="http://www.w3.org/2000/svg" width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg> 
                        <?php echo $video['duracion']; ?>
                    </span>
                </div>

                <div class="video-content">
                    <h3>
                        <a href="https://www.youtube.com/watch?v=<?php echo $video['id']; ?>" target="_blank">
                            <?php echo $video['titulo']; ?>
                        </a>
                    </h3>
                    <p><?php echo $video['desc']; ?></p>
                    
                    <div class="video-footer">
                        <span class="video-views">
                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                            <?php echo $video['vistas']; ?> vistas
                        </span>
                        
                        <a href="https://www.youtube.com/watch?v=<?php echo $video['id']; ?>" target="_blank" class="btn-ver-video">
                            Ver ahora
                        </a>
                    </div>
                </div>

            </article>
            <?php endforeach; ?>

        </div>

    </main>

    <?php include 'includes/footer.php'; ?>
    <script src="assets/js/app.js"></script> 
</body>
</html>