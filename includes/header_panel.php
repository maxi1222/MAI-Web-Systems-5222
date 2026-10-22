<?php
// includes/header_panel.php
// Encabezado para el Panel Interno (Cocina, Mostrador y Administración)

require_once __DIR__ . '/auth.php';

$ruta_base = isset($ruta_base) ? $ruta_base : "../";
$pagina_panel = isset($pagina_panel) ? $pagina_panel : "agenda";
$titulo_pagina = isset($titulo_pagina) ? $titulo_pagina : "Panel Interno - Panadería La Ideal";

$nombre_usuario = isset($_SESSION["nombre"]) ? $_SESSION["nombre"] : "Personal";
$rol_usuario = isset($_SESSION["rol"]) ? $_SESSION["rol"] : "Empleado";
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($titulo_pagina, ENT_QUOTES, 'UTF-8'); ?></title>
    <link rel="stylesheet" href="<?php echo $ruta_base; ?>assets/css/estilos.css">
</head>
<body class="body-panel">
    <header class="navbar-panel">
        <div class="contenedor navbar-panel-inner">
            <a href="agenda.php" class="brand-logo brand-logo-panel">
                <span class="brand-icon">🥖</span>
                <div class="brand-text">
                    <span class="brand-title">Panadería La Ideal</span>
                    <span class="brand-subtitle">Sector Interno · <?php echo htmlspecialchars($rol_usuario, ENT_QUOTES, 'UTF-8'); ?></span>
                </div>
            </a>

            <button type="button" class="btn-menu-movil btn-menu-panel" id="btnMenuPanel" aria-label="Abrir menú del panel">
                ☰
            </button>

            <nav class="nav-panel-links" id="navLinksPanel">
                <a href="agenda.php" class="nav-panel-link <?php echo ($pagina_panel === 'agenda') ? 'activo' : ''; ?>">Agenda de Pedidos</a>
                <a href="calendario.php" class="nav-panel-link <?php echo ($pagina_panel === 'calendario') ? 'activo' : ''; ?>">Calendario Mensual</a>
                <a href="pedidos_buscar.php" class="nav-panel-link <?php echo ($pagina_panel === 'buscar') ? 'activo' : ''; ?>">Buscar Pedidos</a>
                <a href="productos.php" class="nav-panel-link <?php echo ($pagina_panel === 'productos') ? 'activo' : ''; ?>">Catálogo y Stock</a>
                <a href="<?php echo $ruta_base; ?>index.php" class="nav-panel-link nav-panel-tienda">Ver Tienda ↗</a>
            </nav>

            <div class="panel-user-info">
                <div class="user-pill">
                    <span class="user-name"><?php echo htmlspecialchars($nombre_usuario, ENT_QUOTES, 'UTF-8'); ?></span>
                    <span class="user-role-badge <?php echo ($rol_usuario === 'Administrador') ? 'rol-admin' : 'rol-empleado'; ?>">
                        <?php echo htmlspecialchars($rol_usuario, ENT_QUOTES, 'UTF-8'); ?>
                    </span>
                </div>
                <a href="logout.php" class="btn-logout-panel">Salir</a>
            </div>
        </div>
    </header>
    <main class="main-panel">
