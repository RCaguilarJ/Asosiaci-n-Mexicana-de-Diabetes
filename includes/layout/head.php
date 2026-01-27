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
        // Función para verificar si es dispositivo de escritorio
        function esDispositivoEscritorio() {
            // Verificar múltiples indicadores
            const ancho = window.innerWidth || document.documentElement.clientWidth || document.body.clientWidth;
            const esMovil = /Android|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent);
            const esTouchDevice = 'ontouchstart' in window || navigator.maxTouchPoints > 0;
            
            // Es escritorio si: ancho >= 1024px Y NO es móvil Y NO tiene touch
            return ancho >= 1024 && !esMovil && !esTouchDevice;
        }
        
        // Verificar inmediatamente
        // if (esDispositivoEscritorio()) {
        //     window.location.href = 'https://diabetesjalisco.org/';
        // }
        
        // También verificar después de que la página se cargue completamente
        // window.addEventListener('load', function() {
        //     if (esDispositivoEscritorio()) {
        //         window.location.href = 'https://diabetesjalisco.org/';
        //     }
        // });
    </script>
</head>
