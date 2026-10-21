<?php
// config/conexion.php
// Configuración de la conexión a la base de datos y constantes globales del sistema

// Constantes globales del negocio
if (!defined('CUPO_DIARIO')) {
    define('CUPO_DIARIO', 20);
}
if (!defined('CUPO_DIARIO_MAXIMO')) {
    define('CUPO_DIARIO_MAXIMO', CUPO_DIARIO);
}
if (!defined('TELEFONO_LOCAL')) {
    define('TELEFONO_LOCAL', '098 318 851');
}
if (!defined('TELEFONO')) {
    define('TELEFONO', TELEFONO_LOCAL);
}

// Conexión a la base de datos MySQL mediante mysqli
$servidor = "localhost";
$usuario_bd = "root";
$contrasena_bd = "";
$base_datos = "MAI_Panaderia";

$conexion = new mysqli($servidor, $usuario_bd, $contrasena_bd, $base_datos);

// Comprobar si hubo un error al conectar
if ($conexion->connect_error) {
    // Guardar el error en el log del servidor de forma privada
    error_log("Error de conexión de la BD: " . $conexion->connect_error);

    // Mostrar mensaje genérico al usuario
    die("Lo sentimos, el servicio no está disponible en este momento.");
}

// Configurar codificación de caracteres a UTF-8 para soportar tildes y caracteres especiales
$conexion->set_charset("utf8mb4");
?>
