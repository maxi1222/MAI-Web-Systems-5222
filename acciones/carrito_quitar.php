<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_producto = isset($_POST['id_producto']) ? (int)$_POST['id_producto'] : 0;
    $accion = isset($_POST['accion']) ? $_POST['accion'] : '';

    if ($accion === 'eliminar' && $id_producto > 0) {
        unset($_SESSION['carrito'][$id_producto]);
    }

    if ($accion === 'vaciar') {
        $_SESSION['carrito'] = [];
    }
}

header("Location: ../carrito.php");
exit();
?>