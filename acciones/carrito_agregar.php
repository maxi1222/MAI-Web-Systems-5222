<?php

session_start();
require_once __DIR__ . '/../config/conexion.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_producto = isset($_POST['id_producto']) ? (int)$_POST['id_producto'] : 0;
    $cantidad = isset($_POST['cantidad']) ? (int)$_POST['cantidad'] : 1;
    if ($cantidad <= 0) {
        $cantidad = 1;
    }

    // Preservar la fecha de retiro seleccionada en el catálogo si fue enviada
    if (isset($_POST['fecha_retiro']) && !empty(trim($_POST['fecha_retiro']))) {
        $_SESSION['fecha_retiro_seleccionada'] = trim($_POST['fecha_retiro']);
    }

    if ($id_producto > 0) {
        $sql = "SELECT id_producto, disponible FROM productos WHERE id_producto = ? AND (activo = 1 OR activo IS NULL)";
        $stmt = mysqli_prepare($conexion, $sql);
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "i", $id_producto);
            mysqli_stmt_execute($stmt);
            $resultado = mysqli_stmt_get_result($stmt);
            $producto = mysqli_fetch_assoc($resultado);
            mysqli_stmt_close($stmt);

            // Si el producto existe, está activo y no está agotado (disponible == 1)
            if ($producto && (int)$producto['disponible'] === 1) {
                if (!isset($_SESSION['carrito']) || !is_array($_SESSION['carrito'])) {
                    $_SESSION['carrito'] = [];
                }
                
                // Indexar SIEMPRE por el ID entero del producto
                if (isset($_SESSION['carrito'][$id_producto])) {
                    $_SESSION['carrito'][$id_producto] += $cantidad;
                } else {
                    $_SESSION['carrito'][$id_producto] = $cantidad;
                }
            }
        }
    }
}

header("Location: ../carrito.php");
exit();
?>
