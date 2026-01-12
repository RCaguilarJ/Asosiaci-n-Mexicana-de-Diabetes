<?php
// includes/security-headers.php
// Cabeceras de seguridad recomendadas. Ajusta CSP según los scripts reales.
if (!headers_sent()) {
	header('X-Frame-Options: DENY');
	header('X-Content-Type-Options: nosniff');
	header('Referrer-Policy: no-referrer-when-downgrade');
	header('Permissions-Policy: geolocation=(), camera=()');

	// HSTS solo cuando se sirve sobre HTTPS
	if (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') {
		header('Strict-Transport-Security: max-age=63072000; includeSubDomains; preload');
	}

	// Content Security Policy: mantengo 'unsafe-inline' temporalmente si el proyecto usa scripts inline.
	// Para producción, reemplazar por nonces y eliminar 'unsafe-inline'.
	header("Content-Security-Policy: default-src 'self'; script-src 'self' 'unsafe-inline'; style-src 'self' 'unsafe-inline' https://fonts.googleapis.com; font-src 'self' https://fonts.gstatic.com; img-src 'self' data:;");
}
?>