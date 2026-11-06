// assets/js/firebase_likes.js
// Integración con Firebase Realtime Database para leer y actualizar el árbol JSON
// /likes_productos/prod_{id_producto} e impedir likes duplicados por visitante (H.U-10)

document.addEventListener("DOMContentLoaded", function () {
    // URL base de Firebase Realtime Database del proyecto 'mai-panaderia'
    const FIREBASE_DB_URL = "https://mai-panaderia-default-rtdb.firebaseio.com";
    const STORAGE_KEY_LIKES = "mai_panaderia_likes_visitante";

    // 1. Obtener lista de IDs de productos a los que este visitante ya les dio like
    function obtenerLikesVisitante() {
        try {
            const data = localStorage.getItem(STORAGE_KEY_LIKES);
            return data ? JSON.parse(data) : [];
        } catch (e) {
            console.warn("[Firebase Likes] No se pudo acceder a localStorage:", e);
            return [];
        }
    }

    // 2. Guardar un nuevo ID de producto en el registro local del visitante
    function registrarLikeVisitante(idProducto) {
        try {
            const likes = obtenerLikesVisitante();
            const idNum = parseInt(idProducto, 10);
            if (!likes.includes(idNum)) {
                likes.push(idNum);
                localStorage.setItem(STORAGE_KEY_LIKES, JSON.stringify(likes));
            }
        } catch (e) {
            console.warn("[Firebase Likes] No se pudo guardar el like en localStorage:", e);
        }
    }

    // 3. Comprobar si este visitante ya dio like al producto
    function haDadoLike(idProducto) {
        const likes = obtenerLikesVisitante();
        return likes.includes(parseInt(idProducto, 10));
    }

    // 4. Leer todos los likes en una sola petición eficiente
    async function leerTodosLosLikesDeFirebase() {
        const endpoint = `${FIREBASE_DB_URL}/likes_productos.json`;
        try {
            const respuesta = await fetch(endpoint, {
                method: "GET",
                headers: { "Content-Type": "application/json" }
            });

            if (!respuesta.ok) return null;
            return await respuesta.json();
        } catch (error) {
            console.info("[Firebase Likes] Modo offline o sin conexión al leer colección:", error.message);
            return null;
        }
    }

    // 5. Leer contador de likes individual desde /likes_productos/prod_{id_producto}
    async function leerLikesDeFirebase(idProducto) {
        const endpoint = `${FIREBASE_DB_URL}/likes_productos/prod_${idProducto}.json`;
        try {
            const respuesta = await fetch(endpoint, {
                method: "GET",
                headers: { "Content-Type": "application/json" }
            });

            if (!respuesta.ok) {
                return 0;
            }

            const data = await respuesta.json();
            if (!data) return 0;
            if (typeof data === "number") return data;
            if (typeof data.total === "number") return data.total;
            return 0;
        } catch (error) {
            console.info(`[Firebase Likes] Modo offline o sin conexión en prod_${idProducto}:`, error.message);
            return 0;
        }
    }

    // 6. Actualizar e incrementar contador en el árbol JSON /likes_productos/prod_{id_producto}
    async function incrementarLikeEnFirebase(idProducto, totalActual) {
        const idNum = parseInt(idProducto, 10);
        if (isNaN(idNum) || idNum <= 0) {
            console.error("[Firebase Likes] id_producto inválido:", idProducto);
            return totalActual;
        }

        const endpoint = `${FIREBASE_DB_URL}/likes_productos/prod_${idNum}.json`;

        // Intentar obtener el conteo más reciente para evitar sobreescritura accidental
        let conteoBase = totalActual;
        try {
            const resActual = await fetch(endpoint, { method: "GET" });
            if (resActual.ok) {
                const dataActual = await resActual.json();
                if (dataActual && typeof dataActual.total === "number") {
                    conteoBase = dataActual.total;
                }
            }
        } catch (_) {
            // Si falla la lectura previa, usamos totalActual
        }

        const nuevoTotal = conteoBase + 1;
        const ahoraIso = new Date().toISOString();

        try {
            const respuesta = await fetch(endpoint, {
                method: "PATCH",
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify({
                    id_producto: idNum,
                    total: nuevoTotal,
                    actualizado_en: ahoraIso
                })
            });

            if (respuesta.ok) {
                return nuevoTotal;
            } else {
                const errorTexto = await respuesta.text().catch(() => "");
                console.warn("[Firebase Likes] Firebase rechazó la petición:", respuesta.status, errorTexto);
            }
        } catch (error) {
            console.warn("[Firebase Likes] Error de conexión al enviar like a Firebase:", error.message);
        }

        // Si Firebase no está disponible, reflejamos el nuevo total en interfaz
        return nuevoTotal;
    }

    // 7. Actualizar visualmente todos los botones de un mismo producto en el DOM
    function actualizarVisualizacionBoton(idProducto, total, activo) {
        const selector = `.btn-like[data-id="${idProducto}"]`;
        const botones = document.querySelectorAll(selector);
        botones.forEach(function (btn) {
            const contadorSpan = btn.querySelector(".like-contador");
            const corazonSpan = btn.querySelector(".corazon-icono");

            if (contadorSpan && typeof total === "number") {
                contadorSpan.textContent = total;
            }

            if (activo) {
                btn.classList.add("like-activo");
                if (corazonSpan) corazonSpan.textContent = "♥";
                btn.title = "Ya te gusta este producto";
            } else {
                btn.classList.remove("like-activo");
                if (corazonSpan) corazonSpan.textContent = "♡";
                btn.title = "Dar me gusta a este producto";
            }
        });
    }

    // 8. Seleccionar todos los botones de likes en las tarjetas de index.php y catalogo.php
    const botonesLike = document.querySelectorAll(".btn-like");
    if (!botonesLike || botonesLike.length === 0) {
        return;
    }

    // Cargar estado inicial visual de los botones según localStorage
    botonesLike.forEach(function (boton) {
        const idProducto = boton.getAttribute("data-id");
        if (!idProducto) return;
        const yaVoto = haDadoLike(idProducto);
        actualizarVisualizacionBoton(idProducto, undefined, yaVoto);
    });

    // Carga masiva de likes desde Firebase (1 sola llamada a /likes_productos.json)
    leerTodosLosLikesDeFirebase().then(function (todosLosLikes) {
        if (todosLosLikes && typeof todosLosLikes === "object") {
            botonesLike.forEach(function (boton) {
                const idProducto = boton.getAttribute("data-id");
                if (!idProducto) return;
                const nodo = todosLosLikes[`prod_${idProducto}`];
                let count = 0;
                if (nodo) {
                    if (typeof nodo.total === "number") count = nodo.total;
                    else if (typeof nodo === "number") count = nodo;
                }
                const contadorSpan = boton.querySelector(".like-contador");
                if (contadorSpan) contadorSpan.textContent = count;
            });
        } else {
            // Fallback: cargar individualmente si falló la lectura masiva
            botonesLike.forEach(function (boton) {
                const idProducto = boton.getAttribute("data-id");
                if (!idProducto) return;
                leerLikesDeFirebase(idProducto).then(function (totalLikes) {
                    const contadorSpan = boton.querySelector(".like-contador");
                    if (contadorSpan) contadorSpan.textContent = totalLikes;
                });
            });
        }
    });

    // 9. Asignar listener de click a cada botón
    botonesLike.forEach(function (boton) {
        const idProducto = boton.getAttribute("data-id");
        if (!idProducto) return;

        boton.addEventListener("click", async function (e) {
            e.preventDefault();

            // Evitar votos duplicados por visitante
            if (haDadoLike(idProducto)) {
                actualizarVisualizacionBoton(idProducto, undefined, true);
                return;
            }

            // Deshabilitar momentáneamente para evitar doble click
            boton.disabled = true;

            const contadorSpan = boton.querySelector(".like-contador");
            const totalActual = contadorSpan ? (parseInt(contadorSpan.textContent, 10) || 0) : 0;

            const nuevoTotal = await incrementarLikeEnFirebase(idProducto, totalActual);

            // Registrar al visitante en localStorage
            registrarLikeVisitante(idProducto);

            // Actualizar visualmente todos los elementos de este producto
            actualizarVisualizacionBoton(idProducto, nuevoTotal, true);

            boton.disabled = false;
        });
    });
});
