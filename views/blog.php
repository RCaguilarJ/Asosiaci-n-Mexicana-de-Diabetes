<?php
    // VERIFICAR SESIÓN - El usuario debe estar logueado para acceder
    require '../includes/security/check-session.php';
    
    $paginaActual = 'blog';
    $tituloDeLaPagina = "Blog Educativo - Asoc. Mexicana de Diabetes"; 
    $feedUrl = 'https://app.desingsgdl.app/feed/';
    $imagenPorDefecto = $basePath . '/assets/img/platilloSano.jpg';

    /**
     * Descarga el contenido remoto usando cURL con fallback a file_get_contents.
     */
    function obtenerContenidoRemoto($url) {
        if (function_exists('curl_init')) {
            $ch = curl_init($url);
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_CONNECTTIMEOUT => 5,
                CURLOPT_TIMEOUT => 8,
                CURLOPT_USERAGENT => 'AMD-Jalisco/1.0 (+https://app.desingsgdl.app)'
            ]);
            $data = curl_exec($ch);
            $error = curl_errno($ch);
            curl_close($ch);
            if ($error === 0 && $data) {
                return $data;
            }
        }
        return @file_get_contents($url);
    }

    function limpiarTextoBlog($texto) {
        $decoded = html_entity_decode($texto ?? '', ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $sinEtiquetas = strip_tags($decoded);
        return trim(preg_replace('/\s+/', ' ', $sinEtiquetas));
    }

    function extraerImagenDelContenido($html) {
        if (!$html) {
            return null;
        }
        if (preg_match('/<img[^>]+src=["\']([^"\']+)["\']/i', $html, $matches)) {
            return $matches[1];
        }
        return null;
    }

    function claseSegunCategoria($categoria) {
        $map = [
            'taller automonitoreo' => 'event-tag--actividad-blue',
            'diplomado m5' => 'event-tag--tecnologia',
            'blog' => 'event-tag--bienestar',
        ];
        $clave = mb_strtolower($categoria ?? '', 'UTF-8');
        return $map[$clave] ?? 'event-tag--educativo';
    }

    function calcularTiempoLectura($texto) {
        $palabras = str_word_count($texto);
        return max(3, min(12, (int) ceil($palabras / 40)));
    }

    function obtenerArticulosDelFeed($url, $limite, $imagenFallback) {
        $xmlString = obtenerContenidoRemoto($url);
        if (!$xmlString) {
            return [];
        }

        libxml_use_internal_errors(true);
        $xml = simplexml_load_string($xmlString, 'SimpleXMLElement', LIBXML_NOCDATA);
        libxml_clear_errors();
        libxml_use_internal_errors(false);

        if (!$xml || !isset($xml->channel->item)) {
            return [];
        }

        $articulos = [];
        foreach ($xml->channel->item as $item) {
            $categoria = (string) ($item->category ?? '');
            $contenido = (string) $item->children('content', true)->encoded;
            $descripcion = (string) $item->description;
            $texto = limpiarTextoBlog($descripcion) ?: limpiarTextoBlog($contenido);
            $imagen = extraerImagenDelContenido($contenido) ?: $imagenFallback;

            $articulos[] = [
                'titulo' => limpiarTextoBlog((string) $item->title),
                'desc' => $texto,
                'autor' => limpiarTextoBlog((string) $item->children('dc', true)->creator ?: 'AMD Jalisco'),
                'fecha' => date('d M Y', strtotime((string) $item->pubDate)),
                'imagen' => $imagen,
                'link' => (string) $item->link ?: '#',
                'tag' => limpiarTextoBlog($categoria ?: 'Blog'),
                'tag_clase' => claseSegunCategoria($categoria),
                'lectura' => calcularTiempoLectura($texto)
            ];

            if (count($articulos) >= $limite) {
                break;
            }
        }

        return $articulos;
    }

    function obtenerCategoriasDeArticulos($articulos) {
        $unicos = [];
        foreach ($articulos as $articulo) {
            $nombre = trim($articulo['tag'] ?? '');
            if ($nombre === '') {
                continue;
            }
            $clave = mb_strtolower($nombre, 'UTF-8');
            if (!isset($unicos[$clave])) {
                $unicos[$clave] = $nombre;
            }
        }
        if (empty($unicos)) {
            return ['Nutrición', 'Actividad Física', 'Tecnología', 'Bienestar'];
        }
        return array_values($unicos);
    }

    $articulos = obtenerArticulosDelFeed($feedUrl, 6, $imagenPorDefecto);

    if (empty($articulos)) {
        // Fallback local en caso de no poder obtener el feed
        $articulos = [
            [
                'titulo' => '10 Alimentos Esenciales para Controlar la Diabetes',
                'desc'   => 'Descubre qué alimentos pueden ayudarte a mantener niveles de glucosa estables y mejorar tu salud general.',
                'autor'  => 'Dr. María González',
                'fecha'  => '20 Oct 2025',
                'imagen' => $imagenPorDefecto,
                'link'   => '#',
                'tag'    => 'Nutrición',
                'tag_clase' => 'event-tag--educativo',
                'lectura' => 5
            ],
            [
                'titulo' => 'Ejercicio y Diabetes: Guía Completa',
                'desc'   => 'Aprende cómo el ejercicio regular puede mejorar significativamente el control de la diabetes.',
                'autor'  => 'Equipo Médico',
                'fecha'  => '18 Oct 2025',
                'imagen' => $basePath . '/assets/img/medidorInsulina.jpg',
                'link'   => '#',
                'tag'    => 'Actividad Física',
                'tag_clase' => 'event-tag--actividad-blue',
                'lectura' => 6
            ],
            [
                'titulo' => 'Monitoreo Continuo de Glucosa: Tecnología Actual',
                'desc'   => 'Conoce las últimas tecnologías disponibles para el monitoreo continuo y cómo pueden mejorar tu control.',
                'autor'  => 'Ing. Juan Pérez',
                'fecha'  => '15 Oct 2025',
                'imagen' => $basePath . '/assets/img/medidorInsulina.jpg',
                'link'   => '#',
                'tag'    => 'Tecnología',
                'tag_clase' => 'event-tag--tecnologia',
                'lectura' => 5
            ],
            [
                'titulo' => 'Manejo del Estrés y Diabetes',
                'desc'   => 'El estrés puede afectar tus niveles de glucosa. Aprende técnicas efectivas para manejarlo.',
                'autor'  => 'Psic. Ana López',
                'fecha'  => '12 Oct 2025',
                'imagen' => $imagenPorDefecto,
                'link'   => '#',
                'tag'    => 'Bienestar',
                'tag_clase' => 'event-tag--bienestar',
                'lectura' => 4
            ]
        ];
    }

    $destacado = $articulos[0] ?? null;
    $recientes = array_slice($articulos, 1);
    $categorias = obtenerCategoriasDeArticulos($articulos);
    $totalArticulos = count($articulos);
    $totalCategorias = count($categorias);
?>

<!DOCTYPE html>
<html lang="es">

<?php include '../includes/layout/head.php'; ?>

<body>

    <?php include '../includes/layout/menu-drawer.php'; ?>
    <?php include '../includes/layout/header.php'; ?>

    <section class="blog-hero">
        <div class="blog-hero-inner">
            <div class="blog-hero__content">
                <span class="blog-hero__label">Blog Educativo</span>
                <h1>Información y consejos actualizados</h1>
                <p>Explora los artículos más recientes publicados por la Asociación Mexicana de Diabetes en Jalisco. Resumimos cada tema para que puedas tomar acciones informadas hoy mismo.</p>

                <div class="blog-hero__stats">
                    <div class="hero-stat">
                        <strong><?php echo $totalArticulos; ?></strong>
                        <span>Entradas recientes</span>
                    </div>
                    <div class="hero-stat">
                        <strong><?php echo $totalCategorias; ?></strong>
                        <span>Categorías oficiales</span>
                    </div>
                </div>

                <div class="blog-hero__actions">
                    <a href="<?php echo $basePath; ?>/views/index.php" class="btn-back" style="color: #0066b2; text-decoration: none; padding: 10px; border: 1px solid #0066b2; border-radius: 50%; width: 45px; height: 45px; display: flex; align-items: center; justify-content: center; margin-right: 15px; background: white;">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 12H5"></path><path d="M12 19l-7-7 7-7"></path></svg>
                    </a>
                    <a class="btn-hero" href="https://app.desingsgdl.app" target="_blank" rel="noopener">
                        Visitar sitio oficial
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 13v6a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"></path><polyline points="15 3 21 3 21 9"></polyline><line x1="10" y1="14" x2="21" y2="3"></line></svg>
                    </a>
                </div>
            </div>
            <div class="blog-hero__figure">
                <div class="hero-illustration">
                    <img src="<?php echo $basePath; ?>/assets/img/medidorInsulina.jpg" alt="Personas aprendiendo sobre diabetes">
                </div>
            </div>
        </div>
    </section>

    <section class="category-scroll-container">
        <div class="category-filter">
            <button class="cat-btn active">Todos</button>
            <?php foreach ($categorias as $categoria): ?>
                <button class="cat-btn"><?php echo htmlspecialchars($categoria); ?></button>
            <?php endforeach; ?>
        </div>
    </section>

    <main class="contenedor">

        <?php if ($destacado): ?>
            <article class="featured-post card-base p-0 overflow-hidden">
                <div class="featured-image-container">
                    <a href="<?php echo htmlspecialchars($destacado['link']); ?>" target="_blank" rel="noopener">
                        <img src="<?php echo htmlspecialchars($destacado['imagen']); ?>" alt="<?php echo htmlspecialchars($destacado['titulo']); ?>" class="featured-img">
                    </a>
                </div>
                <div class="featured-content p-20">
                    <div class="tags-container">
                        <span class="event-tag <?php echo htmlspecialchars($destacado['tag_clase']); ?>"><?php echo htmlspecialchars($destacado['tag']); ?></span>
                        <span class="event-tag event-tag--destacado">Destacado</span>
                    </div>
                    <h3>
                        <a href="<?php echo htmlspecialchars($destacado['link']); ?>" target="_blank" rel="noopener">
                            <?php echo htmlspecialchars($destacado['titulo']); ?>
                        </a>
                    </h3>
                    <p><?php echo htmlspecialchars($destacado['desc']); ?></p>
                    
                    <div class="post-meta">
                        <span class="meta-item">
                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
                            <?php echo htmlspecialchars($destacado['autor']); ?>
                        </span>
                        <span class="meta-item">
                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
                            <?php echo (int) $destacado['lectura']; ?> min
                        </span>
                        <span class="meta-date"><?php echo htmlspecialchars($destacado['fecha']); ?></span>
                    </div>
                </div>
            </article>
        <?php else: ?>
            <p class="texto-suave">No pudimos cargar el blog en este momento. Por favor vuelve a intentarlo más tarde.</p>
        <?php endif; ?>

        <h3 class="section-subtitle mt-30">Artículos Recientes</h3>
        
        <div class="blog-recent-list">
            
            <?php if ($recientes): ?>
                <?php foreach($recientes as $post): ?>
                    <article class="blog-card-horizontal card-base p-0 overflow-hidden">
                        <a href="<?php echo htmlspecialchars($post['link']); ?>" target="_blank" rel="noopener" class="horizontal-img-container">
                            <img src="<?php echo htmlspecialchars($post['imagen'] ?: $imagenPorDefecto); ?>" alt="<?php echo htmlspecialchars($post['titulo']); ?>">
                        </a>
                        <div class="horizontal-content">
                            <span class="event-tag <?php echo htmlspecialchars($post['tag_clase']); ?>"><?php echo htmlspecialchars($post['tag']); ?></span>
                            
                            <h4>
                                <a href="<?php echo htmlspecialchars($post['link']); ?>" target="_blank" rel="noopener">
                                    <?php echo htmlspecialchars($post['titulo']); ?>
                                </a>
                            </h4>
                            <p><?php echo htmlspecialchars($post['desc']); ?></p>
                            
                            <div class="post-meta-simple">
                                <span>
                                    <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg> 
                                    <?php echo (int) $post['lectura']; ?> min
                                </span>
                                <span><?php echo htmlspecialchars($post['fecha']); ?></span>
                            </div>
                        </div>
                    </article>
                <?php endforeach; ?>
            <?php else: ?>
                <p class="texto-suave">No hay artículos recientes para mostrar.</p>
            <?php endif; ?>

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

    <?php include '../includes/layout/footer.php'; ?>
    <script src="<?php echo $basePath; ?>/assets/js/app.js"></script> 
</body>
</html>
