<?php
// index.php

require_once 'config/conexion.php';
require_once 'includes/funciones.php';

$ruta_base = "";
$pagina_actual = "inicio";
$titulo_pagina = "Panadería La Ideal - Artesanal y Tradicional";

$productos_destacados = array();

// Ejecutamos la consulta solicitada en los requerimientos (destacado = 1 AND activo = 1)
$sql_destacados = "SELECT id_producto, nombre, descripcion, precio, categoria, imagen_url, disponible, destacado, dias_habilitados FROM productos WHERE destacado = 1 AND activo = 1 ORDER BY id_producto DESC";

$resultado_destacados = $conexion->query($sql_destacados);

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

<!-- ========================================================================================= -->
<!-- Esto te toca a vos iñaki: Maquetación de la portada principal (Hero / Bienvenida) (HU10-T01) -->
<!-- ========================================================================================= -->
<section class="hero-inicio">
    <div class="contenedor hero-contenido">
        <h1>Tradición y Sabor Artesanal</h1>
        <p>Elaboramos panes, facturas y delicias con ingredientes de primera calidad. Hacé tu pedido online con 24 horas de anticipación.</p>
        <div class="hero-botones">
            <a href="catalogo.php" class="btn btn-primario">Ver Catálogo Completo</a>
            <a href="carrito.php" class="btn btn-secundario">Mi Pedido</a>
        </div>
    </div>
</section>

<!-- ========================================================================================= -->
<!-- Esto te toca a vos iñaki: Maquetar la sección principal de destacados en index.php (HU10-T01) -->
<!-- ========================================================================================= -->
<section class="seccion-destacados contenedor">
    <div class="destacados-encabezado">
        <h2>⭐ Especialidades y Destacados de la Casa</h2>
        <p>Nuestras recetas más solicitadas, preparadas frescas todos los días.</p>
    </div>

    <?php if (empty($productos_destacados)): ?>
        <!-- Esto te toca a vos iñaki: Mensaje cuando no haya artículos destacados disponibles -->
        <div class="alerta alerta-info">
            <p>Por el momento no hay productos marcados como destacados. Podés explorar todas nuestras opciones en el <a href="catalogo.php">catálogo completo</a>.</p>
        </div>
    <?php else: ?>
        <!-- Grid responsivo donde se inyectan los productos destacados obtenidos por el backend -->
        <div class="catalogo-grid">
            <?php foreach ($productos_destacados as $prod): ?>
                <!-- ========================================================================================= -->
                <!-- Esto te toca a vos iñaki: Maquetar la tarjeta de producto destacado y los estilos de tarjeta -->
                <!-- ========================================================================================= -->
                <article class="tarjeta-producto <?php echo ((int)$prod["disponible"] === 0) ? 'tarjeta-agotado' : ''; ?>">
                    <div class="tarjeta-imagen-contenedor">
                        <img 
                            src="<?php echo !empty($prod["imagen_url"]) ? htmlspecialchars($prod["imagen_url"], ENT_QUOTES, 'UTF-8') : 'assets/img/productos/default_panaderia.svg'; ?>" 
                            alt="<?php echo htmlspecialchars($prod["nombre"], ENT_QUOTES, 'UTF-8'); ?>"
                            class="tarjeta-imagen"
                            loading="lazy"
                        >
                        <div class="tarjeta-insignias">
                            <span class="insignia-destacado">⭐ Destacado</span>
                            <?php if ((int)$prod["disponible"] === 0): ?>
                                <span class="etiqueta etiqueta-agotado">Agotado</span>
                            <?php endif; ?>
                        </div>

                        <!-- ========================================================================================= -->
                        <!-- Esto te toca a vos iñaki: Interfaz de Likes (Firebase) - Botón de corazón (♡ / ♥)          -->
                        <!-- y su contador numérico en las tarjetas de index.php junto con los estilos CSS de activo   -->
                        <!-- Conectado con assets/js/firebase_likes.js mediante data-id (id_producto)                   -->
                        <!-- ========================================================================================= -->
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
                        
                        <div class="tarjeta-acciones">
                            <?php if ((int)$prod["disponible"] === 1): ?>
                                <form action="acciones/carrito_agregar.php" method="POST" class="form-agregar-rapido">
                                    <input type="hidden" name="id_producto" value="<?php echo (int)$prod["id_producto"]; ?>">
                                    <input type="hidden" name="cantidad" value="1">
                                    <button type="submit" class="btn btn-primario btn-bloque">🛒 Agregar al Carrito</button>
                                </form>
                            <?php else: ?>
                                <button type="button" class="btn btn-deshabilitado btn-bloque" disabled>Agotado</button>
                            <?php endif; ?>
                        </div>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <div class="destacados-footer-cta">
        <p>¿Buscás más variedades de pastelería, tartas o sándwiches?</p>
        <a href="catalogo.php" class="btn btn-secundario">Explorar todo el catálogo</a>
    </div>
</section>

<?php
require_once 'includes/footer.php';
?>

