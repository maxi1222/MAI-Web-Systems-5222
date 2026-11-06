<?php
// panel/acciones/producto_baja.php
// Baja lógica de producto (activo = 0 y destacado = 0) exclusiva para Administrador (HU09-T03)

require_once '../../includes/auth.php';
requerir_administrador();
require_once '../../config/conexion.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../productos.php');
    exit();
}

$id_producto = (isset($_POST['id_producto']) && is_numeric($_POST['id_producto'])) ? (int)$_POST['id_producto'] : 0;

if ($id_producto <= 0) {
    $_SESSION['mensaje_error'] = 'ID de producto inválido.';
    header('Location: ../productos.php?error=id_invalido');
    exit();
}

$sql = "UPDATE productos SET activo = 0, destacado = 0 WHERE id_producto = ?";
$stmt = $conexion->prepare($sql);

if ($stmt) {
    $stmt->bind_param("i", $id_producto);

    if ($stmt->execute()) {
        $stmt->close();
        $_SESSION['mensaje_exito'] = 'Producto dado de baja exitosamente.';
        header('Location: ../productos.php?exito=1');
        exit();
    }
    $error_msg = $stmt->error;
    $stmt->close();
    error_log("Error al dar de baja el producto: " . $error_msg);
}

$_SESSION['mensaje_error'] = 'No se pudo dar de baja el producto.';
header('Location: ../productos.php?error=1');
exit();
?>
