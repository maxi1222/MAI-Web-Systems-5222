<?php
date_default_timezone_set('America/Montevideo');

if (!defined('CUPO_DIARIO_MAXIMO')) {
    define('CUPO_DIARIO_MAXIMO', 20);
}

if (!defined('TELEFONO_LOCAL')) {
    define('TELEFONO_LOCAL', '098 318 851');
}

$servidor = "localhost";
$usuario_bd = "root";
$contrasena_bd = "";
$base_datos = "MAI_Panaderia";

$conexion = new mysqli($servidor, $usuario_bd, $contrasena_bd, $base_datos);

if ($conexion->connect_error) {
    error_log("Error de conexión de la BD: " . $conexion->connect_error);
    die("Lo sentimos, el servicio no está disponible en este momento.");
}

$conexion->set_charset("utf8mb4");
?>
