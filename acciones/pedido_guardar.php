<?php

session_start();
require_once __DIR__ . '/../config/db.php';

define('CUPO_DIARIO_MAXIMO', 10); 

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($_SESSION['carrito'])) {
    header("Location: ../carrito.php");
    exit();
}

$nombre_cliente = isset($_POST['nombre_cliente']) ? trim($_POST['nombre_cliente']) : '';
$telefono_raw = isset($_POST['telefono_cliente']) ? trim($_POST['telefono_cliente']) : '';
$fecha_retiro_dia = isset($_POST['fecha_retiro_dia']) ? $_POST['fecha_retiro_dia'] : '';
$fecha_retiro_hora = isset($_POST['fecha_retiro_hora']) ? $_POST['fecha_retiro_hora'] : '';

$_SESSION['datos_form_pedido'] = [
    'nombre_cliente'    => $nombre_cliente,
    'telefono_cliente'  => $telefono_raw,
    'fecha_retiro_dia'  => $fecha_retiro_dia,
    'fecha_retiro_hora' => $fecha_retiro_hora
];

$errores = [];


$fecha_hora_mysql = '';
if (empty($fecha_retiro_dia) || empty($fecha_retiro_hora)) {
    $errores[] = "Debe seleccionar obligatoriamente la fecha y la hora de retiro.";
} else {
    $fecha_hora_candidata = $fecha_retiro_dia . ' ' . $fecha_retiro_hora . ':00';
    $ts_retiro = strtotime($fecha_hora_candidata);
    $ts_minimo = time() + (24 * 60 * 60);

    if ($ts_retiro === false) {
        $errores[] = "El formato de la fecha u hora no es válido.";
    } else {
        $fecha_hora_mysql = date('Y-m-d H:i:s', $ts_retiro);

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

            if ($pedidos_actuales >= CUPO_DIARIO_MAXIMO) {
                $errores[] = "El cupo diario de producción para la fecha seleccionada ya se encuentra completo.";
            }
        }
    }
}


$dia_semana_retiro = !empty($fecha_retiro_dia) ? date('l', strtotime($fecha_retiro_dia)) : '';
$items_validados = [];

foreach ($_SESSION['carrito'] as $id_producto => $cantidad) {
    $id_int = (int)$id_producto;
    $cant_int = (int)$cantidad;
    if ($cant_int <= 0) continue;

    $sql_prod = "SELECT id_producto, nombre, precio, dias_habilitados FROM productos WHERE id_producto = ?";
    $stmt_prod = mysqli_prepare($conexion, $sql_prod);

    if ($stmt_prod) {
        mysqli_stmt_bind_param($stmt_prod, "i", $id_int);
        mysqli_stmt_execute($stmt_prod);
        $res_p = mysqli_stmt_get_result($stmt_prod);
        $fila_p = mysqli_fetch_assoc($res_p);
        mysqli_stmt_close($stmt_prod);

        if (!$fila_p) {
            $errores[] = "Uno de los productos ya no existe en el catálogo.";
            continue;
        }

        if (!empty($dia_semana_retiro) && strpos($fila_p['dias_habilitados'], $dia_semana_retiro) === false) {
            $errores[] = "El producto \"" . $fila_p['nombre'] . "\" no se elabora el día seleccionado.";
            continue;
        }

        $items_validados[] = [
            'id_producto' => (int)$fila_p['id_producto'],
            'precio_unitario' => (float)$fila_p['precio'],
            'cantidad' => $cant_int
        ];
    }
}

if (empty($items_validados) && empty($errores)) {
    $errores[] = "No hay productos válidos en el carrito.";
}

if (!empty($errores)) {
    $_SESSION['errores_pedido'] = $errores;
    header("Location: ../carrito.php");
    exit();
}


mysqli_begin_transaction($conexion);

$exito_transaccion = true;
$id_nuevo_pedido = 0;

$estado_inicial = 'Pendiente';
$sql_insert_pedido = "INSERT INTO pedidos (nombre_cliente, telefono_cliente, fecha_retiro, estado) VALUES (?, ?, ?, ?)";
$stmt_pedido = mysqli_prepare($conexion, $sql_insert_pedido);

if ($stmt_pedido) {
    mysqli_stmt_bind_param($stmt_pedido, "ssss", $nombre_cliente, $telefono_raw, $fecha_hora_mysql, $estado_inicial);
    if (mysqli_stmt_execute($stmt_pedido)) {
        $id_nuevo_pedido = (int)mysqli_insert_id($conexion);
    } else {
        $exito_transaccion = false;
    }
    mysqli_stmt_close($stmt_pedido);
} else {
    $exito_transaccion = false;
}

if ($exito_transaccion && $id_nuevo_pedido > 0) {
    $sql_insert_detalle = "INSERT INTO detalle_pedidos (id_pedido, id_producto, cantidad, precio_unitario) VALUES (?, ?, ?, ?)";
    $stmt_detalle = mysqli_prepare($conexion, $sql_insert_detalle);

    if ($stmt_detalle) {
        foreach ($items_validados as $item) {
            mysqli_stmt_bind_param(
                $stmt_detalle, 
                "iiid", 
                $id_nuevo_pedido, 
                $item['id_producto'], 
                $item['cantidad'], 
                $item['precio_unitario']
            );
            
            if (!mysqli_stmt_execute($stmt_detalle)) {
                $exito_transaccion = false;
                break;
            }
        }
        mysqli_stmt_close($stmt_detalle);
    } else {
        $exito_transaccion = false;
    }
}

if ($exito_transaccion && $id_nuevo_pedido > 0) {
    mysqli_commit($conexion);
    
    $_SESSION['carrito'] = [];
    unset($_SESSION['datos_form_pedido'], $_SESSION['errores_pedido']);
    
    header("Location: ../confirmacion.php?id=" . $id_nuevo_pedido);
    exit();
} else {
    mysqli_rollback($conexion);
    $_SESSION['errores_pedido'] = ["Ocurrió un error al guardar el pedido en la base de datos."];
    header("Location: ../carrito.php");
    exit();
}
?>