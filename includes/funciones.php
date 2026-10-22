<?php

// Definimos las 24 horas de anticipacion como constante
define('HORAS_ANTICIPACION_MINIMA', 24);

/**
 * 1. Calcular si hay 24 horas de anticipación (RF-07)
 */
function validar_anticipacion_minima($fecha_hora_str) {
    // Convertimos la fecha del pedido a timestamp (timestamp (segundos transcurridos))
    $ts_retiro = strtotime($fecha_hora_str);
    
    if ($ts_retiro === false) {
        return false;
    }

    // Corregimos el error del código original agregando el asterisco (*)
    // Multiplicamos 24 horas por 3600 segundos (segundos en una hora)
    $ts_minimo = time() + (HORAS_ANTICIPACION_MINIMA * 3600);

    return ($ts_retiro >= $ts_minimo);
}

/**
 * 2. Convertir una fecha al día de la semana en español (RF-08)
 */
function obtener_dia_semana($fecha_str) {
    $ts = strtotime($fecha_str);
    
    if ($ts === false) {
        return '';
    }

    // 'N' devuelve el día como número: 1 (Lunes) a 7 (Domingo)
    $num_dia = (int)date('N', $ts);

    $mapa = [
        1 => 'Lunes',
        2 => 'Martes',
        3 => 'Miércoles',
        4 => 'Jueves',
        5 => 'Viernes',
        6 => 'Sábado',
        7 => 'Domingo'
    ];

    return isset($mapa[$num_dia]) ? $mapa[$num_dia] : '';
}

/**
 * 3. Determinar el turno del pedido (H.U-04)
 */
function determinar_turno($fecha_hora_str) {
    $ts = strtotime($fecha_hora_str);
    
    if ($ts === false) {
        return 'No especificado';
    }

    // Extraemos la hora en formato de 24 horas (de 00 a 23)
    $hora = (int)date('H', $ts);

    // Definimos el turno según el horario de la panadería
    if ($hora >= 6 && $hora < 13) {
        return 'Mañana';
    } elseif ($hora >= 13 && $hora < 20) {
        return 'Tarde';
    } else {
        return 'Fuera de horario';
    }
}

?> 