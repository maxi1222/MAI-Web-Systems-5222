<?php
// catalogo.php
// Catálogo de productos con barra de filtros y diseño responsivo en tarjetas (H.U-01 y H.U-08)

require_once 'config/conexion.php';
require_once 'includes/funciones.php';

$ruta_base = "";
$pagina_actual = "catalogo";
$titulo_pagina = "Catálogo de Productos - Panadería La Ideal";

// Leer valores de los filtros enviados por GET usando isset() y trim()
$buscar = isset($_GET["buscar"]) ? trim($_GET["buscar"]) : "";
$categoria = isset($_GET["categoria"]) ? trim($_GET["categoria"]) : "";
$fecha_retiro = isset($_GET["fecha_retiro"]) ? trim($_GET["fecha_retiro"]) : "";

// Lista de categorías del catálogo
$categorias_disponibles = array(
    "Panadería",
    "Facturería",
    "Tortas y Postres",
    "Pastelería",
    "Salados y Sandwichería"
);

// Consultar productos de la base de datos
$todos_los_productos = array();
$sql = "SELECT id_producto, nombre, descripcion, precio, categoria, imagen_url, disponible, destacado, dias_habilitados FROM productos ORDER BY disponible DESC, nombre ASC";
$resultado = $conexion->query($sql);

if ($resultado && $resultado->num_rows > 0) {
    while ($fila = $resultado->fetch_assoc()) {
        $todos_los_productos[] = $fila;
    }
} else {
    // Productos de muestra para visualizar las tarjetas mientras la tabla productos esté vacía
    $todos_los_productos = array(
        array(
            "id_producto" => 1,
            "nombre" => "Croissant de Manteca Artesanal",
            "descripcion" => "Hojaldre francés laminado a mano con manteca extra, horneado cada mañana.",
            "precio" => 95,
            "categoria" => "Facturería",
            "imagen_url" => "assets/img/productos/default_panaderia.svg",
            "disponible" => 1,
            "destacado" => 1,
            "dias_habilitados" => "Lunes,Martes,Miercoles,Jueves,Viernes,Sabado,Domingo"
        ),
        array(
            "id_producto" => 2,
            "nombre" => "Pan de Campo de Masa Madre",
            "descripcion" => "Hogaza rústica de fermentación lenta, corteza crocante y miga suave.",
            "precio" => 210,
            "categoria" => "Panadería",
            "imagen_url" => "assets/img/productos/default_panaderia.svg",
            "disponible" => 1,
            "destacado" => 1,
            "dias_habilitados" => "Lunes,Martes,Miercoles,Jueves,Viernes,Sabado"
        ),
        array(
            "id_producto" => 3,
            "nombre" => "Tarta de Frutos Rojos y Crema",
            "descripcion" => "Base de masa sablée rellena con crema pastelera y frutas frescas.",
            "precio" => 680,
            "categoria" => "Tortas y Postres",
            "imagen_url" => "assets/img/productos/default_panaderia.svg",
            "disponible" => 1,
            "destacado" => 1,
            "dias_habilitados" => "Jueves,Viernes,Sabado,Domingo"
        ),
        array(
            "id_producto" => 4,
            "nombre" => "Alfajores de Maicena Artesanales (6 u.)",
            "descripcion" => "Media docena de alfajores suaves rellenos con dulce de leche y coco.",
            "precio" => 240,
            "categoria" => "Pastelería",
            "imagen_url" => "assets/img/productos/default_panaderia.svg",
            "disponible" => 0,
            "destacado" => 0,
            "dias_habilitados" => "Lunes,Martes,Miercoles,Jueves,Viernes,Sabado"
        ),
        array(
            "id_producto" => 5,
            "nombre" => "Docena de Bizcochos Surtidos",
            "descripcion" => "Selección tradicional recién salida del horno: margaritas, vigilantes y quesitos.",
            "precio" => 320,
            "categoria" => "Facturería",
            "imagen_url" => "assets/img/productos/default_panaderia.svg",
            "disponible" => 1,
            "destacado" => 0,
            "dias_habilitados" => "Lunes,Martes,Miercoles,Jueves,Viernes,Sabado,Domingo"
        ),
        array(
            "id_producto" => 6,
            "nombre" => "Bandeja de Sándwiches de Miga (12 u.)",
            "descripcion" => "Docena de sándwiches triples en pan de miga extra tierno de jamón y queso.",
            "precio" => 590,
            "categoria" => "Salados y Sandwichería",
            "imagen_url" => "assets/img/productos/default_panaderia.svg",
            "disponible" => 1,
            "destacado" => 0,
            "dias_habilitados" => "Martes,Miercoles,Jueves,Viernes,Sabado,Domingo"
        )
    );
}

