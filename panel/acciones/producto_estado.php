<?php
// panel/acciones/producto_estado.php
// Cambio de estado de stock (disponible) y de prioridad (destacado) por POST (HU08-T02 y HU10-T02)

require_once '../../includes/auth.php';
require_once '../../config/conexion.php';

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: ../productos.php");
    exit();
}

$id_producto = (isset($_POST["id_producto"]) && is_numeric($_POST["id_producto"])) ? (int)$_POST["id_producto"] : 0;

if ($id_producto <= 0) {
    header("Location: ../productos.php?error=id_invalido");
    exit();
}

$es_cambio_destacado = isset($_POST["destacado"])
    || (isset($_POST["campo"]) && $_POST["campo"] === "destacado")
    || (isset($_POST["accion"]) && $_POST["accion"] === "destacado");

if ($es_cambio_destacado) {
    // Restricción de seguridad: Solo el rol Administrador puede modificar 'destacado'
    if (!es_administrador()) {
        $_SESSION["mensaje_error"] = "Acción no autorizada: Solo el administrador puede modificar productos destacados.";
        header("Location: ../productos.php?error=no_autorizado");
        exit();
    }

    if (isset($_POST["destacado"])) {
        $destacado = ((int)$_POST["destacado"] === 1) ? 1 : 0;
    } else {
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
