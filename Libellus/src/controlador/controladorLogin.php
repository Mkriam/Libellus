<?php
/**
 * Controlador de login de usuario.
 * 
 * Valida los datos recibidos del formulario de login, verifica el usuario y la contraseña,
 * inicia la sesión y redirige según el rol. Muestra mensajes de error mediante variables de sesión.
 * 
 * @package Controlador
 * @author Miriam Rodríguez Antequera
 */

require_once '../modelo/Usuario.php';
require_once 'validaciones.php';

session_start();

// Conexión a la base de datos (puede ser usada por Usuario internamente)
$conexion = new Conexion("libellus", "db", "miriam", "libreria123");

// Procesa el formulario solo si se ha enviado el botón de login
if (filter_has_var(INPUT_POST, "botonLogin")) {
    // Validar y limpiar los datos recibidos
    $email = validarEmail(filter_input(INPUT_POST, 'email'));
    $contr = validarContr(filter_input(INPUT_POST, 'contr'));

    if ($email && $contr) {
        // Verifica usuario y contraseña en la base de datos
        $datosUsu = Usuario::verificarLogin($email, $contr);

        // Si el usuario existe y la contraseña es correcta
        if ($datosUsu instanceof Usuario) {
            $_SESSION['usuario'] = $datosUsu->getNomUsu();
            $_SESSION['gruposUsu'] = $datosUsu->getGrupos();
            $_SESSION['administrador'] = $datosUsu->esAdministrador();

            // Obtener foto de perfil, si no es válida se pone la imagen por defecto
            $fotoUsu = $datosUsu->getFotoPerfil();
            if (!validarUrl($fotoUsu)) {
                $_SESSION['fotoUsu'] = '../img/avatar.png';
            } else {
                $_SESSION['fotoUsu'] = validarUrl($fotoUsu);
            }

            // Redireccionar según el rol del usuario
            if ($datosUsu->esAdministrador()) {
                header("Location: ../vistas/areaAdmin.php");
            } else {
                header("Location: ../vistas/areaUsuario.php");
            }
            exit();
        } else {
            // Usuario o contraseña incorrectos
            $_SESSION['mensajeError'] = "El email o la contraseña no son correctos.";
        }
    } else {
        // Datos no válidos o no introducidos
        $_SESSION['mensajeError'] = "No ha introducido los datos requeridos o no son válidos.";
    }
} else {
    // Si se accede al script directamente sin enviar el formulario
    $_SESSION['mensajeError'] = "Acceso no permitido.";
}

// Redireccionar a la página de login para mostrar mensajes
header("Location: ../vistas/login.php");
