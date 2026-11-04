<?php

require_once '../includes/auth.php';
require_once '../config/conexion.php';

$ruta_base = "../";
$pagina_panel = "buscar";
$titulo_pagina = "Buscar Pedidos - Panel MAI Panadería";

$fecha_desde = isset($_GET['fecha_desde']) ? trim($_GET['fecha_desde']) : '';
$fecha_hasta = isset($_GET['fecha_hasta']) ? trim($_GET['fecha_hasta']) : '';
$nombre_cliente = isset($_GET['nombre_cliente']) ? trim($_GET['nombre_cliente']) : '';

$sql = "SELECT id_pedido, nombre_cliente, telefono_cliente, fecha_retiro, estado, fecha_creacion
        FROM pedidos
        WHERE 1=1";
$tipos = "";
$parametros = array();

if (!empty($fecha_desde)) {
    $sql .= " AND DATE(fecha_retiro) >= ?";
    $tipos .= "s";
    $parametros[] = $fecha_desde;
}

if (!empty($fecha_hasta)) {
    $sql .= " AND DATE(fecha_retiro) <= ?";
    $tipos .= "s";
    $parametros[] = $fecha_hasta;
}

if (!empty($nombre_cliente)) {
    $sql .= " AND nombre_cliente LIKE ?";
    $tipos .= "s";
    $parametros[] = "%" . $nombre_cliente . "%";
}

$sql .= " ORDER BY fecha_retiro DESC, id_pedido DESC";

$pedidos_encontrados = array();
$stmt = $conexion->prepare($sql);

if ($stmt) {
    if (!empty($parametros)) {
        $stmt->bind_param($tipos, ...$parametros);
    }
    $stmt->execute();
    $resultado = $stmt->get_result();

    $sql_detalle = "SELECT dp.cantidad, dp.precio_unitario, p.nombre
                    FROM detalle_pedidos dp
                    INNER JOIN productos p ON dp.id_producto = p.id_producto
                    WHERE dp.id_pedido = ?";
    $stmt_det = $conexion->prepare($sql_detalle);

    while ($fila = $resultado->fetch_assoc()) {
        $id_ped = (int)$fila['id_pedido'];
        $resumen_items = array();
        $monto_total = 0;

        if ($stmt_det) {
            $stmt_det->bind_param("i", $id_ped);
            $stmt_det->execute();
            $res_det = $stmt_det->get_result();

            while ($item = $res_det->fetch_assoc()) {
                $cant = (int)$item['cantidad'];
                $precio = (int)$item['precio_unitario'];
                $monto_total += ($cant * $precio); // <-- CORREGIDO AQUÍ EL OPERADOR DE MULTIPLICACIÓN
                $resumen_items[] = $cant . "x " . $item['nombre'];
            }
        }

        $fila['resumen_productos'] = !empty($resumen_items) ? implode(", ", $resumen_items) : "Sin productos";
        $fila['total'] = $monto_total;
        $pedidos_encontrados[] = $fila;
    }

    if ($stmt_det) {
        $stmt_det->close();
    }
    $stmt->close();
}

require_once '../includes/header_panel.php';
?>

<div class="contenedor contenedor-panel">
    <div class="cabecera-seccion-panel">
        <h2>Búsqueda y Filtrado de Pedidos</h2>
        <p>Buscá encargos por fecha desde, fecha hasta o por el nombre del cliente.</p>
    </div>

    <form action="pedidos_buscar.php" method="GET" class="barra-filtros-busqueda">
        <div class="filtro-item">
            <label for="fecha_desde">Fecha desde:</label>
            <input
                type="date"
                id="fecha_desde"
                name="fecha_desde"
                value="<?php echo htmlspecialchars($fecha_desde, ENT_QUOTES, 'UTF-8'); ?>"
            >
        </div>

        <div class="filtro-item">
            <label for="fecha_hasta">Fecha hasta:</label>
            <input
                type="date"
                id="fecha_hasta"
                name="fecha_hasta"
                value="<?php echo htmlspecialchars($fecha_hasta, ENT_QUOTES, 'UTF-8'); ?>"
            >
        </div>

        <div class="filtro-item">
            <label for="nombre_cliente">Nombre del cliente:</label>
            <input
                type="text"
                id="nombre_cliente"
                name="nombre_cliente"
                placeholder="Ej: María, Carlos..."
                value="<?php echo htmlspecialchars($nombre_cliente, ENT_QUOTES, 'UTF-8'); ?>"
            >
        </div>

        <div class="filtro-botones">
            <button type="submit" class="btn btn-primario">Buscar Pedidos</button>
            <a href="pedidos_buscar.php" class="btn btn-secundario">Limpiar</a>
        </div>
    </form>

    <section class="seccion-listado">
        <div class="cabecera-tabla-resultados">
            <h3>Resultados de la Búsqueda</h3>
            <span class="contador-resultados">
                Se encontraron <strong><?php echo count($pedidos_encontrados); ?></strong> pedido(s)
            </span>
        </div>

        <?php if (empty($pedidos_encontrados)): ?>
            <div class="mensaje-vacio">
                <h3>No se encontraron pedidos con esos filtros</h3>
                <p>Probá ampliando el rango de fechas o buscando otro nombre de cliente.</p>
                <a href="pedidos_buscar.php" class="btn btn-secundario">Ver todos los pedidos</a>
            </div>
        <?php else: ?>
            <div class="tabla-responsive">
                <table class="tabla-productos tabla-pedidos-buscar">
                    <thead>
                        <tr class="<?php echo $clase_estado; ?>">
                            <th>ID</th>
                            <th>Cliente</th>
                            <th>Teléfono</th>
                            <th>Fecha y Hora de Retiro</th>
                            <th>Productos Encargados</th>
                            <th>Total</th>
                            <th>Estado</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($pedidos_encontrados as $ped): ?>
                            <?php
                            $clase_estado = "estado-pendiente";
                            if ($ped['estado'] === 'En preparación') {
                                $clase_estado = "estado-preparacion";
                            } elseif ($ped['estado'] === 'Listo') {
                                $clase_estado = "estado-listo";
                            } elseif ($ped['estado'] === 'Entregado') {
                                $clase_estado = "estado-entregado";
                            }
                            ?>
                            <tr class="<?php echo $clase_estado; ?>">
                                <td><strong>#<?php echo (int)$ped['id_pedido']; ?></strong></td>
                                <td><?php echo htmlspecialchars($ped['nombre_cliente'], ENT_QUOTES, 'UTF-8'); ?></td>
                                <td><?php echo htmlspecialchars($ped['telefono_cliente'], ENT_QUOTES, 'UTF-8'); ?></td>
                                <td><?php echo htmlspecialchars(date('d/m/Y H:i', strtotime($ped['fecha_retiro'])), ENT_QUOTES, 'UTF-8'); ?> hs</td>
                                <td class="celda-dias"><?php echo htmlspecialchars($ped['resumen_productos'], ENT_QUOTES, 'UTF-8'); ?></td>
                                <td><strong>$<?php echo (int)$ped['total']; ?></strong></td>
                                <td>
                                    <span class="etiqueta-estado <?php echo $clase_estado; ?>">
                                        <?php echo htmlspecialchars($ped['estado'], ENT_QUOTES, 'UTF-8'); ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </section>
</div>

<?php
require_once '../includes/footer.php';
?>