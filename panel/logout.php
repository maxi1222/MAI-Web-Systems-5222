<?php
// panel/logout.php
// Cierre de sesión seguro del personal

session_start();

// Vaciar todas las variables de sesión
$_SESSION = array();

// Destruir la sesión completamente en el servidor
session_destroy();

// Redirigir al formulario de login del panel
header("Location: login.php");
exit();
?>
