<?php
// acciones/pedido_guardar.php
// Validación de reglas de negocio (24h, cupo diario, día habilitado) y guardado de pedido (H.U-02)

session_start();
require_once '../config/conexion.php';
require_once '../includes/funciones.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($_SESSION['carrito'])) {
    header("Location: ../carrito.php");
    exit();
}

$nombre_cliente = isset($_POST['nombre_cliente']) ? trim($_POST['nombre_cliente']) : '';
$telefono_raw = isset($_POST['telefono_cliente']) ? trim($_POST['telefono_cliente']) : '';
$fecha_retiro_dia = isset($_POST['fecha_retiro_dia']) ? trim($_POST['fecha_retiro_dia']) : '';
$fecha_retiro_hora = isset($_POST['fecha_retiro_hora']) ? trim($_POST['fecha_retiro_hora']) : '';

$_SESSION['datos_form_pedido'] = array(
    'nombre_cliente'    => $nombre_cliente,
    'telefono_cliente'  => $telefono_raw,
    'fecha_retiro_dia'  => $fecha_retiro_dia,
    'fecha_retiro_hora' => $fecha_retiro_hora
);

$errores = array();

$telefono_limpio = str_replace(array(" ", "-", "+"), "", $telefono_raw);

if (empty($nombre_cliente) || strlen($nombre_cliente) < 3) {
    $errores[] = "Debe ingresar su nombre completo (mínimo 3 caracteres).";
}

if (empty($telefono_raw) || !is_numeric($telefono_limpio) || strlen($telefono_limpio) < 6) {
    $errores[] = "Debe ingresar un número de teléfono de contacto válido.";
}

$fecha_hora_mysql = '';
if (empty($fecha_retiro_dia) || empty($fecha_retiro_hora)) {
    $errores[] = "Debe seleccionar obligatoriamente la fecha y la hora de retiro.";
} else {
    if ($fecha_retiro_hora < '07:00' || $fecha_retiro_hora > '20:00') {
        $errores[] = "El horario de retiro debe estar comprendido entre las 07:00 y las 20:00 hs.";
    }

    $fecha_hora_candidata = $fecha_retiro_dia . ' ' . $fecha_retiro_hora . ':00';
    $ts_retiro = strtotime($fecha_hora_candidata);

    if ($ts_retiro === false) {
        $errores[] = "El formato de la fecha u hora no es válido.";
    } else {
        $fecha_hora_mysql = date('Y-m-d H:i:s', $ts_retiro);

        // HU02-T03: Validar que haya al menos 24 horas de anticipación
        if (!validar_anticipacion_minima($fecha_hora_mysql)) {
            $errores[] = "Los pedidos deben realizarse con al menos 1 día (24 horas) de anticipación.";
        }

        // HU02-T04: Consultar cuántos pedidos existen ya para esa fecha de retiro (RF-02)
        $inicio_dia_cupo = date('Y-m-d', $ts_retiro) . ' 00:00:00';
        $fin_dia_cupo = date('Y-m-d', $ts_retiro) . ' 23:59:59';
        $sql_cupo = "SELECT COUNT(*) AS total FROM pedidos WHERE fecha_retiro >= ? AND fecha_retiro <= ?";
        $stmt_cupo = $conexion->prepare($sql_cupo);

        if ($stmt_cupo) {
            $stmt_cupo->bind_param("ss", $inicio_dia_cupo, $fin_dia_cupo);
            $stmt_cupo->execute();
            $resultado = $stmt_cupo->get_result();
            $fila = $resultado->fetch_assoc();

            $pedidos_actuales = (int)$fila['total'];
            $stmt_cupo->close();

            if ($pedidos_actuales >= CUPO_DIARIO_MAXIMO) {
                $errores[] = "El cupo diario de producción para la fecha seleccionada ya se encuentra completo.";
            }
        }
    }
}

$dia_con_tilde = !empty($fecha_retiro_dia) ? obtener_dia_semana($fecha_retiro_dia) : '';
$dia_semana_retiro = normalizar_texto_dias($dia_con_tilde);
$items_validados = array();
$total_calculado = 0;

// Preparar la consulta una sola vez antes del bucle
$sql_prod = "SELECT id_producto, nombre, precio, disponible, dias_habilitados
             FROM productos
             WHERE id_producto = ? AND (activo = 1 OR activo IS NULL)";
