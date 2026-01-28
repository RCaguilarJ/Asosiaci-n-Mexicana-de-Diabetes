<!DOCTYPE html>
<?php require_once __DIR__ . '/security-headers.php'; ?>
<?php require_once __DIR__ . '/../base_path.php'; ?>
<?php
    //session_start();
    // Si no se ha definido un título, ponemos uno por defecto
    if(!isset($tituloDeLaPagina)) {
        // ACTUALIZADO:
        $tituloDeLaPagina = "Asociación Mexicana de Diabetes"; 
    }
    if(!isset($metaDescription)) {
        $metaDescription = "La Asociación Mexicana de Diabetes en Jalisco es una organización sin fines de lucro dedicada a la educación, prevención y manejo de la diabetes.";
    }
?>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="<?php echo htmlspecialchars($metaDescription); ?>">
    
    <title><?php echo $tituloDeLaPagina; // Título dinámico ?></title>
    
    <link rel="stylesheet" href="<?php echo $basePath; ?>/assets/css/style.css">
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <script>
        // Redirige si la resoluciÃ³n no coincide con medidas estÃ¡ndar de tablet.
        function esResolucionTabletEstandar() {
            const ancho = window.innerWidth || document.documentElement.clientWidth || document.body.clientWidth;
            const alto = window.innerHeight || document.documentElement.clientHeight || document.body.clientHeight;

            const medidasEstandar = [
                { w: 768, h: 1024 },   // iPad 9.7" portrait
                { w: 800, h: 1280 },   // Android 10" portrait
                { w: 810, h: 1080 },   // iPad 10.2" portrait
                { w: 834, h: 1112 },   // iPad Pro 10.5" portrait
                { w: 834, h: 1194 },   // iPad Pro 11" portrait
                { w: 1024, h: 1366 }   // iPad Pro 12.9" portrait
            ];

            return medidasEstandar.some(function(medida) {
                const portrait = ancho === medida.w && alto === medida.h;
                const landscape = ancho === medida.h && alto === medida.w;
                return portrait || landscape;
            });
        }

        function validarResolucionYRedirigir() {
            if (!esResolucionTabletEstandar()) {
                window.location.href = 'https://diabetesjalisco.org/';
            }
        }

        // Verificar inmediatamente y al cargar completamente
        validarResolucionYRedirigir();
        window.addEventListener('load', validarResolucionYRedirigir);
    </script>
</head>
