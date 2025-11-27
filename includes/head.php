<?php
    session_start();
    // Si no se ha definido un título, ponemos uno por defecto
    if(!isset($tituloDeLaPagina)) {
        // ACTUALIZADO:
        $tituloDeLaPagina = "Asociación Mexicana de Diabetes"; 
    }
?>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <title><?php echo $tituloDeLaPagina; // Título dinámico ?></title>
    
    <link rel="stylesheet" href="assets/css/style.css">
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
</head>