// Si el cliente seleccionó una fecha de retiro, obtener el día de la semana (quitando tildes para comparar con dias_habilitados)
$dia_buscado = "";
if (!empty($fecha_retiro)) {
    $dia_con_tilde = obtener_dia_semana($fecha_retiro);
    $dia_buscado = str_replace(array("é", "á"), array("e", "a"), $dia_con_tilde);
}

// Aplicar los filtros seleccionados (texto, categoría y fecha de retiro)
$productos = array();
foreach ($todos_los_productos as $item) {
    // 1. Filtrar por buscador de texto (nombre o descripción)
    if (!empty($buscar)) {
        $coincide_nombre = (stripos($item["nombre"], $buscar) !== false);
        $coincide_desc = (stripos($item["descripcion"], $buscar) !== false);
        if (!$coincide_nombre && !$coincide_desc) {
            continue;
        }
    }

    // 2. Filtrar por categoría exacta
    if (!empty($categoria)) {
        if ($item["categoria"] !== $categoria) {
            continue;
        }
    }

    // 3. Filtrar por día de elaboración según la fecha de retiro elegida
    if (!empty($dia_buscado)) {
        $dias_item = str_replace(array("é", "á"), array("e", "a"), $item["dias_habilitados"]);
        if (stripos($dias_item, $dia_buscado) === false) {
            continue;
        }
    }

    $productos[] = $item;
}

// Fecha mínima para el selector (mañana / 24 horas de anticipación)
$fecha_minima = date("Y-m-d", strtotime("+1 day"));

require_once 'includes/header_publico.php';
?>

<!-- Encabezado de la página de catálogo -->
<section class="banner-catalogo">
    <div class="contenedor">
        <span class="subtitulo-seccion">Elaboración artesanal diaria</span>
        <h1 class="titulo-seccion">Catálogo de Productos</h1>
        <p class="descripcion-seccion">
            Buscá tu producto favorito, filtrá por categoría o elegí tu fecha de retiro para ver qué horneamos ese día.
        </p>
    </div>
</section>

