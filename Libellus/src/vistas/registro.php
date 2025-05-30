<?php
// Vista de registro de usuario para Libellus
// Muestra el formulario de registro y los mensajes de éxito o error

require_once '../controlador/validaciones.php';

session_start();

// Recupera mensajes de éxito o error de la sesión (si existen)
$mensajeExito = $_SESSION['mensajeExito'] ?? null;
$mensajeError = $_SESSION['mensajeError'] ?? null;
// Elimina los mensajes de la sesión para que no se muestren de nuevo al recargar
unset($_SESSION['mensajeExito'], $_SESSION['mensajeError']);

?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro - Libellus</title>
    <link rel="stylesheet" href="./css/login_registro.css">
</head>

<body>
    <div class="container">
        <div class="logo">
            <!-- Logo que lleva al inicio -->
            <a href="../index.html"><img src="../img/logo_nom.png" alt="Logo Libellus"></a>
        </div>
        <div class="form">
            <h1>Crear Cuenta</h1>

            <!-- Muestra mensajes de éxito o error si existen -->
            <?php if ($mensajeExito) { ?> 
                <div class="mensajeExito"><?php echo validarCadena($mensajeExito); ?></div> 
            <?php } ?>
            <?php if ($mensajeError) { ?> 
                <div class="mensajeError"><?php echo validarCadena($mensajeError); ?></div> 
            <?php } ?>

            <!-- Formulario de registro -->
            <form action="../controlador/controladorRegistro.php" method="POST">
                <div class="parteForm">
                    <label for="nombre">Nombre usuario:</label>
                    <input type="text" id="nombre" name="nombre" placeholder="Entre 1 y 20 caracteres." required>
                </div>
                <div class="parteForm">
                    <label for="email">Correo Electrónico:</label>
                    <input type="email" id="email" name="email" placeholder="correo@ejemplo.com" required>
                </div>
                <div class="parteForm">
                    <label for="contr">Contraseña:</label>
                    <input type="password" id="contr" name="contr" placeholder="Al menos 8 caracteres, una letra y un número" required>
                </div>
                <div class="parteForm">
                    <label for="confirmarContr">Confirmar Contraseña:</label>
                    <input type="password" id="confirmarContr" name="confirmarContr" required>
                </div>
                <button type="submit" class="boton" name="botonRegistro">Registrarse</button>
                <div class="link">
                    ¿Ya tienes una cuenta? <a href="login.php">Iniciar Sesión</a>
                </div>
            </form>
        </div>
    </div>
</body>

</html>
