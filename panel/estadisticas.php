<?php
// panel/estadisticas.php

require_once '../includes/auth.php';

requerir_administrador();

require_once '../config/conexion.php';

$ruta_base = "../";
$pagina_panel = "estadisticas";
$titulo_pagina = "Estadísticas de Ventas - Panel MAI Panadería";

// Procesar selector de período (semanal, mensual o anual) y fecha base
$periodo = isset($_GET['periodo']) ? trim($_GET['periodo']) : 'mensual';
if ($periodo !== 'semanal' && $periodo !== 'mensual' && $periodo !== 'anual') {
    $periodo = 'mensual';
}

$fecha_base = (isset($_GET['fecha']) && !empty(trim($_GET['fecha']))) ? trim($_GET['fecha']) : date('Y-m-d');
$ts_base = strtotime($fecha_base);
if ($ts_base === false) {
    $fecha_base = date('Y-m-d');
    $ts_base = time();
}

// Procesar rango de fechas ($fecha_desde y $fecha_hasta) según el período seleccionado
if ($periodo === 'semanal') {
    // Últimos 7 días hasta la fecha de referencia
    $fecha_desde = date('Y-m-d', strtotime('-6 days', $ts_base));
    $fecha_hasta = date('Y-m-d', $ts_base);
    $etiqueta_periodo = "Período Semanal (" . date('d/m/Y', strtotime($fecha_desde)) . " al " . date('d/m/Y', strtotime($fecha_hasta)) . ")";
} elseif ($periodo === 'anual') {
    // Todo el año de la fecha de referencia
    $anio_ref = date('Y', $ts_base);
    $fecha_desde = $anio_ref . "-01-01";
    $fecha_hasta = $anio_ref . "-12-31";
    $etiqueta_periodo = "Período Anual (Año " . $anio_ref . ")";
} else {
    // Todo el mes de la fecha de referencia (por defecto)
    $fecha_desde = date('Y-m-01', $ts_base);
    $fecha_hasta = date('Y-m-t', $ts_base);
    $etiqueta_periodo = "Período Mensual (" . date('m/Y', $ts_base) . ")";
}

// Contemplar períodos sin ventas inicializando valores en cero sin errores
$total_recaudado = 0;
$cantidad_encargos = 0;
$producto_mas_vendido_nombre = "Sin ventas registradas";
$producto_mas_vendido_unidades = 0;
$ranking_productos = array();

// Consulta SQL de métricas y ventas filtrando únicamente pedidos concretados (WHERE p.estado = 'Entregado')
// con funciones de agregación (COALESCE(SUM(dp.cantidad * dp.precio_unitario), 0), COUNT(DISTINCT p.id_pedido))
$sql_metricas = "SELECT 
                    COALESCE(SUM(dp.cantidad * dp.precio_unitario), 0) AS total_recaudado,
                    COUNT(DISTINCT p.id_pedido) AS total_pedidos
                 FROM pedidos p
                 LEFT JOIN detalle_pedidos dp ON p.id_pedido = dp.id_pedido
                 WHERE p.estado = 'Entregado'
                   AND DATE(p.fecha_retiro) >= ?
                   AND DATE(p.fecha_retiro) <= ?";
$stmt_met = $conexion->prepare($sql_metricas);

if ($stmt_met) {
    $stmt_met->bind_param("ss", $fecha_desde, $fecha_hasta);
    $stmt_met->execute();
    $res_met = $stmt_met->get_result();
    if ($fila_met = $res_met->fetch_assoc()) {
        $total_recaudado = (int)$fila_met['total_recaudado'];
        $cantidad_encargos = (int)$fila_met['total_pedidos'];
    }
    $stmt_met->close();
}

