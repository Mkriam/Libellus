<?php
// Vista de perfil de usuario para Libellus
// Permite ver y modificar datos personales, cambiar contraseña, foto, email y eliminar la cuenta

require_once '../modelo/Usuario.php';
require_once '../controlador/validaciones.php';
session_start();

// Comprobación de usuario: si no hay sesión, redirige al login
if (!isset($_SESSION['usuario'])) {
    header("Location: login.php?$mensajeError = Sesión inválida. Por favor, inicie sesión.");
    exit();
}

// Obtener foto perfil y datos del usuario
$fotoUsu = $_SESSION['fotoUsu'];
$nomUsuario = $_SESSION['usuario'];
$usuario = Usuario::verUsuarioPorNom($nomUsuario);

// Comprobar si el usuario existe realmente en la BD
if (!$usuario) {
    $_SESSION['mensajeError'] = "Usuario no válido. Debes iniciar sesión.";
    header("Location: login.php");
    exit();
}

// Mensajes de éxito o error tras acciones
$mensajeExito = $_SESSION['mensajeExito'] ?? null;
$mensajeError = $_SESSION['mensajeError'] ?? null;
unset($_SESSION['mensajeExito'], $_SESSION['mensajeError']);

?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mi Perfil - Libellus</title>

    <!-- Bootstrap y estilos propios -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="./css/perfil.css">
    <link rel="stylesheet" href="./css/header.css">

    <script>
        // Muestra el formulario seleccionado y oculta los demás
        function mostrarFormularioPerfil(idFormulario) {
            const formulariosOcultables = document.querySelectorAll('.formularioOcultable');
            formulariosOcultables.forEach(form => {
                form.style.display = 'none';
            });

            const formularioSeleccionado = document.getElementById(idFormulario);
            if (formularioSeleccionado) {
                formularioSeleccionado.style.display = 'block';
                formularioSeleccionado.scrollIntoView({
                    behavior: 'smooth',
                    block: 'nearest'
                });
            }
        }
    </script>
</head>

