<?php
// panel/productos.php
// Gestión de productos en el panel interno (H.U-09)

require_once '../includes/auth.php';
require_once '../config/conexion.php';

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

$sql = "SELECT id_producto, nombre, descripcion, precio, categoria, imagen_url, disponible, destacado, dias_habilitados FROM productos ORDER BY id_producto DESC";
$stmt = $conexion->prepare($sql);
$stmt->execute();
$resultado = $stmt->get_result();

$productos = array();
while ($fila = $resultado->fetch_assoc()) {
    $productos[] = $fila;
}
$stmt->close();

$dias_semana_lista = array("Lunes", "Martes", "Miercoles", "Jueves", "Viernes", "Sabado", "Domingo");

require_once '../includes/header_panel.php';
?>

<div class="contenedor contenedor-panel">
    <div class="cabecera-seccion-panel">
        <h2>Gestión del Catálogo de Productos</h2>
        <p>Agregá nuevos productos artesanales, asigná su categoría, precio, foto y días de elaboración.</p>
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

    <!-- HU09-T01: Formulario de gestión de productos -->
    <section class="seccion-formulario">
        <h3>Agregar Nuevo Producto</h3>

        <!-- Contenedor para alertas de validación de JavaScript (HU09-T02) -->
        <div id="errorValidacionProductoJS" class="alerta alerta-error" style="display: none;"></div>

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
                        <option value="Panadería">Panadería</option>
                        <option value="Facturería">Facturería</option>
                        <option value="Tortas y Postres">Tortas y Postres</option>
                        <option value="Pastelería">Pastelería</option>
                        <option value="Salados y Sandwichería">Salados y Sandwichería</option>
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

            <div class="grupo-formulario">
                <label class="checkbox-disponible">
                    <input type="checkbox" name="disponible" value="1" checked>
                    <span>Disponible para la venta (desmarcar si está Agotado)</span>
                </label>
            </div>

            <button type="submit" class="btn btn-primario">Guardar Producto</button>
        </form>
    </section>

    <section class="seccion-listado">
        <h3>Listado de Productos</h3>
        <?php if (empty($productos)): ?>
            <p class="texto-vacio-panel">No hay productos cargados en el catálogo todavía.</p>
        <?php else: ?>
            <div class="tabla-responsive">
                <table class="tabla-productos">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Imagen</th>
                            <th>Nombre</th>
                            <th>Categoría</th>
                            <th>Precio</th>
                            <th>Días Habilitados</th>
                            <th>Estado (Stock)</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($productos as $prod): ?>
                            <tr>
                                <td>#<?php echo (int)$prod["id_producto"]; ?></td>
                                <td>
                                    <?php if (!empty($prod["imagen_url"])): ?>
                                        <img src="../<?php echo htmlspecialchars($prod["imagen_url"], ENT_QUOTES, 'UTF-8'); ?>" alt="<?php echo htmlspecialchars($prod["nombre"], ENT_QUOTES, 'UTF-8'); ?>" class="miniatura-tabla" width="60">
                                    <?php else: ?>
                                        <span class="sin-imagen-texto">Sin imagen</span>
                                    <?php endif; ?>
                                </td>
                                <td><strong><?php echo htmlspecialchars($prod["nombre"], ENT_QUOTES, 'UTF-8'); ?></strong></td>
                                <td><span class="tarjeta-categoria"><?php echo htmlspecialchars($prod["categoria"], ENT_QUOTES, 'UTF-8'); ?></span></td>
                                <td><strong>$<?php echo (int)$prod["precio"]; ?></strong></td>
                                <td class="celda-dias"><?php echo htmlspecialchars(str_replace(",", ", ", $prod["dias_habilitados"]), ENT_QUOTES, 'UTF-8'); ?></td>
                                <td>
                                    <form action="acciones/producto_estado.php" method="POST" class="form-estado-stock">
                                        <input type="hidden" name="id_producto" value="<?php echo (int)$prod["id_producto"]; ?>">
                                        <input type="hidden" name="disponible" value="<?php echo ((int)$prod["disponible"] === 1) ? 0 : 1; ?>">
                                        <label class="switch-stock">
                                            <input
                                                type="checkbox"
                                                onchange="this.form.submit()"
                                                <?php echo ((int)$prod["disponible"] === 1) ? 'checked' : ''; ?>
                                            >
                                            <span class="<?php echo ((int)$prod["disponible"] === 1) ? 'estado-disponible' : 'etiqueta-agotado'; ?>">
                                                <?php echo ((int)$prod["disponible"] === 1) ? 'Disponible' : 'Agotado'; ?>
                                            </span>
                                        </label>
                                    </form>
                                </td>
                                <td>
                                    <a href="producto_editar.php?id=<?php echo (int)$prod["id_producto"]; ?>" class="btn btn-secundario btn-pequeno">Editar</a>
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
