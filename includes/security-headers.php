<?php
// includes/security-headers.php
// Se agregó 'unsafe-inline' a script-src para permitir los scripts del login
header("Content-Security-Policy: default-src 'self'; script-src 'self' 'unsafe-inline'; style-src 'self' 'unsafe-inline' https://fonts.googleapis.com; font-src 'self' https://fonts.gstatic.com;");
?>