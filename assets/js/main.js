// assets/js/main.js
// Funciones JavaScript de interfaz para navegación móvil y modal de aviso

document.addEventListener("DOMContentLoaded", function () {
    // 1. Menú desplegable en celulares (Encabezado público)
    var btnMenuMovil = document.getElementById("btnMenuMovil");
    var navLinksPublico = document.getElementById("navLinksPublico");

    if (btnMenuMovil && navLinksPublico) {
        btnMenuMovil.addEventListener("click", function () {
            navLinksPublico.classList.toggle("abierto");
        });
    }

    // 2. Menú desplegable en celulares (Encabezado del panel interno)
    var btnMenuPanel = document.getElementById("btnMenuPanel");
    var navLinksPanel = document.getElementById("navLinksPanel");

    if (btnMenuPanel && navLinksPanel) {
        btnMenuPanel.addEventListener("click", function () {
            navLinksPanel.classList.toggle("abierto");
        });
    }

    // 3. Control del Modal de Cancelación / Modificación (H.U-03)
    var modalCancelacion = document.getElementById("modalCancelacion");
    var btnAbrirModal = document.getElementById("btnModificarCancelarPedido");
    var btnCerrarModal = document.getElementById("btnCerrarModalCancelacion");
    var btnEntendidoModal = document.getElementById("btnEntendidoModal");

    if (btnAbrirModal && modalCancelacion) {
        btnAbrirModal.addEventListener("click", function () {
            modalCancelacion.classList.add("activo");
        });
    }

    if (btnCerrarModal && modalCancelacion) {
        btnCerrarModal.addEventListener("click", function () {
            modalCancelacion.classList.remove("activo");
        });
    }

    if (btnEntendidoModal && modalCancelacion) {
        btnEntendidoModal.addEventListener("click", function () {
            modalCancelacion.classList.remove("activo");
        });
    }
});
