<?php

require_once '../includes/auth.php';
require_once '../config/conexion.php';

$ruta_base = "../";
$pagina_panel = "calendario";
$titulo_pagina = "Calendario Mensual - Panel MAI Panadería";

$nombres_meses = array(
    1 => 'Enero',
    2 => 'Febrero',
    3 => 'Marzo',
    4 => 'Abril',
    5 => 'Mayo',
    6 => 'Junio',
    7 => 'Julio',
    8 => 'Agosto',
    9 => 'Septiembre',
    10 => 'Octubre',
    11 => 'Noviembre',
    12 => 'Diciembre'
);

$mes_actual = (int)date('n');
$anio_actual = (int)date('Y');

$mes_seleccionado = (isset($_GET['mes']) && is_numeric($_GET['mes'])) ? (int)$_GET['mes'] : $mes_actual;
$anio_seleccionado = (isset($_GET['anio']) && is_numeric($_GET['anio'])) ? (int)$_GET['anio'] : $anio_actual;

if ($mes_seleccionado < 1 || $mes_seleccionado > 12) {
    $mes_seleccionado = $mes_actual;
}
if ($anio_seleccionado < 2020 || $anio_seleccionado > 2035) {
    $anio_seleccionado = $anio_actual;
}

$primer_dia_ts = mktime(0, 0, 0, $mes_seleccionado, 1, $anio_seleccionado);
$total_dias_mes = (int)date('t', $primer_dia_ts);
$dia_semana_inicio = (int)date('N', $primer_dia_ts);

$mes_ant = $mes_seleccionado - 1;
$anio_ant = $anio_seleccionado;
if ($mes_ant < 1) {
    $mes_ant = 12;
    $anio_ant--;
}

$mes_sig = $mes_seleccionado + 1;
$anio_sig = $anio_seleccionado;
if ($mes_sig > 12) {
    $mes_sig = 1;
    $anio_sig++;
}

$fecha_inicio_mes = date('Y-m-01', $primer_dia_ts);
$fecha_fin_mes = date('Y-m-t', $primer_dia_ts);

$pedidos_por_dia = array();
$total_pedidos_mes = 0;

$sql = "SELECT id_pedido, nombre_cliente, fecha_retiro, estado
        FROM pedidos
        WHERE DATE(fecha_retiro) >= ? AND DATE(fecha_retiro) <= ?
        ORDER BY fecha_retiro ASC";
$stmt = $conexion->prepare($sql);

if ($stmt) {
    $stmt->bind_param("ss", $fecha_inicio_mes, $fecha_fin_mes);
    $stmt->execute();
    $resultado = $stmt->get_result();

    while ($fila = $resultado->fetch_assoc()) {
        $dia_num = (int)date('j', strtotime($fila['fecha_retiro']));
        if (!isset($pedidos_por_dia[$dia_num])) {
            $pedidos_por_dia[$dia_num] = array();
        }
        $pedidos_por_dia[$dia_num][] = $fila;
        $total_pedidos_mes++;
    }
    <?php
// --- Conteo diario de pedidos (agrupado) ---
$sql_agg = "SELECT DAY(fecha_retiro) AS dia, COUNT(*) AS total FROM pedidos WHERE MONTH(fecha_retiro) = ? AND YEAR(fecha_retiro) = ? GROUP BY DAY(fecha_retiro)";
$stmt_agg = $conexion->prepare($sql_agg);
if ($stmt_agg) {
    $stmt_agg->bind_param("ii", $mes_seleccionado, $anio_seleccionado);
    $stmt_agg->execute();
    $resultado_agg = $stmt_agg->get_result();
    $conteo_diario = [];
    while ($fila_agg = $resultado_agg->fetch_assoc()) {
        $dia_num = (int)$fila_agg['dia'];
        $conteo_diario[$dia_num] = (int)$fila_agg['total'];
    }
    $stmt_agg->close();
    // Actualizamos total de pedidos del mes usando el conteo agregado
    $total_pedidos_mes = array_sum($conteo_diario);
} else {
    // Si falla la preparación, dejamos contadores vacíos
    $conteo_diario = [];
    $total_pedidos_mes = 0;
}
?>
}

$fecha_hoy_str = date('Y-m-d');

require_once '../includes/header_panel.php';
?>

