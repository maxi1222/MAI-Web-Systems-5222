<?php
// panel/acciones/producto_guardar.php

require_once '../../includes/auth.php';
require_once '../../config/conexion.php';

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: ../productos.php");
    exit();
}

$id_producto = (isset($_POST["id_producto"]) && is_numeric($_POST["id_producto"])) ? (int)$_POST["id_producto"] : 0;
$nombre = isset($_POST["nombre"]) ? trim($_POST["nombre"]) : "";
$descripcion = isset($_POST["descripcion"]) ? trim($_POST["descripcion"]) : "";
$precio = isset($_POST["precio"]) ? trim($_POST["precio"]) : "";
$categoria = isset($_POST["categoria"]) ? trim($_POST["categoria"]) : "";

if (isset($_POST["disponible"])) {
    $disponible = ($_POST["disponible"] == "0") ? 0 : 1;
} else {
    $disponible = isset($_POST["control_disponible"]) ? 0 : 1;
}

$dias_elegidos = array();
if (isset($_POST["dias"]) && is_array($_POST["dias"])) {
    $dias_elegidos = $_POST["dias"];
} elseif (isset($_POST["dias_habilitados"]) && is_array($_POST["dias_habilitados"])) {
    $dias_elegidos = $_POST["dias_habilitados"];
} elseif (isset($_POST["dias_habilitados"]) && !empty(trim($_POST["dias_habilitados"]))) {
    $dias_elegidos = explode(",", trim($_POST["dias_habilitados"]));
}

$dias_limpios = array();
foreach ($dias_elegidos as $dia) {
    $dia_trim = trim($dia);
    if (!empty($dia_trim)) {
        $dias_limpios[] = $dia_trim;
    }
}

$errores = array();

if (empty($nombre) || is_numeric($nombre)) {
    $errores[] = "El nombre del producto es obligatorio y debe ser en formato de texto.";
}

if (empty($precio) || !is_numeric($precio) || (float)$precio <= 0) {
    $errores[] = "El precio del producto es obligatorio y debe ser un valor numérico válido.";
}

if (empty($categoria)) {
    $errores[] = "La categoría del producto es obligatoria.";
}

if (empty($dias_limpios)) {
    $errores[] = "Debe seleccionar al menos un día de la semana para la elaboración del producto.";
}

if (!empty($errores)) {
    $_SESSION["mensaje_error"] = implode(" ", $errores);
    if ($id_producto > 0) {
        header("Location: ../producto_editar.php?id=" . $id_producto . "&error=1");
    } else {
        header("Location: ../productos.php?error=1");
    }
    exit();
}

$dias_habilitados = implode(",", $dias_limpios);
$precio_entero = (int)round((float)$precio);

$imagen_url = isset($_POST["imagen_actual"]) ? trim($_POST["imagen_actual"]) : "";

if (isset($_FILES["imagen"]) && $_FILES["imagen"]["error"] === UPLOAD_ERR_OK) {
    $directorio_relativo = "assets/img/productos/";
    $directorio_destino = "../../" . $directorio_relativo;

    if (!is_dir($directorio_destino)) {
        mkdir($directorio_destino, 0775, true);
    }

    $nombre_original = basename($_FILES["imagen"]["name"]);
    $extension = strtolower(pathinfo($nombre_original, PATHINFO_EXTENSION));
    $extensiones_permitidas = array("jpg", "jpeg", "png", "webp", "gif");

    if (in_array($extension, $extensiones_permitidas)) {
        $nombre_seguro = preg_replace('/[^a-zA-Z0-9_\.-]/', '_', pathinfo($nombre_original, PATHINFO_FILENAME));
        $nombre_archivo = time() . "_" . $nombre_seguro . "." . $extension;
        $ruta_completa = $directorio_destino . $nombre_archivo;

        if (move_uploaded_file($_FILES["imagen"]["tmp_name"], $ruta_completa)) {
            $imagen_url = $directorio_relativo . $nombre_archivo;
        }
    }
} elseif (isset($_POST["imagen_url"]) && !empty(trim($_POST["imagen_url"]))) {
    $imagen_url = trim($_POST["imagen_url"]);
}

if ($id_producto > 0 && empty($imagen_url)) {
    $sql_img = "SELECT imagen_url FROM productos WHERE id_producto = ?";
    $stmt_img = $conexion->prepare($sql_img);
    $stmt_img->bind_param("i", $id_producto);
    $stmt_img->execute();
    $res_img = $stmt_img->get_result();
    if ($fila_img = $res_img->fetch_assoc()) {
        $imagen_url = $fila_img["imagen_url"];
    }
    $stmt_img->close();
}

if ($id_producto > 0) {
    $sql = "UPDATE productos SET nombre = ?, descripcion = ?, precio = ?, categoria = ?, imagen_url = ?, disponible = ?, dias_habilitados = ? WHERE id_producto = ?";
    $stmt = $conexion->prepare($sql);
    $stmt->bind_param(
        "ssissisi",
        $nombre,
        $descripcion,
        $precio_entero,
        $categoria,
        $imagen_url,
        $disponible,
        $dias_habilitados,
        $id_producto
    );
} else {
    $sql = "INSERT INTO productos (nombre, descripcion, precio, categoria, imagen_url, disponible, dias_habilitados) VALUES (?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conexion->prepare($sql);
    $stmt->bind_param(
        "ssissis",
        $nombre,
        $descripcion,
        $precio_entero,
        $categoria,
        $imagen_url,
        $disponible,
        $dias_habilitados
    );
}

if ($stmt->execute()) {
    $stmt->close();
    $_SESSION["mensaje_exito"] = "producto guardado exitosamente";

    if ($id_producto > 0 && isset($_POST["permanecer_en_editar"])) {
        header("Location: ../producto_editar.php?id=" . $id_producto . "&exito=1");
    } else {
        header("Location: ../productos.php?exito=1");
    }
    exit();
} else {
    $error_sql = $stmt->error;
    $stmt->close();
    error_log("Error al guardar producto: " . $error_sql);
    $_SESSION["mensaje_error"] = "Ocurrió un error al guardar el producto en la base de datos.";
    header("Location: ../productos.php?error=1");
    exit();
}
?>
