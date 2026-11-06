<?php
// panel/agenda.php
// Agenda de pedidos para cocina y mostrador (H.U-04: HU04-T01, HU04-T02, HU04-T03 y HU04-T04)

require_once '../includes/auth.php';
require_once '../config/conexion.php';
require_once '../includes/funciones.php';

$ruta_base = "../";
$pagina_panel = "agenda";
$titulo_pagina = "Agenda de Pedidos - Panel MAI Panadería";

$mensaje_exito = "";
if (isset($_SESSION["mensaje_exito"])) {
    $mensaje_exito = $_SESSION["mensaje_exito"];
    unset($_SESSION["mensaje_exito"]);
}

$mensaje_error = "";
if (isset($_SESSION["mensaje_error"])) {
    $mensaje_error = $_SESSION["mensaje_error"];
    unset($_SESSION["mensaje_error"]);
}

$vista = isset($_GET['vista']) ? trim($_GET['vista']) : 'diaria';
$fecha_base = (isset($_GET['fecha']) && !empty(trim($_GET['fecha']))) ? trim($_GET['fecha']) : date('Y-m-d');
$filtro_estado = isset($_GET['estado']) ? trim($_GET['estado']) : '';

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

$fecha_desde_dt = $fecha_desde . ' 00:00:00';
$fecha_hasta_dt = $fecha_hasta . ' 23:59:59';

// HU04-T02: Consulta SQL preparada para filtrar pedidos por rango de fechas y ordenarlos cronológicamente
$sql = "SELECT id_pedido, nombre_cliente, telefono_cliente, fecha_retiro, estado, fecha_modificacion, fecha_creacion
        FROM pedidos
        WHERE (? = 'todos' OR (fecha_retiro >= ? AND fecha_retiro <= ?))
          AND (? = '' OR estado = ?)
        ORDER BY fecha_retiro ASC, id_pedido ASC";

$pedidos_agenda = array();
$stmt = $conexion->prepare($sql);

