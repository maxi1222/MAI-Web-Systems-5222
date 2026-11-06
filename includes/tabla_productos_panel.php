<?php
// includes/tabla_productos_panel.php
// Parcial reutilizable con el listado y controles de productos del panel interno (H.U-08, H.U-09 y H.U-10)
?>
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
                        <th>Destacado</th>
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
                                <!-- HU10-T01: Control para Destacado (Interactivo para Admin, solo lectura para Empleado) -->
                                <?php if (es_administrador()): ?>
                                    <form action="acciones/producto_estado.php" method="POST" class="form-estado-stock">
                                        <input type="hidden" name="id_producto" value="<?php echo (int)$prod["id_producto"]; ?>">
                                        <input type="hidden" name="accion" value="destacado">
                                        <input type="hidden" name="destacado" value="<?php echo ((int)$prod["destacado"] === 1) ? 0 : 1; ?>">
                                        <label class="switch-stock">
                                            <input
                                                type="checkbox"
                                                onchange="this.form.submit()"
                                                <?php echo ((int)$prod["destacado"] === 1) ? 'checked' : ''; ?>
                                            >
                                            <span class="<?php echo ((int)$prod["destacado"] === 1) ? 'estado-destacado-activo' : 'estado-destacado-inactivo'; ?>">
                                                <?php echo ((int)$prod["destacado"] === 1) ? '★ Destacado' : 'Normal'; ?>
                                            </span>
                                        </label>
                                    </form>
                                <?php else: ?>
                                    <span class="<?php echo ((int)$prod["destacado"] === 1) ? 'estado-destacado-activo' : 'estado-destacado-inactivo'; ?>">
                                        <?php echo ((int)$prod["destacado"] === 1) ? '★ Destacado' : 'Normal'; ?>
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <!-- HU08-T02: Interruptor de disponibilidad interactivo para Empleado y Administrador -->
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
                                <!-- HU09-T03: Botones de editar y dar de baja (Exclusivos para Administrador) -->
                                <?php if (es_administrador()): ?>
                                    <div class="acciones-fila-tabla">
                                        <a href="producto_editar.php?id=<?php echo (int)$prod["id_producto"]; ?>" class="btn btn-secundario btn-pequeno">Editar</a>
                                        <form action="acciones/producto_baja.php" method="POST" class="form-baja-producto" onsubmit="return confirm('¿Seguro que deseas dar de baja este producto?');">
                                            <input type="hidden" name="id_producto" value="<?php echo (int)$prod["id_producto"]; ?>">
                                            <button type="submit" class="btn-quitar-item">Dar de baja</button>
                                        </form>
                                    </div>
                                <?php else: ?>
                                    <span class="sin-permisos-texto">Solo lectura</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</section>
