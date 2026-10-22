<?php
// includes/footer.php
// Pie de página global del sistema

$ruta_base = isset($ruta_base) ? $ruta_base : "";
$telefono_mostrar = defined('TELEFONO_LOCAL') ? TELEFONO_LOCAL : "098 318 851";
?>
    </main>

    <!-- Modal de aviso para modificar o cancelar pedido (H.U-03) -->
    <div class="modal-overlay" id="modalCancelacion">
        <div class="modal-card">
            <button type="button" class="modal-close-btn" id="btnCerrarModalCancelacion">&times;</button>
            <h3 class="modal-titulo">Modificar o Cancelar Pedido</h3>
            <p class="modal-mensaje">
                "Para cambiar o cancelar tu pedido, comunicate directamente al teléfono del local"
            </p>
            <div class="modal-datos">
                <p><strong>Teléfono del local:</strong> <?php echo htmlspecialchars($telefono_mostrar, ENT_QUOTES, 'UTF-8'); ?></p>
                <p><strong>Horario de atención:</strong> Lunes a Domingo de 07:00 a 20:30 hs</p>
            </div>
            <div class="modal-acciones">
                <button type="button" class="btn btn-primario" id="btnEntendidoModal">Entendido</button>
            </div>
        </div>
    </div>

    <footer class="footer-sitio">
        <div class="contenedor footer-grid">
            <div class="footer-col">
                <h4 class="footer-titulo-marca">🥖 Panadería La Ideal</h4>
                <p class="footer-desc">
                    Elaboración artesanal diaria con productos frescos. Reservá tu pedido online con al menos 24 horas de anticipación.
                </p>
            </div>

            <div class="footer-col">
                <h4>Navegación</h4>
                <ul class="footer-links">
                    <li><a href="<?php echo $ruta_base; ?>index.php">Inicio</a></li>
                    <li><a href="<?php echo $ruta_base; ?>catalogo.php">Catálogo de Productos</a></li>
                    <li><a href="<?php echo $ruta_base; ?>carrito.php">Mi Pedido (Carrito)</a></li>
                </ul>
            </div>

            <div class="footer-col">
                <h4>Contacto y Retiros</h4>
                <ul class="footer-info">
                    <li><strong>Dirección:</strong> Av. Uruguay 1240, Salto</li>
                    <li><strong>Teléfono:</strong> <?php echo htmlspecialchars($telefono_mostrar, ENT_QUOTES, 'UTF-8'); ?></li>
                    <li><strong>Horario:</strong> 07:00 a 20:30 hs</li>
                </ul>
            </div>
        </div>

        <div class="contenedor footer-bottom">
            <span>&copy; 2026 MAI Web Systems — Proyecto Panadería</span>
            <a href="<?php echo $ruta_base; ?>panel/login.php" class="footer-staff-link">Acceso Personal</a>
        </div>
    </footer>

    <script src="<?php echo $ruta_base; ?>assets/js/main.js"></script>
</body>
</html>
