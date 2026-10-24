<?php

require_once __DIR__ . '/config/db.php';

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
        <p><strong>Cliente:</strong> <?php echo $pedido['nombre_cliente']; ?></p>
        <p><strong>Fecha de Retiro:</strong> <?php echo $pedido['fecha_retiro']; ?></p>
        <p><strong>Estado:</strong> <?php echo $pedido['estado']; ?></p>

        <form action="acciones/pedido_cancelar.php" method="POST">
            <input type="hidden" name="id_pedido" value="<?php echo $pedido['id_pedido']; ?>">
            <button type="submit" style="background-color: red; color: white;">Cancelar Pedido</button>
        </form>
    <?php else: ?>
        <p>No se encontró el pedido solicitado.</p>
    <?php endif; ?>
</body>
</html>