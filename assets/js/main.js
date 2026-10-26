// assets/js/main.js
// Funciones JavaScript de interfaz para navegación móvil, validación de 24h en carrito,
// modal de aviso (H.U-03) y validación de formulario de productos (HU09-T02)

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

    // 3. HU02-T02: Bloquear fechas pasadas y menores a 24 horas en el formulario de carrito.php
    var formPedidoCarrito = document.getElementById("formPedidoCarrito");
    var inputFechaDia = document.getElementById("fecha_retiro_dia");
    var inputFechaHora = document.getElementById("fecha_retiro_hora");
    var inputNombreCliente = document.getElementById("nombre_cliente");
    var inputTelefonoCliente = document.getElementById("telefono_cliente");
    var cajaErrorPedidoJS = document.getElementById("errorValidacionPedidoJS");

    if (inputFechaDia) {
        // Calcular fecha mínima sumando 24 horas (86400000 milisegundos) a la fecha/hora actual
        var fechaMinima24h = new Date(Date.now() + 24 * 60 * 60 * 1000);
        var anio = fechaMinima24h.getFullYear();
        var mes = String(fechaMinima24h.getMonth() + 1).padStart(2, "0");
        var dia = String(fechaMinima24h.getDate()).padStart(2, "0");
        var fechaMinimaTexto = anio + "-" + mes + "-" + dia;

        // Bloquear en el calendario de HTML las fechas pasadas y el día actual
        inputFechaDia.setAttribute("min", fechaMinimaTexto);
    }

    if (formPedidoCarrito) {
        formPedidoCarrito.addEventListener("submit", function (evento) {
            var nombre = inputNombreCliente ? inputNombreCliente.value.trim() : "";
            var telefono = inputTelefonoCliente ? inputTelefonoCliente.value.trim() : "";
            var diaElegido = inputFechaDia ? inputFechaDia.value : "";
            var horaElegida = inputFechaHora ? inputFechaHora.value : "";

            // Validar que nombre, teléfono, fecha y hora no estén vacíos
            if (nombre.length < 3) {
                evento.preventDefault();
                mostrarErrorPedido("Por favor, ingresá tu nombre completo (mínimo 3 caracteres).");
                return;
            }

            if (telefono.length < 6) {
                evento.preventDefault();
                mostrarErrorPedido("Por favor, ingresá un número de teléfono válido.");
                return;
            }

            if (diaElegido === "" || horaElegida === "") {
                evento.preventDefault();
                mostrarErrorPedido("Debés seleccionar obligatoriamente la fecha y la hora de retiro.");
                return;
            }

            // Construir el objeto Date con la fecha y hora elegidas por el cliente
            var fechaHoraRetiro = new Date(diaElegido + "T" + horaElegida + ":00");
            var limiteMinimo24h = new Date(Date.now() + 24 * 60 * 60 * 1000);

            // Comprobar si la fecha y hora de retiro es menor a 24 horas desde ahora
            if (isNaN(fechaHoraRetiro.getTime()) || fechaHoraRetiro < limiteMinimo24h) {
                evento.preventDefault();
                mostrarErrorPedido("Los pedidos deben realizarse con al menos 1 día (24 horas) de anticipación.");
                return;
            }
        });
    }

    function mostrarErrorPedido(mensaje) {
        if (cajaErrorPedidoJS) {
            cajaErrorPedidoJS.textContent = mensaje;
            cajaErrorPedidoJS.style.display = "block";
        } else {
            alert(mensaje);
        }
    }

    // 4. HU03-T02 y HU03-T03: Control del Modal de Cancelación / Modificación en confirmacion.php
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

    if (modalCancelacion) {
        modalCancelacion.addEventListener("click", function (evento) {
            if (evento.target === modalCancelacion) {
                modalCancelacion.classList.remove("activo");
            }
        });
    }

    // 5. HU09-T02: Validación JS en navegador para el formulario de productos (panel/productos.php y panel/producto_editar.php)
    // Validar que el nombre sea texto y el precio sea numérico antes de enviarlo
    var formProducto = document.getElementById("formProducto");
    var formEditarProducto = document.getElementById("formEditarProducto");
    var formularioGestionProd = formProducto ? formProducto : formEditarProducto;
    var cajaErrorProductoJS = document.getElementById("errorValidacionProductoJS");

    if (formularioGestionProd) {
        formularioGestionProd.addEventListener("submit", function (evento) {
            var inputNombreProd = document.getElementById("nombre");
            var inputPrecioProd = document.getElementById("precio");
            var inputCategoriaProd = document.getElementById("categoria");
            var checkboxesDias = formularioGestionProd.querySelectorAll('input[name="dias[]"]:checked');

            var nombreTexto = inputNombreProd ? inputNombreProd.value.trim() : "";
            var precioValor = inputPrecioProd ? inputPrecioProd.value.trim() : "";
            var categoriaValor = inputCategoriaProd ? inputCategoriaProd.value.trim() : "";

            // Validar que el nombre no esté vacío y que sea texto (no solo números)
            if (nombreTexto === "" || !isNaN(nombreTexto)) {
                evento.preventDefault();
                mostrarErrorProducto("El nombre del producto es obligatorio y debe ser texto válido.");
                return;
            }

            // Validar que el precio sea numérico y mayor a 0
            if (precioValor === "" || isNaN(precioValor) || Number(precioValor) <= 0) {
                evento.preventDefault();
                mostrarErrorProducto("El precio del producto debe ser un valor numérico mayor a 0.");
                return;
            }

            // Validar que haya seleccionado categoría
            if (categoriaValor === "") {
                evento.preventDefault();
                mostrarErrorProducto("Por favor, seleccione una categoría para el producto.");
                return;
            }

            // Validar que al menos un día de la semana esté marcado
            if (checkboxesDias.length === 0) {
                evento.preventDefault();
                mostrarErrorProducto("Debe seleccionar al menos un día de elaboración (Lunes a Domingo).");
                return;
            }
        });
    }

    function mostrarErrorProducto(mensaje) {
        if (cajaErrorProductoJS) {
            cajaErrorProductoJS.textContent = mensaje;
            cajaErrorProductoJS.style.display = "block";
        } else {
            alert(mensaje);
        }
    }
});
