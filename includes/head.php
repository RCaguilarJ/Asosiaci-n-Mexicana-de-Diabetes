<?php require_once 'security-headers.php'; ?>
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
    
    <link rel="stylesheet" href="assets/css/style.css">
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">

    <script>
        if (window.screen.width >= 768) {
            window.location.href = 'https://diabetesjalisco.org/';
        }
    </script>
</head>