<body>
    <header>
        <div class="logo">
            <img src="../img/logo.png" alt="Logo Libellus">
        </div>
        <div class="titulo">
            <!-- Enlace al área correspondiente según el rol -->
            <a href="<?php if ($_SESSION['administrador'] == 1) echo "areaAdmin.php"; else echo "areaUsuario.php" ?>">Libellus</a>
        </div>
        <div class="dropdown areaUsu">
            <a href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                <img src="<?php echo validarCadena($fotoUsu); ?>" alt="Foto de perfil" class="dropdown-toggle">
            </a>
            <ul class="dropdown-menu dropdown-menu-end">
                <li><a class="dropdown-item" href="perfil.php">Mi Perfil</a></li>
                <li><a class="dropdown-item" href="areaUsuario.php">Mi Lista</a></li>
                <li><hr class="dropdown-divider"></li>
                <li><a class="dropdown-item" href="../controlador/cerrar_sesion.php">Cerrar sesión</a></li>
            </ul>
        </div>
    </header>

    <main>
        <h1>Mi Perfil</h1>

        <!-- Mensajes de éxito o error tras acciones -->
        <?php if ($mensajeExito) { ?> <div class="mensajeExito"><?php echo validarCadena($mensajeExito); ?></div> <?php } ?>
        <?php if ($mensajeError) { ?> <div class="mensajeError"><?php echo validarCadena($mensajeError); ?></div> <?php } ?>

        <div class="perfil-info">
            <!-- Muestra la foto, nombre y email del usuario -->
            <img src="<?php echo validarCadena($fotoUsu); ?>" alt="Foto de perfil">
            <p><strong>Usuario:</strong> <?php echo validarCadena($nomUsuario); ?></p>
            <p><strong>Email:</strong> <?php echo validarCadena($usuario->getEmail()); ?></p>
        </div>

        <div class="botones-accion">
            <!-- Botones para mostrar los distintos formularios de edición -->
            <button type="button" onclick="mostrarFormularioPerfil('formCambiarNombre')">Cambiar Nombre Usuario</button>
            <button type="button" onclick="mostrarFormularioPerfil('formCambiarEmail')">Cambiar Email</button>
            <button type="button" onclick="mostrarFormularioPerfil('formCambiarFoto')">Cambiar Foto</button>
            <button type="button" onclick="mostrarFormularioPerfil('formCambiarClave')">Cambiar Contraseña</button>
        </div>

        <!-- Formulario para cambiar nombre de usuario -->
        <div id="formCambiarNombre" class="formulario-contenedor formularioOcultable">
            <h2>Cambiar Nombre de Usuario</h2>
            <form action="../controlador/controladorPerfil.php" method="POST">
                <div>
                    <label for="nomNuevo">Nuevo Nombre:</label>
                    <input type="text" id="nomNuevo" name="nomNuevo" value="<?php echo (validarUsu($nomUsuario)); ?>" required>
                    <small>Debe ser único y cumplir el formato válido (sin espacios).</small>
                </div>
                <div>
                    <label for="claveUsu">Contraseña Actual (para confirmar):</label>
                    <input type="password" id="claveUsu" name="claveUsu" required>
                </div>
                <button type="submit" name="accion" value="cambiarNombre">Guardar Nuevo Nombre</button>
            </form>
        </div>

        <!-- Formulario para cambiar email -->
        <div id="formCambiarEmail" class="formulario-contenedor formularioOcultable">
            <h2>Cambiar Email</h2>
            <form action="../controlador/controladorPerfil.php" method="POST">
                <div>
                    <label for="emailNuevo">Nuevo Email:</label>
                    <input type="email" id="emailNuevo" name="emailNuevo" value="<?php echo validarEmail($usuario->getEmail()); ?>" required>
                    <small>Debe ser único y tener formato de email válido.</small>
                </div>
                <div>
                    <label for="claveUsu">Contraseña:</label>
                    <input type="password" id="claveUsu" name="claveUsu" required>
                </div>
                <button type="submit" name="accion" value="cambiarEmail">Guardar Nuevo Email</button>
            </form>
        </div>

        <!-- Formulario para cambiar foto de perfil -->
        <div id="formCambiarFoto" class="formulario-contenedor formularioOcultable">
            <h2>Actualizar Foto de Perfil</h2>
            <form action="../controlador/controladorPerfil.php" method="POST">
                <div>
                    <label for="fotoNueva">Nueva URL de Foto de Perfil:</label>
                    <input type="url" id="fotoNueva" name="fotoNueva" placeholder="https://ejemplo.com/imagen.jpg" value="<?php echo validarUrl($usuario->getFotoPerfil() ?? ''); ?>">
                    <small>Deja en blanco para eliminar la foto actual y volver a la "Por Defecto".</small>
                </div>
                <button type="submit" name="accion" value="cambiarFoto">Cambiar Foto</button>
            </form>
        </div>

        <!-- Formulario para cambiar contraseña -->
        <div id="formCambiarClave" class="formulario-contenedor formularioOcultable">
            <h2>Cambiar Contraseña</h2>
            <form action="../controlador/controladorPerfil.php" method="POST">
                <div>
                    <label for="claveVieja">Contraseña Actual:</label>
                    <input type="password" id="claveVieja" name="claveVieja" required>
                </div>
                <div>
                    <label for="claveNueva">Nueva Contraseña:</label>
                    <input type="password" id="claveNueva" name="claveNueva" required>
                </div>
                <div>
                    <label for="claveNuevaConfirm">Confirmar Nueva Contraseña:</label>
                    <input type="password" id="claveNuevaConfirm" name="claveNuevaConfirm" required>
                </div>
                <button type="submit" name="accion" value="cambiarClave">Cambiar Contraseña</button>
            </form>
        </div>

        <!-- Zona de peligro: eliminar cuenta -->
        <div id="contenedor_zona_peligro" class="formulario-contenedor zona-peligro">
            <h2>Zona de Peligro</h2>

            <div id="botonEliminarCuenta" class="botones-accion">
                <button type="button" class="zona-peligro-boton-inicial" onclick="mostrarFormularioPerfil('confirmEliminar')">
                    Eliminar Cuenta...
                </button>
            </div>

            <!-- Formulario de confirmación para eliminar cuenta -->
            <div id="confirmEliminar" class="formularioOcultable">
                <p>¡Acción irreversible! <br> Para confirmar, introduce tu contraseña actual.</p>
                <form action="../controlador/controladorPerfil.php" method="POST" onsubmit="return confirm('¿Estás ABSOLUTAMENTE seguro de que quieres eliminar tu cuenta?\n\n¡¡¡ESTA ACCIÓN ES IRREVERSIBLE!!!');">
                    <div>
                        <input type="password" id="claveConfirm" name="claveConfirm" required>
                    </div>
                    <button type="submit" name="accion" value="eliminarCuenta">Eliminar Mi Cuenta Permanentemente</button>
                </form>
            </div>
        </div>

    </main>
    <!-- Bootstrap JS para componentes interactivos del header -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>