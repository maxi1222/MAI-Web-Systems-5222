<?php

require_once '../../config/conexion.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../agenda.php");
    exit();
}

$id_pedido    = isset($_POST['id_pedido']) ? (int)$_POST['id_pedido'] : 0;
$nuevo_estado = trim($_POST['estado'] ?? $_POST['nuevo_estado'] ?? '');

$estados_validos = ['Pendiente', 'En preparación', 'Listo', 'Entregado'];

$errores = [];

if ($id_pedido <= 0) {
    $errores[] = "El ID del pedido no es válido.";
}

if (!in_array($nuevo_estado, $estados_validos, true)) {
    $errores[] = "El estado seleccionado no es válido.";
}

// Si hay errores, redirigimos o mostramos el error
if (!empty($errores)) {
    header("Location: ../agenda.php?error=datos_invalidos");
    exit();
}

$sql = "UPDATE pedidos SET estado = ?, fecha_modificacion = NOW() WHERE id_pedido = ?";
$stmt = mysqli_prepare($conexion, $sql);

if ($stmt) {
    mysqli_stmt_bind_param($stmt, "si", $nuevo_estado, $id_pedido);
    
    if (mysqli_stmt_execute($stmt)) {
        header("Location: ../agenda.php?msg=estado_actualizado");
        exit();
    } else {
        echo "Error al actualizar el estado en la base de datos.";
    }
    
    mysqli_stmt_close($stmt);
} else {
    echo "No se pudo preparar la consulta SQL.";
}
?>