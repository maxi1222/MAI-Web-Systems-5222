<?php
// panel/acciones/pedido_estado.php
// Actualización del estado de un pedido en la base de datos (H.U-05 / HU05-T02)

require_once '../../includes/auth.php';
require_once '../../config/conexion.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../agenda.php");
    exit();
}

$id_pedido     = (isset($_POST['id_pedido']) && is_numeric($_POST['id_pedido'])) ? (int)$_POST['id_pedido'] : 0;
$nuevo_estado  = isset($_POST['estado']) ? trim($_POST['estado']) : (isset($_POST['nuevo_estado']) ? trim($_POST['nuevo_estado']) : '');
$vista_actual  = isset($_POST['vista_actual']) ? trim($_POST['vista_actual']) : 'diaria';
$fecha_actual  = isset($_POST['fecha_actual']) ? trim($_POST['fecha_actual']) : date('Y-m-d');
$estado_actual = isset($_POST['estado_actual']) ? trim($_POST['estado_actual']) : '';

$estados_validos = array('Pendiente', 'En preparación', 'Listo', 'Entregado');
$errores = array();

if ($id_pedido <= 0) {
    $errores[] = "El ID del pedido no es válido.";
}

if (!in_array($nuevo_estado, $estados_validos, true)) {
    $errores[] = "El estado seleccionado no es válido.";
}

$url_retorno = "../agenda.php?vista=" . urlencode($vista_actual) . "&fecha=" . urlencode($fecha_actual);
if ($estado_actual !== '') {
    $url_retorno .= "&estado=" . urlencode($estado_actual);
}

if (!empty($errores)) {
    $_SESSION['mensaje_error'] = implode(" ", $errores);
    header("Location: " . $url_retorno . "&error=datos_invalidos");
    exit();
}

$sql = "UPDATE pedidos SET estado = ?, fecha_modificacion = NOW() WHERE id_pedido = ?";
$stmt = $conexion->prepare($sql);

if ($stmt) {
    $stmt->bind_param("si", $nuevo_estado, $id_pedido);

    if ($stmt->execute()) {
        $stmt->close();
        $_SESSION['mensaje_exito'] = "Estado del pedido #" . $id_pedido . " actualizado a \"" . $nuevo_estado . "\".";
        header("Location: " . $url_retorno . "&msg=estado_actualizado");
        exit();
    } else {
        $stmt->close();
        $_SESSION['mensaje_error'] = "No se pudo actualizar el estado del pedido.";
        header("Location: " . $url_retorno . "&error=db");
        exit();
    }
} else {
    $_SESSION['mensaje_error'] = "Error interno al preparar la actualización del pedido.";
    header("Location: " . $url_retorno . "&error=prepare");
    exit();
}
?>
