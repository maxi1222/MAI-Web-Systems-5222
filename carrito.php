<?php
session_start();
require_once 'db.php';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Mi Carrito</title>
</head>
<body>
    <h1>Tus Productos Seleccionados</h1>
    
    <?php if (empty($_SESSION['carrito'])): ?>
        <p>Tu carrito está vacío.</p>
    <?php else: ?>
        <ul>
            <?php foreach ($_SESSION['carrito'] as $id => $cantidad): ?>
                <?php
                $res = mysqli_query($conexion, "SELECT nombre, precio FROM productos WHERE id_producto = $id");
                $item = mysqli_fetch_assoc($res);
                if ($item):
                ?>
                    <li>
                        <?php echo $item['nombre']; ?> - 
                        Cantidad: <?php echo $cantidad; ?> - 
                        Subtotal: $<?php echo $item['precio'] * $cantidad; ?>
                    </li>
                <?php endif; ?>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>

    <br>
    <a href="catalogo.php">Seguir comprando</a>
</body>
</html>