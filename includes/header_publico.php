<?php
// includes/header_publico.php
// Encabezado para las páginas públicas (clientes)

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$ruta_base = isset($ruta_base) ? $ruta_base : "";
$pagina_actual = isset($pagina_actual) ? $pagina_actual : "inicio";
$titulo_pagina = isset($titulo_pagina) ? $titulo_pagina : "Panadería La Ideal - MAI Web Systems";

// Contar cuántos productos hay en el carrito de sesión
$total_carrito = 0;
if (isset($_SESSION["carrito"]) && is_array($_SESSION["carrito"])) {
    foreach ($_SESSION["carrito"] as $item) {
        if (isset($item["cantidad"])) {
            $total_carrito = $total_carrito + (int)$item["cantidad"];
        } else {
            $total_carrito = $total_carrito + (int)$item;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($titulo_pagina, ENT_QUOTES, 'UTF-8'); ?></title>
    <link rel="stylesheet" href="<?php echo $ruta_base; ?>assets/css/estilos.css">
</head>
<body class="body-publico">
    <header class="navbar-publico">
        <div class="contenedor navbar-inner">
            <a href="<?php echo $ruta_base; ?>index.php" class="brand-logo">
                <span class="brand-icon">🥖</span>
                <div class="brand-text">
                    <span class="brand-title">Panadería La Ideal</span>
                    <span class="brand-subtitle">MAI Web Systems</span>
                </div>
            </a>

            <button type="button" class="btn-menu-movil" id="btnMenuMovil" aria-label="Abrir menú">
                ☰
            </button>

            <nav class="nav-links" id="navLinksPublico">
                <a href="<?php echo $ruta_base; ?>index.php" class="nav-link <?php echo ($pagina_actual === 'inicio') ? 'activo' : ''; ?>">Inicio</a>
                <a href="<?php echo $ruta_base; ?>catalogo.php" class="nav-link <?php echo ($pagina_actual === 'catalogo') ? 'activo' : ''; ?>">Catálogo</a>
                <?php if (isset($_SESSION["id_usuario"])): ?>
                    <a href="<?php echo $ruta_base; ?>panel/agenda.php" class="nav-link nav-link-panel">
                        Panel Interno (<?php echo htmlspecialchars($_SESSION["rol"], ENT_QUOTES, 'UTF-8'); ?>)
                    </a>
                <?php else: ?>
                    <a href="<?php echo $ruta_base; ?>panel/login.php" class="nav-link">Acceso Personal</a>
                <?php endif; ?>
            </nav>

            <div class="navbar-actions">
                <a href="<?php echo $ruta_base; ?>carrito.php" class="btn-carrito-header <?php echo ($pagina_actual === 'carrito') ? 'activo' : ''; ?>">
                    <span>🛒 Mi Pedido</span>
                    <span class="carrito-badge"><?php echo $total_carrito; ?></span>
                </a>
            </div>
        </div>
    </header>
    <main class="main-publico">
