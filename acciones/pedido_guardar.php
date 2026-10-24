<?php

require_once __DIR__ . '/../config/db.php';

define('CUPO_DIARIO_MAXIMO', 10); 

$fecha_retiro_dia   = isset($_POST['fecha_retiro_dia']) ? $_POST['fecha_retiro_dia'] : '';
$fecha_retiro_hora  = isset($_POST['fecha_retiro_hora']) ? $_POST['fecha_retiro_hora'] : '';

$errores = [];
if (empty($fecha_retiro_dia) || empty($fecha_retiro_hora)) {
    $errores[] = "Debe seleccionar obligatoriamente la fecha y la hora de retiro.";
} else {
    $fecha_hora_candidata = $fecha_retiro_dia . ' ' . $fecha_retiro_hora . ':00';
    $ts_retiro = strtotime($fecha_hora_candidata);
    $ts_minimo = time() + (24 * 60 * 60);

    if ($ts_retiro < $ts_minimo) {
        $errores[] = "Los pedidos deben realizarse con al menos 1 día de anticipación";
    }

    $sql_cupo = "SELECT COUNT(*) AS total FROM pedidos WHERE DATE(fecha_retiro) = ?";
    $stmt_cupo = mysqli_prepare($conexion, $sql_cupo);

    if ($stmt_cupo) {
        mysqli_stmt_bind_param($stmt_cupo, "s", $fecha_retiro_dia);
        mysqli_stmt_execute($stmt_cupo);
        $resultado = mysqli_stmt_get_result($stmt_cupo);
        $fila = mysqli_fetch_assoc($resultado);
        
        $pedidos_actuales = (int)$fila['total'];
        mysqli_stmt_close($stmt_cupo);

        // (si los pedidos ya alcanzaron o superaron el límite, se rechaza)
        if ($pedidos_actuales >= CUPO_DIARIO_MAXIMO) {
            $errores[] = "El cupo diario de producción para la fecha seleccionada ya se encuentra completo.";
        }
    }
}

$dia_semana_retiro = !empty($fecha_retiro_dia) ? date('l', strtotime($fecha_retiro_dia)) : '';

if (!empty($_SESSION['carrito'])) {
    foreach ($_SESSION['carrito'] as $id_producto => $cantidad) {
        $sql_prod = "SELECT nombre, dias_habilitados FROM productos WHERE id_producto = ?";
        $stmt_prod = mysqli_prepare($conexion, $sql_prod);

        if ($stmt_prod) {
            mysqli_stmt_bind_param($stmt_prod, "i", $id_producto);
            mysqli_stmt_execute($stmt_prod);
            $res_p = mysqli_stmt_get_result($stmt_prod);
            $fila_p = mysqli_fetch_assoc($res_p);
            mysqli_stmt_close($stmt_prod);

            if ($fila_p) {
                if (strpos($fila_p['dias_habilitados'], $dia_semana_retiro) === false) {
                    $errores[] = "El producto \"" . $fila_p['nombre'] . "\" no se elabora el día seleccionado.";
                }
            }
        }
    }
}

if (!empty($errores)) {
    $_SESSION['errores_pedido'] = $errores;
    foreach ($errores as $err) {
        echo "<p style='color:red;'>$err</p>";
    }
} else {
    echo "<p style='color:green;'>¡Validación correcta para HU02-T03 y HU02-T04!</p>";
}
?>