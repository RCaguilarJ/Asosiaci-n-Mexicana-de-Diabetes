<?php
    $paginaActual = 'blog';
    $tituloDeLaPagina = "Blog Educativo - Asoc. Mexicana de Diabetes"; 

    // ===========================================================
    // 1. CONFIGURACIÓN DE CONEXIÓN SEGURA (Evita bloqueos)
    // ===========================================================
    
    $url_feed = "https://diabetesjalisco.org/feed/";
    $articulos = [];

    // Creamos un "contexto" para la conexión
    $opciones = [
        "http" => [
            "method" => "GET",
            // Nos identificamos como un navegador para que no nos bloqueen
            "header" => "User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/114.0.0.0 Safari/537.36\r\n",
            "timeout" => 3 // Si tarda más de 3 segundos, cancela para no congelar la página
        ],
        "ssl" => [
            "verify_peer" => false, // Ignorar error de certificado SSL en localhost
            "verify_peer_name" => false
        ]
    ];
    
    $contexto = stream_context_create($opciones);

    // Intentamos descargar el contenido primero
    $contenido_xml = @file_get_contents($url_feed, false, $contexto);
    $rss = false;

    if ($contenido_xml) {
        $rss = @simplexml_load_string($contenido_xml);
    }

    // ===========================================================
    // 2. LÓGICA DE EXTRACCIÓN DE IMÁGENES
    // ===========================================================

    function obtenerImagen($item) {
        $namespaces = $item->getNamespaces(true);

        // A. Buscar en media:content / media:thumbnail
        if (isset($namespaces['media'])) {
            $media = $item->children($namespaces['media']);
            if (isset($media->content) && isset($media->content->attributes()['url'])) {
                return (string)$media->content->attributes()['url'];
            }
            if (isset($media->thumbnail) && isset($media->thumbnail->attributes()['url'])) {
                return (string)$media->thumbnail->attributes()['url'];
            }
        }

        // B. Buscar en description/content (HTML)
        $html_content = '';
        if (isset($namespaces['content'])) {
            $html_content = (string)$item->children($namespaces['content'])->encoded;
        }
        if (empty($html_content)) {
            $html_content = (string)$item->description;
        }

        if (preg_match('/<img.+?src=[\'"](?P<src>.+?)[\'"].*?>/i', $html_content, $image)) {
            return $image['src'];
        }

        // C. IMAGEN DE RESPALDO (Local)
        return 'assets/img/platilloSano.jpg'; 
    }

    function limpiarTexto($html, $largo = 100) {
        $texto = strip_tags(html_entity_decode($html));
        if (strlen($texto) > $largo) {
            $texto = substr($texto, 0, $largo) . "...";
        }
        return $texto;
    }

    function obtenerCategoria($item) {
        if (isset($item->category)) {
            foreach ($item->category as $categoria) {
                $valor = trim((string)$categoria);
                if (!empty($valor)) {
                    return $valor;
                }
            }
        }
        return 'Salud';
    }

    function calcularTiempoLectura($texto) {
        $palabras = str_word_count(strip_tags($texto));
        $minutos = (int)ceil($palabras / 150);
        if ($minutos < 4) {
            $minutos = 4;
        }
        if ($minutos > 8) {
            $minutos = 8;
        }
        return $minutos;
    }

    function claseCategoria($categoria) {
        $categoria = strtolower($categoria);
        if (strpos($categoria, 'nutri') !== false) return 'tag-nutricion';
        if (strpos($categoria, 'fis') !== false || strpos($categoria, 'actividad') !== false) return 'tag-actividad';
        if (strpos($categoria, 'tecno') !== false) return 'tag-tecnologia';
        if (strpos($categoria, 'bienestar') !== false) return 'tag-bienestar';
        return 'tag-salud';
    }

    function formatearFecha($fecha) {
        if (empty($fecha)) {
            return date('d M Y');
        }
        return date('d M Y', strtotime($fecha));
    }

    function escapar($valor) {
        return htmlspecialchars($valor ?? '', ENT_QUOTES, 'UTF-8');
    }

    // ===========================================================
    // 3. PROCESAMIENTO DE DATOS
    // ===========================================================

    if ($rss) {
        foreach ($rss->channel->item as $item) {
            $namespaces = $item->getNamespaces(true);
            $articulos[] = [
                'titulo'    => (string)$item->title,
                'link'      => (string)$item->link,
                'fecha'     => formatearFecha((string)$item->pubDate),
                'desc'      => limpiarTexto((string)$item->description, 140),
                'autor'     => isset($namespaces['dc']) ? (string)$item->children($namespaces['dc'])->creator : 'AMD Jalisco',
                'imagen'    => obtenerImagen($item),
                'categoria' => obtenerCategoria($item),
                'lectura'   => calcularTiempoLectura((string)$item->description)
            ];
        }
    } 
    
    // Si falló la carga o no hay artículos, usamos datos falsos para que no se vea vacío
    if (empty($articulos)) {
        $articulos[] = [
            'titulo' => 'Consejos de Nutrición',
            'link' => '#',
            'fecha' => date('d M Y'),
            'desc' => 'Aprende a comer saludable con nuestros expertos.',
            'autor' => 'AMD Jalisco',
            'imagen' => 'assets/img/platilloSano.jpg',
            'categoria' => 'Nutrición',
            'lectura' => 5
        ];
        $articulos[] = [
            'titulo' => 'Monitoreo de Glucosa',
            'link' => '#',
            'fecha' => date('d M Y'),
            'desc' => 'La importancia de medir tu glucosa diariamente.',
            'autor' => 'AMD Jalisco',
            'imagen' => 'assets/img/medidorInsulina.jpg',
            'categoria' => 'Tecnología',
            'lectura' => 6
        ];
    }

    $destacado = $articulos[0];
    $recientes = array_slice($articulos, 1, 4);
