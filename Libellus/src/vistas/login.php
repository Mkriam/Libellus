<?php
// Vista de login de usuario para Libellus
// Muestra el formulario de inicio de sesión y los mensajes de éxito o error

session_start();
require_once '../controlador/validaciones.php';

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
    <title>Iniciar Sesión - Libellus</title>
    <link rel="stylesheet" href="./css/login_registro.css">
</head>

<body>
    <div class="container">
        <div class="logo">
            <!-- Logo que lleva al inicio -->
            <a href="../index.html"><img src="../img/logo_nom.png" alt="Libellus Logo"></a>
        </div>

        <!-- Muestra mensajes de éxito o error si existen -->
        <?php 
            if ($mensajeExito) { ?> <div class="mensajeExito"><?php echo validarCadena($mensajeExito); ?></div> <?php } ?>
        <?php 
            if ($mensajeError) { ?> <div class="mensajeError"><?php echo validarCadena($mensajeError); ?></div> <?php } 

        // También permite mostrar mensajes pasados por GET (tras redirección por header)
        $error = validarCadena(filter_input(INPUT_GET, "mensajeError"));
        $exito = validarCadena(filter_input(INPUT_GET, "mensajeExito"));
        if ($error) { ?>
            <div class="error"><?php echo $error; ?></div>
        <?php } else if ($exito) { ?>
            <div class="exito"><?php echo $exito; ?></div>
        <?php }; ?>

        <div class="form">
            <h1>Iniciar Sesión</h1>
            <!-- Formulario de login -->
            <form action="../controlador/controladorLogin.php" method="POST">
                <div class="parteForm">
                    <label for="email">Email:</label>
                    <input type="text" id="email" name="email" required>
                </div>
                <div class="parteForm">
                    <label for="contr">Contraseña:</label>
                    <input type="password" id="contr" name="contr" required>
                </div>
                <button type="submit" name="botonLogin" class="boton">Iniciar Sesión</button>
                <div class="link">
                    ¿No tienes cuenta? <a href="registro.php">Registrar</a>
                </div>
            </form>
        </div>
    </div>
</body>

</html>