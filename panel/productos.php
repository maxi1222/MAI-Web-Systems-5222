<?php
// panel/productos.php
require_once '../includes/auth.php';
require_once '../config/conexion.php';

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
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Productos</title>
    <link rel="stylesheet" href="../assets/css/estilos.css">
</head>
<body>
    <?php
    if (file_exists('../includes/header_panel.php') && filesize('../includes/header_panel.php') > 0) {
        include '../includes/header_panel.php';
    } else {
    ?>
    <header class="header-panel">
        <div class="contenedor-header">
            <h1>Panel MAI Panadería</h1>
            <nav class="nav-panel">
                <span>Usuario: <strong><?php echo htmlspecialchars($_SESSION["nombre"], ENT_QUOTES, 'UTF-8'); ?></strong> (<?php echo htmlspecialchars($_SESSION["rol"], ENT_QUOTES, 'UTF-8'); ?>)</span>
                <a href="productos.php">Productos</a>
                <a href="../catalogo.php">Ver Catálogo Público</a>
                <a href="logout.php">Cerrar Sesión</a>
            </nav>
        </div>
    </header>
    <?php } ?>

    <main class="contenedor-panel">
        <h2>Gestión del Catálogo de Productos</h2>
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

        <section class="seccion-formulario">
            <h3>Agregar Nuevo Producto</h3>
            <form action="acciones/producto_guardar.php" method="POST" enctype="multipart/form-data" class="formulario-producto" id="formProducto">
                <input type="hidden" name="control_disponible" value="1">

                <div class="grupo-formulario">
                    <label for="nombre">Nombre del producto:</label>
                    <input type="text" id="nombre" name="nombre" required placeholder="Ej: Medialunas de Manteca">
                </div>

                <div class="grupo-formulario">
                    <label for="descripcion">Descripción:</label>
                    <textarea id="descripcion" name="descripcion" rows="3" placeholder="Descripción opcional del producto"></textarea>
                </div>

                <div class="grupo-formulario">
                    <label for="precio">Precio ($):</label>
                    <input type="number" id="precio" name="precio" min="1" step="1" required placeholder="Ej: 45">
                </div>

                <div class="grupo-formulario">
                    <label for="categoria">Categoría:</label>
                    <select id="categoria" name="categoria" required>
                        <option value="">Seleccione una categoría...</option>
                        <option value="Panificados">Panificados</option>
                        <option value="Bizcochos y Facturas">Bizcochos y Facturas</option>
                        <option value="Tortas y Postres">Tortas y Postres</option>
                        <option value="Confitería">Confitería</option>
                        <option value="Salados">Salados</option>
                    </select>
                </div>

                <div class="grupo-formulario">
                    <label for="imagen">Imagen del producto:</label>
                    <input type="file" id="imagen" name="imagen" accept="image/*">
                </div>

                <fieldset class="grupo-formulario">
                    <legend>Días de elaboración habilitados (Lunes a Domingo):</legend>
                    <div class="checkboxes-dias">
                        <?php foreach ($dias_semana_lista as $dia): ?>
                            <label class="checkbox-dia">
                                <input type="checkbox" name="dias[]" value="<?php echo htmlspecialchars($dia, ENT_QUOTES, 'UTF-8'); ?>" checked>
                                <?php echo htmlspecialchars($dia, ENT_QUOTES, 'UTF-8'); ?>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </fieldset>

                <div class="grupo-formulario">
                    <label class="checkbox-disponible">
                        <input type="checkbox" name="disponible" value="1" checked>
                        Disponible para la venta (desmarcar si está Agotado)
                    </label>
                </div>

                <button type="submit" class="btn btn-primario">Guardar Producto</button>
            </form>
        </section>

        <section class="seccion-listado">
            <h3>Listado de Productos</h3>
            <?php if (empty($productos)): ?>
                <p>No hay productos cargados en el catálogo todavía.</p>
            <?php else: ?>
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
                                <td><?php echo (int)$prod["id_producto"]; ?></td>
                                <td>
                                    <?php if (!empty($prod["imagen_url"])): ?>
                                        <img src="../<?php echo htmlspecialchars($prod["imagen_url"], ENT_QUOTES, 'UTF-8'); ?>" alt="<?php echo htmlspecialchars($prod["nombre"], ENT_QUOTES, 'UTF-8'); ?>" width="60">
                                    <?php else: ?>
                                        <span>Sin imagen</span>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo htmlspecialchars($prod["nombre"], ENT_QUOTES, 'UTF-8'); ?></td>
                                <td><?php echo htmlspecialchars($prod["categoria"], ENT_QUOTES, 'UTF-8'); ?></td>
                                <td>$<?php echo (int)$prod["precio"]; ?></td>
                                <td><?php echo htmlspecialchars($prod["dias_habilitados"], ENT_QUOTES, 'UTF-8'); ?></td>
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
                                    <a href="producto_editar.php?id=<?php echo (int)$prod["id_producto"]; ?>" class="btn btn-secundario">Editar</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </section>
    </main>

    <?php
    if (file_exists('../includes/footer.php') && filesize('../includes/footer.php') > 0) {
        include '../includes/footer.php';
    }
    ?>
    <script src="../assets/js/main.js"></script>
</body>
</html>