// Consulta SQL para obtener el producto más vendido con agrupamiento y límite:
// (GROUP BY dp.id_producto ORDER BY SUM(dp.cantidad) DESC LIMIT 1) filtrando pedidos concretados (WHERE ped.estado = 'Entregado')
$sql_top_producto = "SELECT 
                        p.nombre,
                        SUM(dp.cantidad) AS unidades_vendidas
                     FROM detalle_pedidos dp
                     INNER JOIN pedidos ped ON dp.id_pedido = ped.id_pedido
                     INNER JOIN productos p ON dp.id_producto = p.id_producto
                     WHERE ped.estado = 'Entregado'
                       AND DATE(ped.fecha_retiro) >= ?
                       AND DATE(ped.fecha_retiro) <= ?
                     GROUP BY dp.id_producto, p.nombre
                     ORDER BY SUM(dp.cantidad) DESC
                     LIMIT 1";

$stmt_top = $conexion->prepare($sql_top_producto);

if ($stmt_top) {
    $stmt_top->bind_param("ss", $fecha_desde, $fecha_hasta);
    $stmt_top->execute();
    $res_top = $stmt_top->get_result();
    if ($fila_top = $res_top->fetch_assoc()) {
        $producto_mas_vendido_nombre = $fila_top['nombre'];
        $producto_mas_vendido_unidades = (int)$fila_top['unidades_vendidas'];
    }
    $stmt_top->close();
}

// Detalle y ranking completo de ventas por producto (WHERE ped.estado = 'Entregado')
$sql_ranking = "SELECT 
                    p.id_producto,
                    p.nombre,
                    p.categoria,
                    SUM(dp.cantidad) AS unidades_vendidas,
                    COALESCE(SUM(dp.cantidad * dp.precio_unitario), 0) AS subtotal_recaudado
                FROM detalle_pedidos dp
                INNER JOIN pedidos ped ON dp.id_pedido = ped.id_pedido
                INNER JOIN productos p ON dp.id_producto = p.id_producto
                WHERE ped.estado = 'Entregado'
                  AND DATE(ped.fecha_retiro) >= ?
                  AND DATE(ped.fecha_retiro) <= ?
                GROUP BY p.id_producto, p.nombre, p.categoria
                ORDER BY unidades_vendidas DESC, subtotal_recaudado DESC";

$stmt_rank = $conexion->prepare($sql_ranking);

if ($stmt_rank) {
    $stmt_rank->bind_param("ss", $fecha_desde, $fecha_hasta);
    $stmt_rank->execute();
    $res_rank = $stmt_rank->get_result();
    while ($fila_rank = $res_rank->fetch_assoc()) {
        $ranking_productos[] = $fila_rank;
    }
    $stmt_rank->close();
}

require_once '../includes/header_panel.php';
?>

