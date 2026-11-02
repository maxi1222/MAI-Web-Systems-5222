<?php

$vista = isset($_GET['vista']) ? $_GET['vista'] : 'diaria';
$fecha_base = isset($_GET['fecha']) && !empty($_GET['fecha']) ? $_GET['fecha'] : date('Y-m-d');
$filtro_estado = isset($_GET['estado']) ? $_GET['estado'] : '';

$ts_base = strtotime($fecha_base);
if ($ts_base === false) {
    $fecha_base = date('Y-m-d');
    $ts_base = time();
}

$fecha_desde = $fecha_base;
$fecha_hasta = $fecha_base;

if ($vista === 'diaria') {
    $fecha_desde = date('Y-m-d', $ts_base);
    $fecha_hasta = date('Y-m-d', $ts_base);
} elseif ($vista === 'semanal') {
    $fecha_desde = date('Y-m-d', $ts_base);
    $fecha_hasta = date('Y-m-d', strtotime('+6 days', $ts_base));
} elseif ($vista === 'mensual') {
    $fecha_desde = date('Y-m-01', $ts_base);
    $fecha_hasta = date('Y-m-t', $ts_base);
}

$sql = "SELECT id_pedido, nombre_cliente, telefono_cliente, fecha_retiro, estado, fecha_modificacion, fecha_creacion 
        FROM pedidos WHERE 1=1";
$tipos = "";
$params = [];

if ($vista !== 'todos') {
    $sql .= " AND DATE(fecha_retiro) >= ? AND DATE(fecha_retiro) <= ?";
    $tipos .= "ss";
    $params[] = $fecha_desde;
    $params[] = $fecha_hasta;
}

if (!empty($filtro_estado)) {
    $sql .= " AND estado = ?";
    $tipos .= "s";
    $params[] = $filtro_estado;
}

$sql .= " ORDER BY fecha_retiro ASC, id_pedido ASC";

$pedidos_agenda = [];
$stmt = mysqli_prepare($conexion, $sql);

if ($stmt) {
    if (!empty($params)) {
        mysqli_stmt_bind_param($stmt, $tipos, ...$params);
    }
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);

    $sql_det = "SELECT dp.cantidad, dp.precio_unitario, p.nombre, p.categoria 
                FROM detalle_pedidos dp
                INNER JOIN productos p ON dp.id_producto = p.id_producto
                WHERE dp.id_pedido = ?";
    $stmt_det = mysqli_prepare($conexion, $sql_det);

    while ($ped = mysqli_fetch_assoc($res)) {
        $id_p = (int)$ped['id_pedido'];
        $items_ped = [];
        $total_ped = 0;

        if ($stmt_det) {
            mysqli_stmt_bind_param($stmt_det, "i", $id_p);
            mysqli_stmt_execute($stmt_det);
            $res_d = mysqli_stmt_get_result($stmt_det);
            
            while ($d = mysqli_fetch_assoc($res_d)) {
                $subtotal = (float)$d['precio_unitario'] * (int)$d['cantidad'];
                $total_ped += $subtotal;
                $items_ped[] = $d;
            }
        }

        $ped['items'] = $items_ped;
        $ped['total'] = $total_ped;
        
        $hora_num = (int)date('H', strtotime($ped['fecha_retiro']));
        $ped['turno'] = ($hora_num < 13) ? 'Turno Mañana' : 'Turno Tarde';
        
        $pedidos_agenda[] = $ped;
    }

    if ($stmt_det) {
        mysqli_stmt_close($stmt_det);
    }
    mysqli_stmt_close($stmt);
}