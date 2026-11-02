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

    while ($ped = mysqli_fetch_assoc($res)) {
        $hora_num = (int)date('H', strtotime($ped['fecha_retiro']));
        $ped['turno'] = ($hora_num < 13) ? 'Turno Mañana' : 'Turno Tarde';
        
        $pedidos_agenda[] = $ped;
    }
    mysqli_stmt_close($stmt);
}