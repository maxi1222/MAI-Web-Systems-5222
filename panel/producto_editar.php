<?php
// panel/producto_editar.php
// Edición de un producto existente en el panel interno (H.U-09)

require_once '../includes/auth.php';
require_once '../config/conexion.php';

$ruta_base = "../";
$pagina_panel = "productos";
$titulo_pagina = "Editar Producto - Panel MAI Panadería";

$mensaje_exito = "";
if (isset($_SESSION["mensaje_exito"])) {
    $mensaje_exito = $_SESSION["mensaje_exito"];
    unset($_SESSION["mensaje_exito"]);
} elseif (isset($_GET["exito"])) {
    $mensaje_exito = "producto guardado exitosamente";
}

$mensaje_error = "";
if (isset($_SESSION["mensaje_error"])) {
    $mensaje_error = $_SESSION["mensaje_error"];
    unset($_SESSION["mensaje_error"]);
}

$id_producto = (isset($_GET["id"]) && is_numeric($_GET["id"])) ? (int)$_GET["id"] : 0;

if ($id_producto <= 0) {
    header("Location: productos.php");
    exit();
}

$sql_prod = "SELECT id_producto, nombre, descripcion, precio, categoria, imagen_url, disponible, destacado, dias_habilitados FROM productos WHERE id_producto = ?";
$stmt_prod = $conexion->prepare($sql_prod);
$stmt_prod->bind_param("i", $id_producto);
$stmt_prod->execute();
$res_prod = $stmt_prod->get_result();
$producto = $res_prod->fetch_assoc();
$stmt_prod->close();

if (!$producto) {
    $_SESSION["mensaje_error"] = "El producto solicitado no existe.";
    header("Location: productos.php");
    exit();
}

$sql_todos = "SELECT id_producto, nombre, descripcion, precio, categoria, imagen_url, disponible, destacado, dias_habilitados FROM productos ORDER BY id_producto DESC";
$stmt_todos = $conexion->prepare($sql_todos);
$stmt_todos->execute();
$res_todos = $stmt_todos->get_result();
$productos = array();
while ($fila = $res_todos->fetch_assoc()) {
    $productos[] = $fila;
}
$stmt_todos->close();

$dias_semana_lista = array("Lunes", "Martes", "Miercoles", "Jueves", "Viernes", "Sabado", "Domingo");
$dias_producto = array_map('trim', explode(",", (string)$producto["dias_habilitados"]));

require_once '../includes/header_panel.php';
?>

<div class="contenedor contenedor-panel">
    <div class="cabecera-seccion-panel">
        <h2>Editar Producto #<?php echo (int)$producto["id_producto"]; ?></h2>
        <p>Modificá los datos del producto seleccionado y guardá los cambios.</p>
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

    <!-- HU09-T01: Formulario de edición de producto -->
    <section class="seccion-formulario">
        <h3>Datos del Producto</h3>

        <!-- Contenedor para alertas de validación de JavaScript (HU09-T02) -->
        <div id="errorValidacionProductoJS" class="alerta alerta-error" style="display: none;"></div>

        <form action="acciones/producto_guardar.php" method="POST" enctype="multipart/form-data" class="formulario-producto" id="formEditarProducto">
            <input type="hidden" name="id_producto" value="<?php echo (int)$producto["id_producto"]; ?>">
            <input type="hidden" name="imagen_actual" value="<?php echo htmlspecialchars((string)$producto["imagen_url"], ENT_QUOTES, 'UTF-8'); ?>">
            <input type="hidden" name="control_disponible" value="1">
            <input type="hidden" name="permanecer_en_editar" value="1">

            <div class="fila-formulario-doble">
                <div class="grupo-formulario">
                    <label for="nombre">Nombre del producto:</label>
                    <input
                        type="text"
                        id="nombre"
                        name="nombre"
                        required
                        value="<?php echo htmlspecialchars($producto["nombre"], ENT_QUOTES, 'UTF-8'); ?>"
                    >
                </div>

                <div class="grupo-formulario">
                    <label for="precio">Precio ($):</label>
                    <input
                        type="number"
                        id="precio"
                        name="precio"
                        min="1"
                        step="1"
                        required
                        value="<?php echo (int)$producto["precio"]; ?>"
                    >
                </div>
            </div>

            <div class="fila-formulario-doble">
                <div class="grupo-formulario">
                    <label for="categoria">Categoría:</label>
                    <?php
                    $categorias_base = array(
                        "Panadería",
                        "Facturería",
                        "Tortas y Postres",
                        "Pastelería",
                        "Salados y Sandwichería"
                    );
                    if (!in_array($producto["categoria"], $categorias_base) && !empty($producto["categoria"])) {
                        $categorias_base[] = $producto["categoria"];
                    }
                    ?>
                    <select id="categoria" name="categoria" required>
                        <?php foreach ($categorias_base as $cat): ?>
                            <option value="<?php echo htmlspecialchars($cat, ENT_QUOTES, 'UTF-8'); ?>" <?php echo ($producto["categoria"] === $cat) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($cat, ENT_QUOTES, 'UTF-8'); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="grupo-formulario">
                    <label for="imagen">Imagen del producto (dejar vacío para mantener la actual):</label>
                    <?php if (!empty($producto["imagen_url"])): ?>
                        <div class="imagen-actual-preview">
                            <img src="../<?php echo htmlspecialchars($producto["imagen_url"], ENT_QUOTES, 'UTF-8'); ?>" alt="Imagen actual" width="80">
                        </div>
                    <?php endif; ?>
                    <input type="file" id="imagen" name="imagen" accept="image/*">
                </div>
            </div>

            <div class="grupo-formulario">
                <label for="descripcion">Descripción:</label>
                <textarea id="descripcion" name="descripcion" rows="3"><?php echo htmlspecialchars((string)$producto["descripcion"], ENT_QUOTES, 'UTF-8'); ?></textarea>
            </div>

            <fieldset class="grupo-formulario fieldset-dias">
                <legend>Días de elaboración habilitados (Lunes a Domingo):</legend>
                <div class="checkboxes-dias">
                    <?php foreach ($dias_semana_lista as $dia): ?>
                        <label class="checkbox-dia">
                            <input
                                type="checkbox"
                                name="dias[]"
                                value="<?php echo htmlspecialchars($dia, ENT_QUOTES, 'UTF-8'); ?>"
                                <?php echo in_array($dia, $dias_producto) ? 'checked' : ''; ?>
                            >
                            <span><?php echo htmlspecialchars($dia, ENT_QUOTES, 'UTF-8'); ?></span>
                        </label>
                    <?php endforeach; ?>
                </div>
            </fieldset>

            <div class="grupo-formulario">
                <label class="checkbox-disponible">
                    <input
                        type="checkbox"
                        name="disponible"
                        value="1"
                        <?php echo ((int)$producto["disponible"] === 1) ? 'checked' : ''; ?>
                    >
                    <span>Disponible para la venta (desmarcar si está Agotado)</span>
                </label>
            </div>

            <div class="acciones-formulario">
                <button type="submit" class="btn btn-primario">Guardar Cambios</button>
                <a href="productos.php" class="btn btn-secundario">Volver al listado</a>
            </div>
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
