<?php
// carrito.php
// Vista del carrito de compras y formulario de datos y fecha/hora de retiro (H.U-02 / HU02-T02)

session_start();
require_once __DIR__ . '/config/conexion.php';

$ruta_base = "";
$pagina_actual = "carrito";
$titulo_pagina = "Mi Pedido - Panadería La Ideal";

// Recuperar posibles errores enviados desde acciones/pedido_guardar.php
$errores_pedido = array();
if (isset($_SESSION["errores_pedido"]) && !empty($_SESSION["errores_pedido"])) {
    $errores_pedido = $_SESSION["errores_pedido"];
    unset($_SESSION["errores_pedido"]);
}

// Recuperar datos previos del formulario si el cliente tuvo un error al enviar
$datos_previos = isset($_SESSION["datos_form_pedido"]) ? $_SESSION["datos_form_pedido"] : array();
$nombre_previo = isset($datos_previos["nombre_cliente"]) ? $datos_previos["nombre_cliente"] : "";
$telefono_previo = isset($datos_previos["telefono_cliente"]) ? $datos_previos["telefono_cliente"] : "";
$fecha_dia_previa = isset($datos_previos["fecha_retiro_dia"]) ? $datos_previos["fecha_retiro_dia"] : "";
$fecha_hora_previa = isset($datos_previos["fecha_retiro_hora"]) ? $datos_previos["fecha_retiro_hora"] : "";

// Fecha mínima inicial para el input date (mañana)
$fecha_minima = date("Y-m-d", strtotime("+1 day"));

require_once __DIR__ . '/includes/header_publico.php';
?>

<section class="banner-catalogo">
    <div class="contenedor">
        <span class="subtitulo-seccion">Resumen de tu encargo</span>
        <h1 class="titulo-seccion">Mi Pedido (Carrito)</h1>
        <p class="descripcion-seccion">
            Revisá los productos seleccionados y completá tus datos y fecha de retiro con al menos 24 horas de anticipación.
        </p>
    </div>
</section>

