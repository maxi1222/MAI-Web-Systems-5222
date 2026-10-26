<?php

require_once __DIR__ . '/config/conexion.php';

$id_pedido = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$sql = "SELECT * FROM pedidos WHERE id_pedido = ?";
$stmt = mysqli_prepare($conexion, $sql);
mysqli_stmt_bind_param($stmt, "i", $id_pedido);
mysqli_stmt_execute($stmt);
$resultado = mysqli_stmt_get_result($stmt);
$pedido = mysqli_fetch_assoc($resultado);
mysqli_stmt_close($stmt);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Confirmación de Pedido</title>
</head>
<body>
    <h1>¡Pedido Registrado con Éxito!</h1>
    
    <?php if ($pedido): ?>
        <p><strong>Número de Pedido:</strong> #<?php echo $pedido['id_pedido']; ?></p>
        <p><strong>Cliente:</strong> <?php echo htmlspecialchars($pedido['nombre_cliente'], ENT_QUOTES, 'UTF-8');
    ?></p>
        <p><strong>Fecha de Retiro:</strong> <?php echo $pedido['fecha_retiro']; ?></p>
        <p><strong>Estado:</strong> <?php echo $pedido['estado']; ?></p>

        <button type="button" id="btnModificarCancelarPedido" class="btn btn-secundario">Modificar o Cancelar
        Pedido</button>
    <?php else: ?>
        <p>No se encontró el pedido solicitado.</p>
    <?php endif; ?>
</body>
</html>