?>
<!DOCTYPE html>
<html lang="es">

<?php 
    // Incluimos el head general
    include 'includes/head.php'; 
?>

<body>

    <?php 
        include 'includes/menu-drawer.php'; 
        include 'includes/header.php'; 
    ?>

    <section class="blog-hero">
        <div class="contenedor blog-hero-inner">
            <div class="blog-hero-content">
                <div class="blog-hero-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 19V5a2 2 0 0 1 2-2h14"></path><path d="M21 19V5a2 2 0 0 0-2-2h-6"></path><path d="M3 19a2 2 0 0 0 2 2h14"></path><path d="M9 3v18"></path></svg>
                </div>
                <div class="blog-hero-text">
                    <p class="hero-label">Blog Educativo</p>
                    <h1>Información y consejos</h1>
                    <p>Descubre tips sobre nutrición, actividad física y tecnología para vivir con diabetes.</p>
                </div>
                <div class="blog-hero-logo">
                    <img src="assets/img/logo.png" alt="Logo AMD Jalisco">
                </div>
            </div>
            <div class="blog-hero-tabs">
                <div class="category-filter">
                    <button type="button" class="cat-btn cat-btn--todos active">Todos</button>
                    <button type="button" class="cat-btn cat-btn--nutricion">Nutrición</button>
                    <button type="button" class="cat-btn cat-btn--actividad">Actividad Física</button>
                    <button type="button" class="cat-btn cat-btn--tecnologia">Tecnología</button>
                    <button type="button" class="cat-btn cat-btn--bienestar">Bienestar</button>
                </div>
                <div class="category-scroll-indicator" aria-hidden="true">
                    <button class="scroll-arrow" type="button">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="11 4 5 8 11 12"></polyline></svg>
                    </button>
                    <div class="scroll-track">
                        <span></span>
                    </div>
                    <button class="scroll-arrow" type="button">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="7 4 13 8 7 12"></polyline></svg>
                    </button>
                </div>
            </div>
        </div>
    </section>

    <main class="contenedor blog-wrapper">
        <?php if (!empty($destacado)): ?>
            <article class="card-base featured-post">
                <div class="featured-image-container">
                    <img src="<?php echo escapar($destacado['imagen']); ?>" alt="Imagen del artículo destacado" class="featured-img">
                </div>
                <div class="featured-content">
                    <div class="tags-container">
                        <span class="blog-tag <?php echo claseCategoria($destacado['categoria']); ?>"><?php echo escapar($destacado['categoria']); ?></span>
                        <span class="blog-tag blog-tag--destacado">Destacado</span>
                    </div>
                    <h2><?php echo escapar($destacado['titulo']); ?></h2>
                    <p><?php echo escapar($destacado['desc']); ?></p>
                    <div class="post-meta">
                        <span class="meta-item">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="8" r="4"></circle><path d="M2 21a8 8 0 0 1 16 0"></path></svg>
                            <?php echo escapar($destacado['autor']); ?>
                        </span>
                        <span class="meta-item">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
                            <?php echo (int)$destacado['lectura']; ?> min
                        </span>
                        <span class="meta-item">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 4h18"></path><path d="M8 2v4"></path><path d="M16 2v4"></path><rect width="18" height="18" x="3" y="4" rx="2"></rect><path d="M3 10h18"></path></svg>
                            <?php echo escapar($destacado['fecha']); ?>
                        </span>
                    </div>
                    <a class="btn-blog-link" href="<?php echo escapar($destacado['link']); ?>" target="_blank" rel="noopener">
                        Leer artículo completo
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg>
                    </a>
                </div>
            </article>
        <?php endif; ?>

        <section class="blog-recent-section">
            <div class="section-header">
                <h2>Artículos Recientes</h2>
                <a class="link-ver-blog" href="https://diabetesjalisco.org/blog" target="_blank" rel="noopener">Ver blog completo</a>
            </div>
            <div class="blog-recent-list">
                <?php if (!empty($recientes)): ?>
                    <?php foreach ($recientes as $articulo): ?>
                        <article class="card-base blog-card">
                            <div class="blog-card__image">
                                <img src="<?php echo escapar($articulo['imagen']); ?>" alt="Imagen artículo" loading="lazy">
                            </div>
                            <div class="blog-card__body">
                                <span class="blog-tag <?php echo claseCategoria($articulo['categoria']); ?>"><?php echo escapar($articulo['categoria']); ?></span>
                                <h3><?php echo escapar($articulo['titulo']); ?></h3>
                                <p><?php echo escapar($articulo['desc']); ?></p>
                                <div class="post-meta post-meta--compact">
                                    <span class="meta-item">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
                                        <?php echo (int)$articulo['lectura']; ?> min
                                    </span>
                                    <span class="meta-item">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 4h18"></path><path d="M8 2v4"></path><path d="M16 2v4"></path><rect width="18" height="18" x="3" y="4" rx="2"></rect><path d="M3 10h18"></path></svg>
                                        <?php echo escapar($articulo['fecha']); ?>
                                    </span>
                                </div>
                                <a class="blog-card__link" href="<?php echo escapar($articulo['link']); ?>" target="_blank" rel="noopener">
                                    Leer artículo
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg>
                                </a>
                            </div>
                        </article>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p class="texto-suave">Por ahora no hay más artículos para mostrar. Vuelve pronto.</p>
                <?php endif; ?>
            </div>
        </section>

        <section class="newsletter-card blog-newsletter">
            <div class="newsletter-icon">
                <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"></path><path d="M2 17V7"></path><path d="M22 17V7"></path><path d="M2 7l10 6 10-6"></path></svg>
            </div>
            <h3>Mantente informado</h3>
            <p>Recibe los últimos artículos y consejos directamente en tu correo.</p>
            <button class="btn-suscribir" type="button">Suscribirse</button>
        </section>
    </main>

    <?php include 'includes/footer.php'; ?>

    <script src="assets/js/app.js"></script>
</body>
</html>