<section class="contenedor seccion-carrito">

    <!-- Mostrar errores de validación del servidor si existen -->
    <?php if (!empty($errores_pedido)): ?>
        <div class="alerta alerta-error">
            <strong>Por favor revisá los siguientes datos:</strong>
            <ul class="lista-errores">
                <?php foreach ($errores_pedido as $err): ?>
                    <li><?php echo htmlspecialchars($err, ENT_QUOTES, 'UTF-8'); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <?php if (empty($_SESSION["carrito"])): ?>
        <div class="mensaje-vacio">
            <h3>Tu carrito de pedidos está vacío</h3>
            <p>Todavía no agregaste productos a tu encargo. Explorá nuestro catálogo para armar tu pedido.</p>
            <a href="catalogo.php" class="btn btn-primario">Ir al Catálogo</a>
        </div>
    <?php else: ?>
        <div class="carrito-layout">

            <!-- Columna izquierda: Lista de productos en el carrito -->
            <div class="carrito-productos-caja">
                <div class="carrito-cabecera-caja">
                    <h2>Productos Seleccionados</h2>
                    <form action="acciones/carrito_quitar.php" method="POST">
                        <input type="hidden" name="accion" value="vaciar">
                        <button type="submit" class="btn btn-secundario btn-pequeno">Vaciar Carrito</button>
                    </form>
                </div>

                <div class="carrito-lista">
                    <?php
                    $total_general = 0;
                    foreach ($_SESSION["carrito"] as $id => $cantidad):
                        $id_limpio = (int)$id;
                        $cantidad_limpia = (int)$cantidad;

                        $sql_item = "SELECT id_producto, nombre, precio, categoria, dias_habilitados FROM productos WHERE id_producto = ?";
                        $stmt_item = $conexion->prepare($sql_item);
                        $stmt_item->bind_param("i", $id_limpio);
                        $stmt_item->execute();
                        $res_item = $stmt_item->get_result();
                        $item = $res_item->fetch_assoc();
                        $stmt_item->close();

                        if ($item):
                            $subtotal = (int)$item["precio"] * $cantidad_limpia;
                            $total_general = $total_general + $subtotal;
                    ?>
                        <div class="carrito-fila-item">
                            <div class="carrito-item-info">
                                <h3><?php echo htmlspecialchars($item["nombre"], ENT_QUOTES, 'UTF-8'); ?></h3>
                                <p class="carrito-item-detalle">
                                    Precio unitario: $<?php echo (int)$item["precio"]; ?> · Cantidad: <strong><?php echo $cantidad_limpia; ?></strong>
                                </p>
                                <small class="carrito-item-dias">
                                    Días de elaboración: <?php echo htmlspecialchars(str_replace(",", ", ", $item["dias_habilitados"]), ENT_QUOTES, 'UTF-8'); ?>
                                </small>
                            </div>

                            <div class="carrito-item-derecha">
                                <span class="carrito-item-subtotal">$<?php echo $subtotal; ?></span>
                                <form action="acciones/carrito_quitar.php" method="POST">
                                    <input type="hidden" name="id_producto" value="<?php echo (int)$item["id_producto"]; ?>">
                                    <input type="hidden" name="accion" value="eliminar">
                                    <button type="submit" class="btn-quitar-item" title="Quitar producto">Quitar</button>
                                </form>
                            </div>
                        </div>
                    <?php
                        endif;
                    endforeach;
                    ?>
                </div>

                <div class="carrito-total-barra">
                    <span>Total estimado a pagar al retirar:</span>
                    <strong class="carrito-total-monto">$<?php echo $total_general; ?></strong>
                </div>

                <div class="carrito-seguir-comprando">
                    <a href="catalogo.php" class="btn btn-secundario">&larr; Seguir agregando productos</a>
                </div>
            </div>

            <!-- Columna derecha: HU02-T02 Formulario de datos y fecha/hora de retiro -->
            <div class="carrito-formulario-caja">
                <h2>Datos para el Retiro</h2>
                <p class="carrito-form-ayuda">
                    Recordá que todos los pedidos deben realizarse con al menos <strong>24 horas de anticipación</strong>.
                </p>

                <!-- Contenedor para alertas de validación de JavaScript -->
                <div id="errorValidacionPedidoJS" class="alerta alerta-error" style="display: none;"></div>

                <form action="acciones/pedido_guardar.php" method="POST" id="formPedidoCarrito" class="formulario-pedido">
                    <div class="grupo-formulario">
                        <label for="nombre_cliente">Nombre completo:</label>
                        <input
                            type="text"
                            id="nombre_cliente"
                            name="nombre_cliente"
                            required
                            placeholder="Ej: María Rodríguez"
                            value="<?php echo htmlspecialchars($nombre_previo, ENT_QUOTES, 'UTF-8'); ?>"
                        >
                    </div>

                    <div class="grupo-formulario">
                        <label for="telefono_cliente">Teléfono de contacto:</label>
                        <input
                            type="tel"
                            id="telefono_cliente"
                            name="telefono_cliente"
                            required
                            placeholder="Ej: 099 123 456"
                            value="<?php echo htmlspecialchars($telefono_previo, ENT_QUOTES, 'UTF-8'); ?>"
                        >
                    </div>

                    <div class="grupo-formulario">
                        <label for="fecha_retiro_dia">Fecha de retiro:</label>
                        <input
                            type="date"
                            id="fecha_retiro_dia"
                            name="fecha_retiro_dia"
                            required
                            min="<?php echo $fecha_minima; ?>"
                            value="<?php echo htmlspecialchars($fecha_dia_previa, ENT_QUOTES, 'UTF-8'); ?>"
                        >
                    </div>

                    <div class="grupo-formulario">
                        <label for="fecha_retiro_hora">Hora de retiro (07:00 a 20:00 hs):</label>
                        <input
                            type="time"
                            id="fecha_retiro_hora"
                            name="fecha_retiro_hora"
                            required
                            min="07:00"
                            max="20:00"
                            value="<?php echo !empty($fecha_hora_previa) ? htmlspecialchars($fecha_hora_previa, ENT_QUOTES, 'UTF-8') : '10:00'; ?>"
                        >
                    </div>

                    <button type="submit" class="btn btn-primario btn-bloque">Confirmar Pedido</button>
                </form>
            </div>

        </div>
    <?php endif; ?>

</section>

<?php
require_once __DIR__ . '/includes/footer.php';
?>
