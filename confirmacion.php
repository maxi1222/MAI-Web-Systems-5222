<?php
// confirmacion.php
// Vista de confirmación del pedido con bloqueo de edición y botón de aviso modal (H.U-02 y H.U-03)

require_once __DIR__ . '/config/conexion.php';

$ruta_base = "";
$pagina_actual = "carrito";
$titulo_pagina = "Confirmación de Pedido - Panadería La Ideal";

$id_pedido = isset($_GET["id"]) ? (int)$_GET["id"] : 0;

// Consultar la cabecera del pedido
$pedido = null;
if ($id_pedido > 0) {
    $sql = "SELECT id_pedido, nombre_cliente, telefono_cliente, fecha_retiro, estado, fecha_creacion FROM pedidos WHERE id_pedido = ?";
    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("i", $id_pedido);
    $stmt->execute();
    $resultado = $stmt->get_result();
    $pedido = $resultado->fetch_assoc();
    $stmt->close();
}

// Consultar los productos incluidos en el pedido para mostrar el resumen completo
$detalles_pedido = array();
$total_pedido = 0;

if ($pedido) {
    $sql_det = "SELECT d.cantidad, d.precio_unitario, p.nombre 
                FROM detalle_pedidos d 
                INNER JOIN productos p ON d.id_producto = p.id_producto 
                WHERE d.id_pedido = ?";
    $stmt_det = $conexion->prepare($sql_det);
    $stmt_det->bind_param("i", $id_pedido);
    $stmt_det->execute();
    $res_det = $stmt_det->get_result();

    while ($fila_det = $res_det->fetch_assoc()) {
        $detalles_pedido[] = $fila_det;
        $total_pedido = $total_pedido + ((int)$fila_det["cantidad"] * (float)$fila_det["precio_unitario"]);
    }
    $stmt_det->close();
}

require_once __DIR__ . '/includes/header_publico.php';
?>

<section class="contenedor seccion-confirmacion">
    <?php if ($pedido): ?>
        <div class="tarjeta-confirmacion">
            <div class="confirmacion-encabezado">
                <span class="icono-exito-pedido">✓</span>
                <h1>¡Pedido Registrado con Éxito!</h1>
                <p>Tu encargo fue recibido correctamente. Guardá tu número de pedido para retirarlo en el local.</p>
            </div>

            <!-- HU03-T03: Resumen del pedido en modo solo lectura (edición bloqueada en pantalla) -->
            <div class="confirmacion-datos-grid">
                <div class="dato-caja">
                    <span class="dato-etiqueta">Número de Pedido</span>
                    <strong class="dato-valor">#<?php echo (int)$pedido["id_pedido"]; ?></strong>
                </div>

                <div class="dato-caja">
                    <span class="dato-etiqueta">Cliente</span>
                    <strong class="dato-valor"><?php echo htmlspecialchars($pedido["nombre_cliente"], ENT_QUOTES, 'UTF-8'); ?></strong>
                </div>

                <div class="dato-caja">
                    <span class="dato-etiqueta">Teléfono Registrado</span>
                    <strong class="dato-valor"><?php echo htmlspecialchars($pedido["telefono_cliente"], ENT_QUOTES, 'UTF-8'); ?></strong>
                </div>

                <div class="dato-caja">
                    <span class="dato-etiqueta">Fecha y Hora de Retiro</span>
                    <strong class="dato-valor"><?php echo htmlspecialchars(date("d/m/Y H:i", strtotime($pedido["fecha_retiro"])), ENT_QUOTES, 'UTF-8'); ?> hs</strong>
                </div>

                <div class="dato-caja">
                    <span class="dato-etiqueta">Estado del Pedido</span>
                    <span class="etiqueta-estado estado-pendiente"><?php echo htmlspecialchars($pedido["estado"], ENT_QUOTES, 'UTF-8'); ?></span>
                </div>
            </div>

            <?php if (!empty($detalles_pedido)): ?>
                <div class="confirmacion-detalle-productos">
                    <h3>Detalle del Pedido</h3>
                    <ul class="lista-detalle-confirmacion">
                        <?php foreach ($detalles_pedido as $det): ?>
                            <li>
                                <span><?php echo (int)$det["cantidad"]; ?>x <?php echo htmlspecialchars($det["nombre"], ENT_QUOTES, 'UTF-8'); ?></span>
                                <strong>$<?php echo (int)$det["cantidad"] * (int)$det["precio_unitario"]; ?></strong>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                    <div class="confirmacion-total-fila">
                        <span>Total del Pedido:</span>
                        <strong>$<?php echo (int)$total_pedido; ?></strong>
                    </div>
                </div>
            <?php endif; ?>

            <p class="confirmacion-nota-bloqueo">
                Los datos de un pedido confirmado no pueden editarse directamente desde la web.
            </p>

            <!-- HU03-T01: Botón "Modificar o Cancelar Pedido" que abre el modal de aviso telefónico -->
            <div class="confirmacion-acciones">
                <button type="button" id="btnModificarCancelarPedido" class="btn btn-secundario">
                    Modificar o Cancelar Pedido
                </button>
                <a href="catalogo.php" class="btn btn-primario">Volver al Catálogo</a>
            </div>
        </div>
    <?php else: ?>
        <div class="mensaje-vacio">
            <h3>No se encontró el pedido solicitado</h3>
            <p>Verificá el número de pedido o regresá al catálogo de la tienda.</p>
            <a href="catalogo.php" class="btn btn-primario">Ir al Catálogo</a>
        </div>
    <?php endif; ?>
</section>

<?php
require_once __DIR__ . '/includes/footer.php';
?>
