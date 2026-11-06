<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../config/conexion.php';
require_once __DIR__ . '/../../includes/auth.php';

// 1. Verificar sesión y rol de Administrador (RNF-02 y RF-05)
if (function_exists('requerir_admin')) {
    requerir_admin();
} elseif (function_exists('verificar_admin')) {
    verificar_admin();
} else {
    $rol_actual = $_SESSION['rol'] ?? ($_SESSION['usuario']['rol'] ?? '');
    if ($rol_actual !== 'Administrador') {
        header('Location: ../productos.php?error=' . urlencode('No tienes permisos para dar de baja productos'));
        exit;
    }
}

// 2. Obtener la conexión mysqli definida en config/conexion.php
$db = $conexion ?? $conn ?? $mysqli ?? null;
if (!$db) {
    header('Location: ../productos.php?error=' . urlencode('Error de conexión a la base de datos'));
    exit;
}

// 3. Capturar y validar el ID del producto (compatible con formulario POST o enlace GET)
$id_raw = $_POST['id_producto'] ?? $_GET['id_producto'] ?? $_POST['id'] ?? $_GET['id'] ?? null;

if ($id_raw === null || !is_numeric($id_raw) || (int)$id_raw <= 0) {
    header('Location: ../productos.php?error=' . urlencode('ID de producto inválido'));
    exit;
}

$id_producto = (int)$id_raw;

// 4. Ejecutar la baja lógica en la tabla productos 
// Además de pasar activo = 0, quitamos la marca de destacado para que no quede en portada (HU10-T03)
$sql = "UPDATE productos SET activo = 0, destacado = 0 WHERE id_producto = ?";
$stmt = $db->prepare($sql);

if ($stmt) {
    $stmt->bind_param("i", $id_producto);
    
    if ($stmt->execute()) {
        $stmt->close();
        $_SESSION['mensaje'] = 'Producto dado de baja exitosamente';
        header('Location: ../productos.php?mensaje=' . urlencode('Producto dado de baja exitosamente') . '&exito=1');
        exit;
    }
    $stmt->close();
}

header('Location: ../productos.php?error=' . urlencode('No se pudo dar de baja el producto'));
exit;