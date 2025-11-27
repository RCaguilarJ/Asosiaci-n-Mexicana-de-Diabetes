<?php
    $paginaActual = 'blog';
    $tituloDeLaPagina = "Blog Educativo - Asoc. Mexicana de Diabetes"; 

    // ===========================================================
    // DATOS MANUALES (Para evitar el bloqueo de WAMP)
    // ===========================================================
    
    $articulos = [
        [
            'titulo' => '10 Alimentos Esenciales para Controlar la Diabetes',
            'desc'   => 'Descubre qué alimentos pueden ayudarte a mantener niveles de glucosa estables y mejorar tu salud general.',
            'autor'  => 'Dr. María González',
            'fecha'  => '20 Oct 2025',
            'imagen' => 'assets/images/platilloSano.jpg', // Usamos tu imagen local
            'link'   => '#', // Enlace simulado
            'tag'    => 'Nutrición',
            'tag_clase' => 'event-tag--educativo'
        ],
        [
            'titulo' => 'Ejercicio y Diabetes: Guía Completa',
            'desc'   => 'Aprende cómo el ejercicio regular puede mejorar significativamente el control de la diabetes.',
            'autor'  => 'Equipo Médico',
            'fecha'  => '18 Oct 2025',
            'imagen' => 'assets/images/medidorInsulina.jpg',
            'link'   => '#',
            'tag'    => 'Actividad Física',
            'tag_clase' => 'event-tag--actividad-blue'
        ],
        [
            'titulo' => 'Monitoreo Continuo de Glucosa: Tecnología Actual',
            'desc'   => 'Conoce las últimas tecnologías disponibles para el monitoreo continuo y cómo pueden mejorar tu control.',
            'autor'  => 'Ing. Juan Pérez',
            'fecha'  => '15 Oct 2025',
            'imagen' => 'assets/images/medidorInsulina.jpg',
            'link'   => '#',
            'tag'    => 'Tecnología',
            'tag_clase' => 'event-tag--tecnologia'
        ],
        [
            'titulo' => 'Manejo del Estrés y Diabetes',
            'desc'   => 'El estrés puede afectar tus niveles de glucosa. Aprende técnicas efectivas para manejarlo.',
            'autor'  => 'Psic. Ana López',
            'fecha'  => '12 Oct 2025',
            'imagen' => 'assets/images/platilloSano.jpg',
            'link'   => '#',
            'tag'    => 'Bienestar',
            'tag_clase' => 'event-tag--bienestar'
        ]
    ];

    // Lógica de separación
    $destacado = $articulos[0];
    $recientes = array_slice($articulos, 1); 
?>

<!DOCTYPE html>
<html lang="es">

<?php include 'includes/head.php'; ?>

<body>

    <?php include 'includes/menu-drawer.php'; ?>
    <?php include 'includes/header.php'; ?>

    <header class="page-header">
        <div class="page-header-icon">
            <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="#FFFFFF" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"></path><path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"></path></svg>
        </div>
        <div class="page-header-text">
            <h1>Blog Educativo</h1>
            <p>Información y consejos</p>
        </div>
    </header>

    <div class="category-scroll-container">
        <div class="category-filter">
            <button class="cat-btn active">Todos</button>
            <button class="cat-btn">Nutrición</button>
            <button class="cat-btn">Actividad Física</button>
            <button class="cat-btn">Tecnología</button>
            <button class="cat-btn">Bienestar</button>
        </div>
    </div>

    <main class="contenedor">

        <article class="featured-post card-base p-0 overflow-hidden">
            <div class="featured-image-container">
                <img src="<?php echo $destacado['imagen']; ?>" alt="<?php echo $destacado['titulo']; ?>" class="featured-img">
            </div>
            <div class="featured-content p-20">
                <div class="tags-container">
                    <span class="event-tag <?php echo $destacado['tag_clase']; ?>"><?php echo $destacado['tag']; ?></span>
                    <span class="event-tag event-tag--destacado">Destacado</span>
                </div>
                <h3><?php echo $destacado['titulo']; ?></h3>
                <p><?php echo $destacado['desc']; ?></p>
                
                <div class="post-meta">
                    <span class="meta-item">
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
                        <?php echo $destacado['autor']; ?>
                    </span>
                    <span class="meta-item">
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
                        5 min
                    </span>
                    <span class="meta-date"><?php echo $destacado['fecha']; ?></span>
                </div>
            </div>
        </article>

        <h3 class="section-subtitle mt-30">Artículos Recientes</h3>
        
        <div class="blog-recent-list">
            
            <?php foreach($recientes as $post): ?>
            <article class="blog-card-horizontal card-base p-0 overflow-hidden">
                <div class="horizontal-img-container">
                    <img src="<?php echo $post['imagen']; ?>" alt="<?php echo $post['titulo']; ?>">
                </div>
                <div class="horizontal-content">
                    <span class="event-tag <?php echo $post['tag_clase']; ?>"><?php echo $post['tag']; ?></span>
                    
                    <h4><?php echo $post['titulo']; ?></h4>
                    <p><?php echo $post['desc']; ?></p>
                    
                    <div class="post-meta-simple">
                        <span>
                            <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg> 
                            <?php echo mt_rand(3, 8); ?> min
                        </span>
                        <span><?php echo $post['fecha']; ?></span>
                    </div>
                </div>
            </article>
            <?php endforeach; ?>

        </div>

        <section class="newsletter-card mt-30">
            <div class="newsletter-icon">
                <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"></path><path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"></path></svg>
            </div>
            <h3>Mantente Informado</h3>
            <p>Recibe los últimos artículos y consejos directamente en tu correo</p>
            <button class="btn-suscribir">Suscribirse</button>
        </section>

    </main>

    <?php include 'includes/footer.php'; ?>
    <script src="assets/js/app.js"></script> 
</body>
</html>