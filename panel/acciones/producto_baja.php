<?php
// panel/acciones/producto_baja.php
// Baja lógica de un producto (HU09-T03)

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../config/conexion.php';
require_once __DIR__ . '/../../includes/auth.php';

// 1. Validar que el usuario tenga rol Administrador (RNF-02 y RF-05)
requerir_administrador();

// 2. Obtener la conexión a la base de datos
$db = $conexion ?? $conn ?? $mysqli ?? null;
if (!$db) {
    $_SESSION['mensaje_error'] = 'Error de conexión a la base de datos.';
    header('Location: ../productos.php?error=conexion');
    exit();
}

// 3. Capturar y validar el ID del producto (compatible con POST o GET, id_producto o id)
$id_raw = $_POST['id_producto'] ?? $_GET['id_producto'] ?? $_POST['id'] ?? $_GET['id'] ?? null;

if ($id_raw === null || !is_numeric($id_raw) || (int)$id_raw <= 0) {
    $_SESSION['mensaje_error'] = 'ID de producto inválido.';
    header('Location: ../productos.php?error=id_invalido');
    exit();
}

$id_producto = (int)$id_raw;

// 4. Ejecutar la baja lógica en la tabla productos pasando activo = 0 y quitando destacado = 0
$sql = "UPDATE productos SET activo = 0, destacado = 0 WHERE id_producto = ?";
$stmt = $db->prepare($sql);

if ($stmt) {
    $stmt->bind_param("i", $id_producto);
    
    if ($stmt->execute()) {
        $stmt->close();
        $_SESSION['mensaje_exito'] = 'Producto dado de baja exitosamente.';
        header('Location: ../productos.php?exito=1');
        exit();
    }
    $error_msg = $stmt->error;
    $stmt->close();
    error_log("Error al dar de baja el producto: " . $error_msg);
}

$_SESSION['mensaje_error'] = 'No se pudo dar de baja el producto.';
header('Location: ../productos.php?error=1');
exit();
?>
