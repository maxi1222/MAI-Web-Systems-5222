<?php
// panel/login.php
// Inicio de sesión del personal verificando correo y contraseña contra la tabla usuarios

session_start();
require_once '../config/conexion.php';

// Si ya existe una sesión activa, redirigir al panel
if (isset($_SESSION["id_usuario"]) && isset($_SESSION["rol"])) {
    header("Location: agenda.php");
    exit();
}

$mensaje_error = "";

// Procesar el formulario cuando se envía por POST
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $campo_email = isset($_POST["email"]) ? $_POST["email"] : "";
    $email = trim($campo_email);
    $password = isset($_POST["password"]) ? $_POST["password"] : "";

    // Validar que los campos no estén vacíos
    if (empty($email) || empty($password)) {
        $mensaje_error = "Por favor, complete el correo electrónico y la contraseña.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $mensaje_error = "El formato del correo electrónico no es válido.";
    } else {
        try {
            // Consulta preparada para buscar el usuario por correo electrónico
            $sql = "SELECT id_usuario, nombre, email, password, rol FROM usuarios WHERE email = ?";
            $stmt = $conexion->prepare($sql);
            $stmt->bind_param("s", $email);
            $stmt->execute();

            $resultado = $stmt->get_result();
            $user = $resultado->fetch_assoc();
            $stmt->close();

            // Verificar si el usuario existe y la contraseña coincide con el hash Bcrypt
            if ($user && password_verify($password, $user["password"])) {
                // Comprobar si el hash necesita actualizarse al estándar actual
                if (password_needs_rehash($user["password"], PASSWORD_DEFAULT)) {
                    $nuevo_hash = password_hash($password, PASSWORD_DEFAULT);
                    $sql_rehash = "UPDATE usuarios SET password = ? WHERE id_usuario = ?";
                    $stmt_rehash = $conexion->prepare($sql_rehash);
                    $stmt_rehash->bind_param("si", $nuevo_hash, $user["id_usuario"]);
                    $stmt_rehash->execute();
                    $stmt_rehash->close();
                }

                // Regenerar ID de sesión por seguridad
                session_regenerate_id(true);

                // Guardar datos del usuario y su rol (Administrador o Empleado) en $_SESSION
                $_SESSION["id_usuario"] = $user["id_usuario"];
                $_SESSION["nombre"] = $user["nombre"];
                $_SESSION["usuario"] = $user["nombre"];
                $_SESSION["email"] = $user["email"];
                $_SESSION["rol"] = $user["rol"];

                header("Location: agenda.php");
                exit();
            } else {
                // Mensaje genérico por seguridad
                $mensaje_error = "Correo o contraseña incorrectos.";
            }
        } catch (mysqli_sql_exception $e) {
            error_log("Error en login: " . $e->getMessage());
            $mensaje_error = "Ocurrió un error al iniciar sesión. Intente nuevamente más tarde.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión</title>
    <link rel="stylesheet" href="../assets/css/estilos.css">
</head>
<body>
    <main class="login-contenedor">
        <section class="login-tarjeta">
            <h1>Acceso del Personal</h1>
            <p>Inicio de sesion para administradores y empleados</p>

            <?php if (!empty($mensaje_error)): ?>
                <div class="alerta alerta-error" role="alert">
                    <?php echo htmlspecialchars($mensaje_error, ENT_QUOTES, 'UTF-8'); ?>
                </div>
            <?php endif; ?>

            <form action="login.php" method="POST" class="formulario-login">
                <div class="grupo-formulario">
                    <label for="email">Correo electrónico:</label>
                    <input
                        type="email"
                        id="email"
                        name="email"
                        required
                        placeholder="ejemplo@correo.com"
                        value="<?php echo isset($email) ? htmlspecialchars($email, ENT_QUOTES, 'UTF-8') : ''; ?>"
                    >
                </div>

                <div class="grupo-formulario">
                    <label for="password">Contraseña:</label>
                    <input
                        type="password"
                        id="password"
                        name="password"
                        required
                        placeholder="••••••••"
                    >
                </div>

                <button type="submit" class="btn btn-primario">Iniciar Sesión</button>
            </form>

            <div class="enlace-volver">
                <a href="../index.php">&larr; Volver a la tienda</a>
            </div>
        </section>
    </main>
</body>
</html>
