<?php
// includes/auth.php
// Verificación de sesión activa y control de roles del personal (Administrador / Empleado)

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Muro de seguridad: verificar que el empleado o administrador haya iniciado sesión
if (!isset($_SESSION["id_usuario"]) || !isset($_SESSION["rol"])) {
    $ruta_login = file_exists("login.php") ? "login.php" : "../login.php";
    header("Location: " . $ruta_login);
    exit();
}

// Función auxiliar para comprobar si el usuario actual tiene rol Administrador
function es_administrador() {
    return isset($_SESSION["rol"]) && $_SESSION["rol"] === "Administrador";
}

// Función auxiliar para restringir acciones exclusivas del rol Administrador
function requerir_administrador() {
    if (!es_administrador()) {
        $ruta_panel = file_exists("productos.php") ? "productos.php" : "../productos.php";
        header("Location: " . $ruta_panel . "?error=no_autorizado");
        exit();
    }
}
?>
