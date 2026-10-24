<?php

$fecha_hora_candidata = isset($_POST['fecha_hora_retiro']) ? $_POST['fecha_hora_retiro'] : '';

$errores = [];

if (empty($fecha_hora_candidata)) {
    $errores[] = "Debe seleccionar la fecha y hora de retiro.";
} else {
    $ts_retiro = strtotime($fecha_hora_candidata);
    
    $ts_minimo_permitido = time() + (24 * 60 * 60);

    if ($ts_retiro < $ts_minimo_permitido) {
        $errores[] = "Los pedidos deben realizarse con al menos 1 día de anticipación.";
    }
}

if (!empty($errores)) {
    echo $errores[0];
} else {
    echo "Fecha válida, El pedido puede continuar.";
}
?>