<div class="contenedor contenedor-panel">
    <div class="cabecera-seccion-panel">
        <h2>Estadísticas de Ventas y Desempeño</h2>
        <p>Consultá el resumen comercial de la panadería filtrando por período Semanal, Mensual o Anual.</p>
    </div>

    <!-- Formulario selector de período (Semanal, Mensual, Anual) -->
    <form action="estadisticas.php" method="GET" class="barra-filtros-estadisticas">
        <div class="filtro-item">
            <label for="periodo">Período de análisis:</label>
            <select id="periodo" name="periodo">
                <option value="semanal" <?php echo ($periodo === 'semanal') ? 'selected' : ''; ?>>Semanal (7 días)</option>
                <option value="mensual" <?php echo ($periodo === 'mensual') ? 'selected' : ''; ?>>Mensual</option>
                <option value="anual" <?php echo ($periodo === 'anual') ? 'selected' : ''; ?>>Anual</option>
            </select>
        </div>

        <div class="filtro-item">
            <label for="fecha">Fecha de referencia:</label>
            <input
                type="date"
                id="fecha"
                name="fecha"
                value="<?php echo htmlspecialchars($fecha_base, ENT_QUOTES, 'UTF-8'); ?>"
            >
        </div>

        <div class="filtro-botones">
            <button type="submit" class="btn btn-primario">Calcular Estadísticas</button>
            <a href="estadisticas.php" class="btn btn-secundario">Mes Actual</a>
        </div>
    </form>

    <!-- Barra informativa con el período activo -->
    <div class="resumen-rango-agenda">
        <span>Mostrando métricas para: <strong><?php echo htmlspecialchars($etiqueta_periodo, ENT_QUOTES, 'UTF-8'); ?></strong></span>
        <span>Rango: <strong><?php echo date('d/m/Y', strtotime($fecha_desde)); ?></strong> al <strong><?php echo date('d/m/Y', strtotime($fecha_hasta)); ?></strong></span>
    </div>

    <!-- Tarjetas de métricas (Total recaudado, Cantidad de encargos y Producto más vendido) -->
    <section class="metricas-grid">
        <!-- Tarjeta 1: Total Recaudado -->
        <article class="tarjeta-metrica metrica-recaudacion">
            <div class="metrica-cabecera">
                <span class="metrica-titulo">Total Recaudado</span>
                <span class="metrica-icono" aria-hidden="true">💰</span>
            </div>
            <p class="metrica-valor">$<?php echo number_format($total_recaudado, 0, ',', '.'); ?></p>
            <span class="metrica-subtexto">Ingresos acumulados en el período seleccionado</span>
        </article>

        <!-- Tarjeta 2: Cantidad de Encargos -->
        <article class="tarjeta-metrica metrica-encargos">
            <div class="metrica-cabecera">
                <span class="metrica-titulo">Cantidad de Encargos</span>
                <span class="metrica-icono" aria-hidden="true">📦</span>
            </div>
            <p class="metrica-valor"><?php echo $cantidad_encargos; ?></p>
            <span class="metrica-subtexto">Pedidos entregados en el período seleccionado</span>
        </article>

        <!-- Tarjeta 3: Producto Más Vendido -->
        <article class="tarjeta-metrica metrica-estrella">
            <div class="metrica-cabecera">
                <span class="metrica-titulo">Producto Más Vendido</span>
                <span class="metrica-icono" aria-hidden="true">🏆</span>
            </div>
            <p class="metrica-valor metrica-valor-producto">
                <?php echo htmlspecialchars($producto_mas_vendido_nombre, ENT_QUOTES, 'UTF-8'); ?>
            </p>
            <span class="metrica-subtexto">
                <?php if ($producto_mas_vendido_unidades > 0): ?>
                    <strong><?php echo $producto_mas_vendido_unidades; ?></strong> unidad(es) vendidas en este período
                <?php else: ?>
                    Aún no se registran unidades vendidas
                <?php endif; ?>
            </span>
        </article>
    </section>

    <!-- Desglose en tabla reutilizando los estilos del panel (.seccion-listado y .tabla-productos) -->
    <section class="seccion-listado">
        <div class="cabecera-tabla-resultados">
            <h3>Detalle de Ventas por Producto</h3>
            <span class="contador-resultados">
                Productos con movimiento: <strong><?php echo count($ranking_productos); ?></strong>
            </span>
        </div>

        <?php if (empty($ranking_productos)): ?>
            <div class="mensaje-vacio">
                <h3>No hay ventas registradas para este período</h3>
                <p>Probá seleccionando otro rango de fechas o cambiando el filtro a Mensual o Anual.</p>
            </div>
        <?php else: ?>
            <div class="tabla-responsive">
                <table class="tabla-productos">
                    <thead>
                        <tr>
                            <th>Posición</th>
                            <th>Producto</th>
                            <th>Categoría</th>
                            <th>Unidades Vendidas</th>
                            <th>Total Generado</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($ranking_productos as $indice => $item_rank): ?>
                            <tr>
                                <td>
                                    <span class="badge-posicion <?php echo ($indice === 0) ? 'posicion-oro' : ''; ?>">
                                        #<?php echo ($indice + 1); ?>
                                    </span>
                                </td>
                                <td><strong><?php echo htmlspecialchars($item_rank['nombre'], ENT_QUOTES, 'UTF-8'); ?></strong></td>
                                <td><?php echo htmlspecialchars($item_rank['categoria'], ENT_QUOTES, 'UTF-8'); ?></td>
                                <td><?php echo (int)$item_rank['unidades_vendidas']; ?> u.</td>
                                <td><strong>$<?php echo number_format((int)$item_rank['subtotal_recaudado'], 0, ',', '.'); ?></strong></td>
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