<div class="contenedor contenedor-panel">
    <div class="cabecera-seccion-panel">
        <h2>Calendario Mensual de Pedidos</h2>
        <p>Visualizá la carga de encargos de cada día del mes y accedé directo a la agenda diaria.</p>
    </div>

    <form action="calendario.php" method="GET" class="barra-filtros-calendario">
        <div class="filtro-item">
            <label for="mes">Mes:</label>
            <select id="mes" name="mes">
                <?php foreach ($nombres_meses as $num_mes => $nombre_mes): ?>
                    <option value="<?php echo $num_mes; ?>" <?php echo ($mes_seleccionado === $num_mes) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($nombre_mes, ENT_QUOTES, 'UTF-8'); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="filtro-item">
            <label for="anio">Año:</label>
            <select id="anio" name="anio">
                <?php for ($a = $anio_actual - 2; $a <= $anio_actual + 3; $a++): ?>
                    <option value="<?php echo $a; ?>" <?php echo ($anio_seleccionado === $a) ? 'selected' : ''; ?>>
                        <?php echo $a; ?>
                    </option>
                <?php endfor; ?>
            </select>
        </div>

        <div class="filtro-botones">
            <button type="submit" class="btn btn-primario">Consultar Mes</button>
            <a href="calendario.php" class="btn btn-secundario">Mes Actual</a>
        </div>
    </form>

    <div class="calendario-navegacion-mes">
        <a href="calendario.php?mes=<?php echo $mes_ant; ?>&anio=<?php echo $anio_ant; ?>" class="btn btn-secundario btn-pequeno">
            ← Mes Anterior
        </a>
        <div class="calendario-titulo-central">
            <h3><?php echo htmlspecialchars($nombres_meses[$mes_seleccionado] . " " . $anio_seleccionado, ENT_QUOTES, 'UTF-8'); ?></h3>
            <span>Total en el mes: <strong><?php echo $total_pedidos_mes; ?></strong> encargo(s)</span>
        </div>
        <a href="calendario.php?mes=<?php echo $mes_sig; ?>&anio=<?php echo $anio_sig; ?>" class="btn btn-secundario btn-pequeno">
            Mes Siguiente →
        </a>
    </div>

    <div class="calendario-contenedor">
        <div class="calendario-cabecera-dias">
            <div class="calendario-dia-nombre">Lunes</div>
            <div class="calendario-dia-nombre">Martes</div>
            <div class="calendario-dia-nombre">Miércoles</div>
            <div class="calendario-dia-nombre">Jueves</div>
            <div class="calendario-dia-nombre">Viernes</div>
            <div class="calendario-dia-nombre">Sábado</div>
            <div class="calendario-dia-nombre">Domingo</div>
        </div>

        <div class="calendario-grid-7col">
            <?php
            for ($vacio = 1; $vacio < $dia_semana_inicio; $vacio++):
            ?>
                <div class="calendario-celda calendario-celda-vacia"></div>
            <?php endfor; ?>

            <?php
            for ($dia = 1; $dia <= $total_dias_mes; $dia++):
                $fecha_celda = sprintf('%04d-%02d-%02d', $anio_seleccionado, $mes_seleccionado, $dia);
                $es_hoy = ($fecha_celda === $fecha_hoy_str);
                $pedidos_del_dia = isset($pedidos_por_dia[$dia]) ? $pedidos_por_dia[$dia] : array();
                $cantidad_dia = count($pedidos_del_dia);

                $clases_celda = "calendario-celda";
                if ($es_hoy) {
                    $clases_celda .= " calendario-celda-hoy";
                }
                if ($cantidad_dia > 0) {
                    $clases_celda .= " calendario-celda-con-pedidos";
                }
            ?>
                <div class="<?php echo $clases_celda; ?>">
                    <div class="calendario-celda-top">
                        <span class="calendario-numero-dia"><?php echo $dia; ?></span>
                        <?php if ($es_hoy): ?>
                            <span class="calendario-badge-hoy">Hoy</span>
                        <?php endif; ?>
                    </div>

                    <?php if ($cantidad_dia > 0): ?>
                        <div class="calendario-resumen-dia">
                            <span class="calendario-contador-dia">
                                <?php echo $cantidad_dia; ?> encargo<?php echo ($cantidad_dia > 1) ? 's' : ''; ?>
                            </span>

                            <ul class="calendario-lista-mini">
                                <?php
                                $mostrados = 0;
                                foreach ($pedidos_del_dia as $ped_mini):
                                    if ($mostrados >= 3) {
                                        break;
                                    }
                                    $clase_est = "estado-pendiente";
                                    if ($ped_mini['estado'] === 'En preparación') {
                                        $clase_est = "estado-preparacion";
                                    } elseif ($ped_mini['estado'] === 'Listo') {
                                        $clase_est = "estado-listo";
                                    } elseif ($ped_mini['estado'] === 'Entregado') {
                                        $clase_est = "estado-entregado";
                                    }
                                    $hora_mini = date('H:i', strtotime($ped_mini['fecha_retiro']));
                                    $mostrados++;
                                ?>
                                    <li class="calendario-item-mini <?php echo $clase_est; ?>">
                                        <strong><?php echo $hora_mini; ?></strong>
                                        <?php echo htmlspecialchars($ped_mini['nombre_cliente'], ENT_QUOTES, 'UTF-8'); ?>
                                    </li>
                                <?php endforeach; ?>
                            </ul>

                            <?php if ($cantidad_dia > 3): ?>
                                <small class="calendario-mas-pedidos">+<?php echo ($cantidad_dia - 3); ?> más...</small>
                            <?php endif; ?>

                            <a href="agenda.php?vista=diaria&fecha=<?php echo $fecha_celda; ?>" class="calendario-link-agenda">
                                Ver en agenda →
                            </a>
                        </div>
                    <?php else: ?>
                        <div class="calendario-sin-pedidos">Sin encargos</div>
                    <?php endif; ?>
                </div>
            <?php endfor; ?>

            <?php
            $celdas_usadas = ($dia_semana_inicio - 1) + $total_dias_mes;
            $resto_semana = $celdas_usadas % 7;
            if ($resto_semana !== 0):
                $celdas_faltantes = 7 - $resto_semana;
                for ($v = 1; $v <= $celdas_faltantes; $v++):
            ?>
                    <div class="calendario-celda calendario-celda-vacia"></div>
            <?php
                endfor;
            endif;
            ?>
        </div>
    </div>
</div>

<?php
require_once '../includes/footer.php';
?>