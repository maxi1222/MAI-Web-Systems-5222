<?php
// panel/acciones/producto_estado.php

require_once '../../includes/auth.php';
require_once '../../config/conexion.php';

$id_producto = 0;
if (isset($_POST["id_producto"]) && is_numeric($_POST["id_producto"])) {
    $id_producto = (int)$_POST["id_producto"];
} elseif (isset($_GET["id_producto"]) && is_numeric($_GET["id_producto"])) {
    $id_producto = (int)$_GET["id_producto"];
} elseif (isset($_POST["id"]) && is_numeric($_POST["id"])) {
    $id_producto = (int)$_POST["id"];
} elseif (isset($_GET["id"]) && is_numeric($_GET["id"])) {
    $id_producto = (int)$_GET["id"];
}

if ($id_producto <= 0) {
    header("Location: ../productos.php?error=id_invalido");
    exit();
}

// Esto te toca a vos iñaki: En la vista de productos (panel/productos.php o producto_editar.php),
// maquetar el control visual (switch o botón) para alternar destacados enviando campo=destacado o accion=destacado.

$es_cambio_destacado = false;
if (isset($_POST["destacado"]) || isset($_GET["destacado"])) {
    $es_cambio_destacado = true;
} elseif ((isset($_POST["campo"]) && $_POST["campo"] === "destacado") || (isset($_GET["campo"]) && $_GET["campo"] === "destacado")) {
    $es_cambio_destacado = true;
} elseif ((isset($_POST["accion"]) && $_POST["accion"] === "destacado") || (isset($_GET["accion"]) && $_GET["accion"] === "destacado")) {
    $es_cambio_destacado = true;
}

if ($es_cambio_destacado) {
    // Restricción de seguridad: Solo el rol Administrador puede modificar 'destacado'
    if (!es_administrador()) {
        $_SESSION["mensaje_error"] = "Acción no autorizada: Solo el administrador puede modificar productos destacados.";
        header("Location: ../productos.php?error=no_autorizado");
        exit();
    }

    $destacado = null;
    if (isset($_POST["destacado"])) {
        $destacado = ((int)$_POST["destacado"] === 1) ? 1 : 0;
    } elseif (isset($_GET["destacado"])) {
        $destacado = ((int)$_GET["destacado"] === 1) ? 1 : 0;
    } else {
        // Si no se envió el valor explícito, consultamos el actual para alternarlo (toggle)
        $sql_actual = "SELECT destacado FROM productos WHERE id_producto = ?";
        $stmt_actual = $conexion->prepare($sql_actual);
        $stmt_actual->bind_param("i", $id_producto);
        $stmt_actual->execute();
        $resultado_actual = $stmt_actual->get_result();
        $producto_actual = $resultado_actual->fetch_assoc();
        $stmt_actual->close();

        if (!$producto_actual) {
            header("Location: ../productos.php?error=no_encontrado");
            exit();
        }

        $destacado = ((int)$producto_actual["destacado"] === 1) ? 0 : 1;
    }

    // Consulta SQL preparada para actualizar la prioridad de destacado (HU10-T02)
    $sql = "UPDATE productos SET destacado = ? WHERE id_producto = ?";
    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("ii", $destacado, $id_producto);

    if ($stmt->execute()) {
        $stmt->close();
        $_SESSION["mensaje_exito"] = "producto guardado exitosamente";
        header("Location: ../productos.php?exito=1");
        exit();
    } else {
        error_log("Error al actualizar prioridad/destacado del producto: " . $stmt->error);
        $stmt->close();
        $_SESSION["mensaje_error"] = "No se pudo actualizar el estado de destacado del producto.";
        header("Location: ../productos.php?error=1");
        exit();
    }
}

if (isset($_POST["disponible"])) {
    $disponible = ((int)$_POST["disponible"] === 1) ? 1 : 0;
} elseif (isset($_GET["disponible"])) {
    $disponible = ((int)$_GET["disponible"] === 1) ? 1 : 0;
} else {
    $sql_actual = "SELECT disponible FROM productos WHERE id_producto = ?";
    $stmt_actual = $conexion->prepare($sql_actual);
    $stmt_actual->bind_param("i", $id_producto);
    $stmt_actual->execute();
    $resultado_actual = $stmt_actual->get_result();
    $producto_actual = $resultado_actual->fetch_assoc();
    $stmt_actual->close();

    if (!$producto_actual) {
        header("Location: ../productos.php?error=no_encontrado");
        exit();
    }

    $disponible = ((int)$producto_actual["disponible"] === 1) ? 0 : 1;
}

$sql = "UPDATE productos SET disponible = ? WHERE id_producto = ?";
$stmt = $conexion->prepare($sql);
$stmt->bind_param("ii", $disponible, $id_producto);

if ($stmt->execute()) {
    $stmt->close();
    $_SESSION["mensaje_exito"] = "producto guardado exitosamente";
    header("Location: ../productos.php?exito=1");
    exit();
} else {
    error_log("Error al actualizar estado del producto: " . $stmt->error);
    $stmt->close();
    $_SESSION["mensaje_error"] = "No se pudo actualizar el estado de disponibilidad del producto.";
    header("Location: ../productos.php?error=1");
    exit();
}
?>
