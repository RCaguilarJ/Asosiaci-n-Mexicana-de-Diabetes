<?php require_once __DIR__ . '/security-headers.php'; ?>
<?php require_once __DIR__ . '/../base_path.php'; ?>
<?php
// session_start();
if (!isset($tituloDeLaPagina)) {
    $tituloDeLaPagina = 'Asociacion Mexicana de Diabetes';
}
if (!isset($metaDescription)) {
    $metaDescription = 'La Asociacion Mexicana de Diabetes en Jalisco es una organizacion sin fines de lucro dedicada a la educacion, prevencion y manejo de la diabetes.';
}
?>
<!DOCTYPE html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="<?php echo htmlspecialchars($metaDescription); ?>">

    <title><?php echo $tituloDeLaPagina; ?></title>

    <link rel="stylesheet" href="<?php echo $basePath; ?>/assets/css/style.css">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <script>
        (function() {
            const desktopQuery = window.matchMedia('(min-width: 1025px)');
            const desktopRedirectUrl = 'https://app.diabetesjalisco.org/';

            function validarResolucionYRedirigir() {
                if (desktopQuery.matches) {
                    window.location.href = desktopRedirectUrl;
                }
            }

            validarResolucionYRedirigir();
            desktopQuery.addEventListener('change', validarResolucionYRedirigir);
            window.addEventListener('load', validarResolucionYRedirigir);
        })();
    </script>
</head>
