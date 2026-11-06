<?php
// panel/productos.php
// Gestión de productos en el panel interno (H.U-08, H.U-09, H.U-10)

require_once '../includes/auth.php';
require_once '../config/conexion.php';
require_once '../includes/funciones.php';

$ruta_base = "../";
$pagina_panel = "productos";
$titulo_pagina = "Gestión de Productos - Panel MAI Panadería";

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

// Filtrar productos activos para no listar los dados de baja lógica (HU09-T03)
$sql = "SELECT id_producto, nombre, descripcion, precio, categoria, imagen_url, disponible, destacado, dias_habilitados FROM productos WHERE (activo = 1 OR activo IS NULL) ORDER BY id_producto DESC";
$stmt = $conexion->prepare($sql);
$stmt->execute();
$resultado = $stmt->get_result();

$productos = array();
while ($fila = $resultado->fetch_assoc()) {
    $productos[] = $fila;
}
$stmt->close();

$categorias_lista = obtener_categorias_catalogo();
$dias_semana_lista = obtener_dias_semana_catalogo();

require_once '../includes/header_panel.php';
?>

<div class="contenedor contenedor-panel">
    <div class="cabecera-seccion-panel">
        <h2>Gestión del Catálogo de Productos</h2>
        <p>
            <?php if (es_administrador()): ?>
                Agregá nuevos productos artesanales, asigná su categoría, precio, foto, días de elaboración y estado.
            <?php else: ?>
                Control de disponibilidad y stock de productos para el personal de cocina y mostrador.
            <?php endif; ?>
        </p>
    </div>

    <?php if (!empty($mensaje_exito)): ?>
        <div class="alerta alerta-exito" role="status">
            <?php echo htmlspecialchars($mensaje_exito, ENT_QUOTES, 'UTF-8'); ?>
        </div>
    <?php endif; ?>

    <?php if (!empty($mensaje_error)): ?>
        <div class="alerta alerta-error" role="alert">
            <?php echo htmlspecialchars($mensaje_error, ENT_QUOTES, 'UTF-8'); ?>
        </div>
    <?php endif; ?>

    <!-- HU09-T01: Formulario de gestión de productos (Solo visible para rol Administrador - RNF-02) -->
    <?php if (es_administrador()): ?>
    <section class="seccion-formulario">
        <h3>Agregar Nuevo Producto</h3>

        <!-- Contenedor para alertas de validación de JavaScript (HU09-T02) -->
        <div id="errorValidacionProductoJS" class="alerta alerta-error oculto"></div>

        <form action="acciones/producto_guardar.php" method="POST" enctype="multipart/form-data" class="formulario-producto" id="formProducto">
            <input type="hidden" name="control_disponible" value="1">

            <div class="fila-formulario-doble">
                <div class="grupo-formulario">
                    <label for="nombre">Nombre del producto:</label>
                    <input type="text" id="nombre" name="nombre" required placeholder="Ej: Medialunas de Manteca">
                </div>

                <div class="grupo-formulario">
                    <label for="precio">Precio ($):</label>
                    <input type="number" id="precio" name="precio" min="1" step="1" required placeholder="Ej: 95">
                </div>
            </div>

            <div class="fila-formulario-doble">
                <div class="grupo-formulario">
                    <label for="categoria">Categoría:</label>
                    <select id="categoria" name="categoria" required>
                        <option value="">Seleccione una categoría...</option>
                        <?php foreach ($categorias_lista as $cat): ?>
                            <option value="<?php echo htmlspecialchars($cat, ENT_QUOTES, 'UTF-8'); ?>">
                                <?php echo htmlspecialchars($cat, ENT_QUOTES, 'UTF-8'); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="grupo-formulario">
                    <label for="imagen">Imagen del producto:</label>
                    <input type="file" id="imagen" name="imagen" accept="image/*">
                </div>
            </div>

            <div class="grupo-formulario">
                <label for="descripcion">Descripción:</label>
                <textarea id="descripcion" name="descripcion" rows="3" placeholder="Descripción opcional del producto"></textarea>
            </div>

            <fieldset class="grupo-formulario fieldset-dias">
                <legend>Días de elaboración habilitados (Lunes a Domingo):</legend>
                <div class="checkboxes-dias">
                    <?php foreach ($dias_semana_lista as $dia): ?>
                        <label class="checkbox-dia">
                            <input type="checkbox" name="dias[]" value="<?php echo htmlspecialchars($dia, ENT_QUOTES, 'UTF-8'); ?>" checked>
                            <span><?php echo htmlspecialchars($dia, ENT_QUOTES, 'UTF-8'); ?></span>
                        </label>
                    <?php endforeach; ?>
                </div>
            </fieldset>

            <div class="fila-checkboxes-estado">
                <div class="grupo-formulario">
                    <label class="checkbox-disponible">
                        <input type="checkbox" name="disponible" value="1" checked>
                        <span>Disponible para la venta (desmarcar si está Agotado)</span>
                    </label>
                </div>

                <!-- HU10-T01: Checkbox opcional "Destacado" exclusivo para Administrador -->
                <div class="grupo-formulario">
                    <label class="checkbox-disponible checkbox-destacado">
                        <input type="checkbox" name="destacado" value="1">
                        <span>★ Destacado (mostrar en la portada de Inicio)</span>
                    </label>
                </div>
            </div>

            <button type="submit" class="btn btn-primario">Guardar Producto</button>
        </form>
    </section>
    <?php endif; ?>

    <?php require_once '../includes/tabla_productos_panel.php'; ?>
</div>

<?php
require_once '../includes/footer.php';
?>
