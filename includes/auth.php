<?php
// includes/auth.php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION["id_usuario"]) || !isset($_SESSION["rol"])) {
    $ruta_login = file_exists("login.php") ? "login.php" : "../login.php";
    header("Location: " . $ruta_login);
    exit();
}

function es_administrador() {
    return isset($_SESSION["rol"]) && $_SESSION["rol"] === "Administrador";
}

function requerir_administrador() {
    if (!es_administrador()) {
        $ruta_panel = file_exists("productos.php") ? "productos.php" : "../productos.php";
        header("Location: " . $ruta_panel . "?error=no_autorizado");
        exit();
    }
}
?>
