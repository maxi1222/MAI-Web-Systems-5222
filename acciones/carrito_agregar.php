<?php
// acciones/carrito_agregar.php
// Gestión de carrito de compras en sesión PHP (H.U-02 / HU02-T01)

session_start();
require_once '../config/conexion.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_producto = (isset($_POST['id_producto']) && is_numeric($_POST['id_producto'])) ? (int)$_POST['id_producto'] : 0;
    $cantidad = (isset($_POST['cantidad']) && is_numeric($_POST['cantidad'])) ? (int)$_POST['cantidad'] : 1;
    if ($cantidad <= 0) {
        $cantidad = 1;
    }

    // Preservar la fecha de retiro seleccionada en el catálogo si fue enviada
    if (isset($_POST['fecha_retiro']) && !empty(trim($_POST['fecha_retiro']))) {
        $_SESSION['fecha_retiro_seleccionada'] = trim($_POST['fecha_retiro']);
    }

    if ($id_producto > 0) {
        $sql = "SELECT id_producto, disponible FROM productos WHERE id_producto = ? AND (activo = 1 OR activo IS NULL)";
        $stmt = $conexion->prepare($sql);
        if ($stmt) {
            $stmt->bind_param("i", $id_producto);
            $stmt->execute();
            $resultado = $stmt->get_result();
            $producto = $resultado->fetch_assoc();
            $stmt->close();

            // Si el producto existe, está activo y no está agotado (disponible == 1)
            if ($producto && (int)$producto['disponible'] === 1) {
                if (!isset($_SESSION['carrito'])) {
                    $_SESSION['carrito'] = array();
                }

                if (isset($_SESSION['carrito'][$id_producto])) {
                    $_SESSION['carrito'][$id_producto] = $_SESSION['carrito'][$id_producto] + $cantidad;
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
