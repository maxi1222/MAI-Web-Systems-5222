<?php
// includes/funciones.php
// Funciones auxiliares de negocio: anticipación mínima, días de elaboración, turnos y listas de dominio

if (!defined('HORAS_ANTICIPACION_MINIMA')) {
    define('HORAS_ANTICIPACION_MINIMA', 24);
}

/**
 * 1. Calcular si hay al menos 24 horas de anticipación (RF-07)
 */
function validar_anticipacion_minima($fecha_hora_str) {
    $ts_retiro = strtotime($fecha_hora_str);

    if ($ts_retiro === false) {
        return false;
    }

    $ts_minimo = time() + (HORAS_ANTICIPACION_MINIMA * 3600);

    return ($ts_retiro >= $ts_minimo);
}

/**
 * 2. Convertir una fecha al día de la semana en español sin tildes (RF-08)
 */
function obtener_dia_semana($fecha_str) {
    $ts = strtotime($fecha_str);

    if ($ts === false) {
        return '';
    }

    // 'N' devuelve el día como número: 1 (Lunes) a 7 (Domingo)
    $num_dia = (int)date('N', $ts);

    $mapa = array(
        1 => 'Lunes',
        2 => 'Martes',
        3 => 'Miercoles',
        4 => 'Jueves',
        5 => 'Viernes',
        6 => 'Sabado',
        7 => 'Domingo'
    );

    return isset($mapa[$num_dia]) ? $mapa[$num_dia] : '';
}

/**
 * 3. Determinar el turno del pedido según la hora de retiro (H.U-04)
 */
function determinar_turno($fecha_hora_str) {
    $ts = strtotime($fecha_hora_str);

    if ($ts === false) {
        return 'No especificado';
    }

    $hora = (int)date('H', $ts);

    if ($hora >= 6 && $hora < 13) {
        return 'Turno Mañana';
    } elseif ($hora >= 13 && $hora < 20) {
        return 'Turno Tarde';
    } else {
        return 'Turno Noche';
    }
}

/**
 * 4. Obtener la clase CSS correspondiente al estado de un pedido (HU05-T03)
 */
function obtener_clase_estado($estado) {
    if ($estado === 'En preparación') {
        return 'estado-preparacion';
    } elseif ($estado === 'Listo') {
        return 'estado-listo';
    } elseif ($estado === 'Entregado') {
        return 'estado-entregado';
    }
    return 'estado-pendiente';
}

/**
 * 5. Obtener la lista oficial de categorías del catálogo
 */
function obtener_categorias_catalogo() {
    return array(
        'Panadería',
        'Facturería',
        'Tortas y Postres',
        'Pastelería',
        'Salados y Sandwichería'
    );
}

/**
 * 6. Obtener la lista oficial de días de elaboración (Lunes a Domingo)
 */
function obtener_dias_semana_catalogo() {
    return array(
        'Lunes',
        'Martes',
        'Miercoles',
        'Jueves',
        'Viernes',
        'Sabado',
        'Domingo'
    );
}

/**
 * 7. Normalizar tildes en nombres de días de la semana para comparaciones seguras
 */
function normalizar_texto_dias($texto) {
    return str_replace(
        array('á', 'é', 'í', 'ó', 'ú', 'Á', 'É', 'Í', 'Ó', 'Ú'),
        array('a', 'e', 'i', 'o', 'u', 'A', 'E', 'I', 'O', 'U'),
        (string)$texto
    );
}
?>