<section class="contenedor seccion-catalogo">

    <!-- HU01-T03: Barra de filtros (buscador de texto, selector de categoría y selector de fecha de retiro) -->
    <form action="catalogo.php" method="GET" class="barra-filtros">
        <div class="filtro-item">
            <label for="buscar">Buscar producto:</label>
            <input
                type="text"
                id="buscar"
                name="buscar"
                placeholder="Ej: Pan, croissant, tarta..."
                value="<?php echo htmlspecialchars($buscar, ENT_QUOTES, 'UTF-8'); ?>"
            >
        </div>

        <div class="filtro-item">
            <label for="categoria">Categoría:</label>
            <select id="categoria" name="categoria">
                <option value="">Todas las categorías</option>
                <?php foreach ($categorias_disponibles as $cat): ?>
                    <option value="<?php echo htmlspecialchars($cat, ENT_QUOTES, 'UTF-8'); ?>" <?php echo ($categoria === $cat) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($cat, ENT_QUOTES, 'UTF-8'); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="filtro-item">
            <label for="fecha_retiro">Fecha de retiro:</label>
            <input
                type="date"
                id="fecha_retiro"
                name="fecha_retiro"
                min="<?php echo $fecha_minima; ?>"
                value="<?php echo htmlspecialchars($fecha_retiro, ENT_QUOTES, 'UTF-8'); ?>"
            >
        </div>

        <div class="filtro-botones">
            <button type="submit" class="btn btn-primario">Filtrar</button>
            <?php if (!empty($buscar) || !empty($categoria) || !empty($fecha_retiro)): ?>
                <a href="catalogo.php" class="btn btn-secundario">Limpiar</a>
            <?php endif; ?>
        </div>
    </form>

    <!-- HU01-T01: Contenedor adaptable en tarjetas (cards) usando CSS Grid sin tablas rígidas -->
    <?php if (empty($productos)): ?>
        <div class="mensaje-vacio">
            <h3>No se encontraron productos</h3>
            <p>Probá cambiando los filtros de búsqueda o seleccionando otra categoría.</p>
            <a href="catalogo.php" class="btn btn-secundario">Ver todo el catálogo</a>
        </div>
    <?php else: ?>
        <div class="catalogo-grid">
            <?php foreach ($productos as $prod): ?>
                <?php
                    $esta_agotado = ((int)$prod["disponible"] === 0);
                    $ruta_imagen = !empty($prod["imagen_url"]) ? $prod["imagen_url"] : "assets/img/productos/default_panaderia.svg";
                ?>
                <article class="tarjeta-producto <?php echo $esta_agotado ? 'producto-agotado' : ''; ?>">
                    <div class="tarjeta-imagen-caja">
                        <img
                            src="<?php echo htmlspecialchars($ruta_imagen, ENT_QUOTES, 'UTF-8'); ?>"
                            alt="<?php echo htmlspecialchars($prod["nombre"], ENT_QUOTES, 'UTF-8'); ?>"
                            class="tarjeta-imagen"
                            onerror="this.src='assets/img/productos/default_panaderia.svg';"
                        >

                        <!-- Etiquetas superiores (Destacado / Agotado) -->
                        <div class="tarjeta-etiquetas">
                            <?php if ((int)$prod["destacado"] === 1): ?>
                                <span class="etiqueta etiqueta-destacado">★ Destacado</span>
                            <?php endif; ?>

                            <?php if ($esta_agotado): ?>
                                <!-- HU08-T03: Etiqueta visual roja "Agotado" -->
                                <span class="etiqueta etiqueta-agotado">Agotado</span>
                            <?php endif; ?>
                        </div>

                        <!-- Botón de corazón para Likes (Firebase) -->
                        <button type="button" class="btn-like" data-id="<?php echo (int)$prod["id_producto"]; ?>" aria-label="Me gusta">
                            <span class="corazon-icono">♡</span>
                            <span class="like-contador">0</span>
                        </button>
                    </div>

                    <div class="tarjeta-cuerpo">
                        <div class="tarjeta-fila-superior">
                            <span class="tarjeta-categoria"><?php echo htmlspecialchars($prod["categoria"], ENT_QUOTES, 'UTF-8'); ?></span>
                            <span class="tarjeta-precio">$<?php echo (int)$prod["precio"]; ?></span>
                        </div>

                        <h3 class="tarjeta-titulo"><?php echo htmlspecialchars($prod["nombre"], ENT_QUOTES, 'UTF-8'); ?></h3>
                        <p class="tarjeta-descripcion"><?php echo htmlspecialchars($prod["descripcion"], ENT_QUOTES, 'UTF-8'); ?></p>

                        <p class="tarjeta-dias">
                            <strong>Días:</strong> <?php echo htmlspecialchars(str_replace(",", ", ", $prod["dias_habilitados"]), ENT_QUOTES, 'UTF-8'); ?>
                        </p>

                        <div class="tarjeta-acciones">
                            <?php if ($esta_agotado): ?>
                                <!-- HU08-T03: Botón de encargo bloqueado (disabled) cuando está agotado -->
                                <button type="button" class="btn btn-agotado" disabled>Agotado</button>
                            <?php else: ?>
                                <form action="acciones/carrito_agregar.php" method="POST" class="form-agregar-carrito">
                                    <input type="hidden" name="id_producto" value="<?php echo (int)$prod["id_producto"]; ?>">
                                    <input type="hidden" name="cantidad" value="1">
                                    <button type="submit" class="btn btn-primario btn-bloque">Encargar</button>
                                </form>
                            <?php endif; ?>
                        </div>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

</section>

<?php
require_once 'includes/footer.php';
?>
