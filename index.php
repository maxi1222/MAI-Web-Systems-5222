<?php
// index.php
// Página de inicio con portada principal, productos destacados (H.U-10 / HU10-T01) y reacciones de Likes con Firebase

require_once 'config/conexion.php';
require_once 'includes/funciones.php';

$ruta_base = "";
$pagina_actual = "inicio";
$titulo_pagina = "Panadería La Ideal - Artesanal y Tradicional";

$productos_destacados = array();

// Ejecutamos la consulta solicitada en los requerimientos (destacado = 1 AND activo = 1)
$sql_destacados = "SELECT id_producto, nombre, descripcion, precio, categoria, imagen_url, disponible, destacado, dias_habilitados FROM productos WHERE destacado = 1 AND activo = 1 ORDER BY id_producto DESC";

$resultado_destacados = false;
try {
    $resultado_destacados = $conexion->query($sql_destacados);
} catch (Exception $e) {
    $resultado_destacados = false;
}

// Fallback por si la base de datos local aún no aplicó la migración del campo 'activo'
if (!$resultado_destacados) {
    $sql_fallback = "SELECT id_producto, nombre, descripcion, precio, categoria, imagen_url, disponible, destacado, dias_habilitados FROM productos WHERE destacado = 1 ORDER BY id_producto DESC";
    $resultado_destacados = $conexion->query($sql_fallback);
}

if ($resultado_destacados && $resultado_destacados->num_rows > 0) {
    while ($fila = $resultado_destacados->fetch_assoc()) {
        $productos_destacados[] = $fila;
    }
} else {
    // Si la base de datos está recién inicializada y vacía, se provee respaldo con destacados de muestra
    $productos_destacados = array(
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
        )
    );
}

require_once 'includes/header_publico.php';
?>

<!-- HU10-T01: Portada principal de bienvenida (Hero) -->
<section class="hero-inicio">
    <div class="contenedor hero-contenido">
        <span class="hero-etiqueta">Horneado artesanal todos los días</span>
        <h1>Tradición y Sabor Artesanal en Cada Receta</h1>
        <p>Elaboramos panes de masa madre, facturería de manteca y pastelería fresca con ingredientes seleccionados. Reservá tu encargo online con al menos 24 horas de anticipación.</p>
        <div class="hero-botones">
            <a href="catalogo.php" class="btn btn-primario">Ver Catálogo Completo</a>
            <a href="carrito.php" class="btn btn-secundario">Mi Pedido (Carrito)</a>
        </div>
    </div>
</section>

<!-- HU10-T01: Sección principal de Productos Destacados en index.php -->
<section class="seccion-destacados contenedor">
    <div class="destacados-encabezado">
        <span class="subtitulo-seccion">Selección de nuestra cuadra</span>
        <h2 class="titulo-seccion">⭐ Especialidades y Destacados de la Casa</h2>
        <p class="descripcion-seccion">Nuestras recetas más recomendadas, elegidas por nuestros clientes y preparadas frescas cada mañana.</p>
    </div>

    <?php if (empty($productos_destacados)): ?>
        <div class="mensaje-vacio">
            <h3>Por el momento no hay productos marcados como destacados</h3>
            <p>Podés explorar todas nuestras variedades artesanales en el catálogo completo.</p>
            <a href="catalogo.php" class="btn btn-primario">Ir al Catálogo</a>
        </div>
    <?php else: ?>
        <div class="catalogo-grid">
            <?php foreach ($productos_destacados as $prod): ?>
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
                            loading="lazy"
                            onerror="this.src='assets/img/productos/default_panaderia.svg';"
                        >

                        <div class="tarjeta-etiquetas">
                            <span class="etiqueta etiqueta-destacado">★ Destacado</span>
                            <?php if ($esta_agotado): ?>
                                <span class="etiqueta etiqueta-agotado">Agotado</span>
                            <?php endif; ?>
                        </div>

                        <!-- Interfaz de Likes (Firebase): Botón de corazón (♡ / ♥) y contador numérico -->
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

    <div class="destacados-footer-cta">
        <p>¿Buscás más variedades de panadería, facturería, tartas o sándwiches?</p>
        <a href="catalogo.php" class="btn btn-secundario">Explorar todo el catálogo →</a>
    </div>
</section>

<?php
require_once 'includes/footer.php';
?>
