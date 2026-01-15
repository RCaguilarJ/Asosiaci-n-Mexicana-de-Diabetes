<?php
session_start();
$_SESSION['usuario_id'] = 5;
$_SERVER['REQUEST_METHOD'] = 'POST';
$_POST = [
    'nombre' => 'Carlos Aguilar',
    'email' => 'carlagular800@gmail.com',
    'telefono' => '3751111294',
    'especialidad' => 'NUTRI',
    'fecha' => '2026-01-22',
    'hora' => '10:00',
    'descripcion' => 'chequeo'
];

require __DIR__ . '/actions/guardar_cita.php';