$stmt_prod = $conexion->prepare($sql_prod);

if ($stmt_prod) {
    foreach ($_SESSION['carrito'] as $id_producto => $cantidad) {
        $id_int = (int)$id_producto;
        $cant_int = (int)$cantidad;
        if ($id_int <= 0 || $cant_int <= 0) {
            continue;
        }

        $stmt_prod->bind_param("i", $id_int);
        $stmt_prod->execute();
        $res_p = $stmt_prod->get_result();
        $fila_p = $res_p->fetch_assoc();

        if (!$fila_p) {
            $errores[] = "Uno de los productos ya no existe en el catálogo.";
            continue;
        }

        if ((int)$fila_p['disponible'] !== 1) {
            $errores[] = "El producto \"" . $fila_p['nombre'] . "\" se encuentra agotado.";
            continue;
        }

        $dias_habilitados_sin_tilde = normalizar_texto_dias($fila_p['dias_habilitados']);
        if (!empty($dia_semana_retiro) && stripos($dias_habilitados_sin_tilde, $dia_semana_retiro) === false) {
            $errores[] = "El producto \"" . $fila_p['nombre'] . "\" no se elabora el día seleccionado (" . $dia_con_tilde . ").";
            continue;
        }

        $precio_unitario = (int)$fila_p['precio'];
        $total_calculado = $total_calculado + ($precio_unitario * $cant_int);

        $items_validados[] = array(
            'id_producto'     => (int)$fila_p['id_producto'],
            'precio_unitario' => $precio_unitario,
            'cantidad'        => $cant_int
        );
    }
    $stmt_prod->close();
}

if (empty($items_validados) && empty($errores)) {
    $errores[] = "No hay productos válidos en el carrito.";
}

if (!empty($errores)) {
    $_SESSION['errores_pedido'] = $errores;
    header("Location: ../carrito.php");
    exit();
}

// Iniciar transacción para insertar cabecera (HU02-T05) y detalles (HU02-T06)
$conexion->begin_transaction();

$exito_transaccion = true;
$id_nuevo_pedido = 0;
$estado_inicial = 'Pendiente';

$sql_insert_pedido = "INSERT INTO pedidos (nombre_cliente, telefono_cliente, fecha_retiro, estado) VALUES (?, ?, ?, ?)";
$stmt_pedido = $conexion->prepare($sql_insert_pedido);

if ($stmt_pedido) {
    $stmt_pedido->bind_param("ssss", $nombre_cliente, $telefono_raw, $fecha_hora_mysql, $estado_inicial);
    if ($stmt_pedido->execute()) {
        $id_nuevo_pedido = (int)$conexion->insert_id;
    } else {
        $exito_transaccion = false;
    }
    $stmt_pedido->close();
} else {
    $exito_transaccion = false;
}

if ($exito_transaccion && $id_nuevo_pedido > 0) {
    $sql_insert_detalle = "INSERT INTO detalle_pedidos (id_pedido, id_producto, cantidad, precio_unitario) VALUES (?, ?, ?, ?)";
    $stmt_detalle = $conexion->prepare($sql_insert_detalle);

    if ($stmt_detalle) {
        foreach ($items_validados as $item) {
            $stmt_detalle->bind_param(
                "iiii",
                $id_nuevo_pedido,
                $item['id_producto'],
                $item['cantidad'],
                $item['precio_unitario']
            );

            if (!$stmt_detalle->execute()) {
                $exito_transaccion = false;
                break;
            }
        }
        $stmt_detalle->close();
    } else {
        $exito_transaccion = false;
    }
}

// HU02-T07: Confirmar transacción, vaciar $_SESSION['carrito'] y redirigir a confirmacion.php
if ($exito_transaccion && $id_nuevo_pedido > 0) {
    $conexion->commit();

    $_SESSION['ultimo_pedido_id'] = $id_nuevo_pedido;
    $_SESSION['carrito'] = array();
    unset($_SESSION['datos_form_pedido']);
    unset($_SESSION['errores_pedido']);
    unset($_SESSION['fecha_retiro_seleccionada']);

    header("Location: ../confirmacion.php?id=" . $id_nuevo_pedido);
    exit();
} else {
    $conexion->rollback();
    $_SESSION['errores_pedido'] = array("Ocurrió un error al guardar el pedido en la base de datos.");
    header("Location: ../carrito.php");
    exit();
}
?>