if ($stmt) {
    $stmt->bind_param("sssss", $vista, $fecha_desde_dt, $fecha_hasta_dt, $filtro_estado, $filtro_estado);
    $stmt->execute();
    $res = $stmt->get_result();

    // HU04-T03: Consulta SQL preparada una sola vez para obtener el detalle de productos de cada pedido
    $sql_det = "SELECT dp.cantidad, dp.precio_unitario, p.nombre, p.categoria
                FROM detalle_pedidos dp
                INNER JOIN productos p ON dp.id_producto = p.id_producto
                WHERE dp.id_pedido = ?";
    $stmt_det = $conexion->prepare($sql_det);

    while ($ped = $res->fetch_assoc()) {
        $id_p = (int)$ped['id_pedido'];
        $items_ped = array();
        $total_ped = 0;

        if ($stmt_det) {
            $stmt_det->bind_param("i", $id_p);
            $stmt_det->execute();
            $res_d = $stmt_det->get_result();

            while ($d = $res_d->fetch_assoc()) {
                $subtotal = (int)$d['precio_unitario'] * (int)$d['cantidad'];
                $total_ped = $total_ped + $subtotal;
                $items_ped[] = $d;
            }
        }

        $ped['items'] = $items_ped;
        $ped['total'] = $total_ped;
        $ped['turno'] = determinar_turno($ped['fecha_retiro']);

        $pedidos_agenda[] = $ped;
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
        <h2>Agenda de Pedidos de Cocina y Mostrador</h2>
        <p>Visualizá los encargos ordenados por horario de retiro y turno de elaboración (Diaria, Semanal o Mensual).</p>
    </div>

    <?php if (!empty($mensaje_exito)): ?>
        <div class="alerta alerta-exito"><?php echo htmlspecialchars($mensaje_exito, ENT_QUOTES, 'UTF-8'); ?></div>
    <?php endif; ?>

    <?php if (!empty($mensaje_error)): ?>
        <div class="alerta alerta-error"><?php echo htmlspecialchars($mensaje_error, ENT_QUOTES, 'UTF-8'); ?></div>
    <?php endif; ?>

    <!-- HU04-T01: Selector de vista (Diaria, Semanal o Mensual) y filtro de fecha -->
    <form action="agenda.php" method="GET" class="barra-filtros-agenda">
        <div class="filtro-item">
            <label for="vista">Vista de agenda:</label>
            <select id="vista" name="vista">
                <option value="diaria" <?php echo ($vista === 'diaria') ? 'selected' : ''; ?>>Diaria</option>
                <option value="semanal" <?php echo ($vista === 'semanal') ? 'selected' : ''; ?>>Semanal (7 días)</option>
                <option value="mensual" <?php echo ($vista === 'mensual') ? 'selected' : ''; ?>>Mensual</option>
                <option value="todos" <?php echo ($vista === 'todos') ? 'selected' : ''; ?>>Todos los pedidos</option>
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

        <div class="filtro-item">
            <label for="estado">Estado del pedido:</label>
            <select id="estado" name="estado">
                <option value="">Todos los estados</option>
                <option value="Pendiente" <?php echo ($filtro_estado === 'Pendiente') ? 'selected' : ''; ?>>Pendiente</option>
                <option value="En preparación" <?php echo ($filtro_estado === 'En preparación') ? 'selected' : ''; ?>>En preparación</option>
                <option value="Listo" <?php echo ($filtro_estado === 'Listo') ? 'selected' : ''; ?>>Listo</option>
                <option value="Entregado" <?php echo ($filtro_estado === 'Entregado') ? 'selected' : ''; ?>>Entregado</option>
            </select>
        </div>

        <div class="filtro-botones">
            <button type="submit" class="btn btn-primario">Actualizar Agenda</button>
            <a href="agenda.php?vista=todos" class="btn btn-secundario">Ver Todos</a>
        </div>
    </form>

    <!-- Resumen del período consultado -->
    <div class="resumen-rango-agenda">
        <?php if ($vista === 'todos'): ?>
            <span>Mostrando <strong>todos los pedidos registrados</strong></span>
        <?php elseif ($vista === 'diaria'): ?>
            <span>Pedidos para el día: <strong><?php echo date('d/m/Y', strtotime($fecha_desde)); ?></strong></span>
        <?php else: ?>
            <span>Período: <strong><?php echo date('d/m/Y', strtotime($fecha_desde)); ?></strong> al <strong><?php echo date('d/m/Y', strtotime($fecha_hasta)); ?></strong></span>
        <?php endif; ?>
        <span class="contador-pedidos-agenda">Total en vista: <strong><?php echo count($pedidos_agenda); ?></strong> pedido(s)</span>
    </div>

    <!-- HU04-T01 y HU04-T04: Estructura visual de fichas/tarjetas de pedidos -->
    <?php if (empty($pedidos_agenda)): ?>
        <div class="mensaje-vacio">
            <h3>No hay pedidos programados para este período</h3>
            <p>Probá cambiando la fecha de referencia o seleccionando la vista "Mensual" o "Todos los pedidos".</p>
            <a href="agenda.php?vista=todos" class="btn btn-secundario">Ver todos los pedidos</a>
        </div>
    <?php else: ?>
        <div class="agenda-grid">
            <?php foreach ($pedidos_agenda as $ped): ?>
                <?php
                // Determinar la clase de color según el estado del pedido (HU05-T03)
                $clase_estado = obtener_clase_estado($ped['estado']);
                ?>
                <article class="ficha-pedido ficha-<?php echo $clase_estado; ?>">
                    <div class="ficha-pedido-cabecera">
                        <div class="ficha-id-grupo">
                            <span class="ficha-id">Pedido #<?php echo (int)$ped['id_pedido']; ?></span>
                            <span class="ficha-turno"><?php echo htmlspecialchars($ped['turno'], ENT_QUOTES, 'UTF-8'); ?></span>
                        </div>
                        <span class="etiqueta-estado <?php echo $clase_estado; ?>">
                            <?php echo htmlspecialchars($ped['estado'], ENT_QUOTES, 'UTF-8'); ?>
                        </span>
                    </div>

                    <div class="ficha-pedido-cliente">
                        <p><strong>Cliente:</strong> <?php echo htmlspecialchars($ped['nombre_cliente'], ENT_QUOTES, 'UTF-8'); ?></p>
                        <p><strong>Teléfono:</strong> <?php echo htmlspecialchars($ped['telefono_cliente'], ENT_QUOTES, 'UTF-8'); ?></p>
                        <p><strong>Fecha y Hora de Retiro:</strong> <?php echo htmlspecialchars(date('d/m/Y H:i', strtotime($ped['fecha_retiro'])), ENT_QUOTES, 'UTF-8'); ?> hs</p>
                    </div>

                    <div class="ficha-pedido-productos">
                        <h4>Productos encargados:</h4>
                        <?php if (empty($ped['items'])): ?>
                            <p class="texto-vacio-panel">Sin detalle de productos.</p>
                        <?php else: ?>
                            <ul class="lista-productos-ficha">
                                <?php foreach ($ped['items'] as $item): ?>
                                    <li>
                                        <span class="cantidad-ficha"><?php echo (int)$item['cantidad']; ?>x</span>
                                        <span class="nombre-prod-ficha"><?php echo htmlspecialchars($item['nombre'], ENT_QUOTES, 'UTF-8'); ?></span>
                                        <span class="subtotal-prod-ficha">$<?php echo (int)$item['cantidad'] * (int)$item['precio_unitario']; ?></span>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        <?php endif; ?>
                    </div>

                    <div class="ficha-pedido-pie">
                        <span>Total del encargo:</span>
                        <strong class="ficha-total-monto">$<?php echo (int)$ped['total']; ?></strong>
                    </div>

                    <!-- HU05-T01: Selector interactivo en cada tarjeta con los 4 estados permitidos -->
                    <form action="acciones/pedido_estado.php" method="POST" class="form-estado-pedido">
                        <input type="hidden" name="id_pedido" value="<?php echo (int)$ped['id_pedido']; ?>">
                        <input type="hidden" name="vista_actual" value="<?php echo htmlspecialchars($vista, ENT_QUOTES, 'UTF-8'); ?>">
                        <input type="hidden" name="fecha_actual" value="<?php echo htmlspecialchars($fecha_base, ENT_QUOTES, 'UTF-8'); ?>">
                        <input type="hidden" name="estado_actual" value="<?php echo htmlspecialchars($filtro_estado, ENT_QUOTES, 'UTF-8'); ?>">

                        <label for="select_estado_<?php echo (int)$ped['id_pedido']; ?>" class="label-estado-pedido">
                            Cambiar estado:
                        </label>
                        <div class="controles-estado-pedido">
                            <select
                                id="select_estado_<?php echo (int)$ped['id_pedido']; ?>"
                                name="estado"
                                class="select-estado-pedido <?php echo $clase_estado; ?>"
                            >
                                <option value="Pendiente" <?php echo ($ped['estado'] === 'Pendiente') ? 'selected' : ''; ?>>Pendiente</option>
                                <option value="En preparación" <?php echo ($ped['estado'] === 'En preparación') ? 'selected' : ''; ?>>En preparación</option>
                                <option value="Listo" <?php echo ($ped['estado'] === 'Listo') ? 'selected' : ''; ?>>Listo</option>
                                <option value="Entregado" <?php echo ($ped['estado'] === 'Entregado') ? 'selected' : ''; ?>>Entregado</option>
                            </select>
                            <button type="submit" class="btn btn-primario btn-pequeno">Guardar</button>
                        </div>
                    </form>
                </article>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php
require_once '../includes/footer.php';
?>
