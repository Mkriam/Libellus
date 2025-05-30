<?php

/**
 * Controlador de registro de usuarios.
 * 
 * Procesa el formulario de registro, valida los datos y crea un nuevo usuario si todo es correcto.
 * Muestra mensajes de error o éxito usando variables de sesión.
 * 
 * @package Controlador
 * @author Miriam Rodríguez Antequera
 */

require_once '../modelo/Conexion.php';
require_once '../controlador/validaciones.php';
require_once '../modelo/Usuario.php';

session_start();

// Solo procesa si se envió el formulario de registro
if (filter_has_var(INPUT_POST, "botonRegistro")) {
    // Validar y limpiar los datos recibidos del formulario
    $nombre = validarUsu(filter_input(INPUT_POST, 'nombre'));
    $email = validarEmail(filter_input(INPUT_POST, 'email'));
    $contr = validarContr(filter_input(INPUT_POST, 'contr'));
    $confirmarContr = validarContr(filter_input(INPUT_POST, 'confirmarContr'));

    // Validaciones específicas y mensajes claros
    if (!$contr) {
        $_SESSION['mensajeError'] = "La contraseña debe tener entre 8 y 200 caracteres, al menos una letra y un número.";
        header("Location: ../vistas/registro.php");
        exit();
    } else if (!$nombre) {
        // Mensaje detallado para nombre de usuario inválido
        $_SESSION['mensajeError'] = "El nombre de usuario solo puede contener letras, números, guiones y guiones bajos, y debe tener entre 1 y 20 caracteres.";
        header("Location: ../vistas/registro.php");
        exit();
    } else if (!$email) {
        // Mensaje detallado para email inválido
        $_SESSION['mensajeError'] = "El correo electrónico no es válido. Debe tener el formato usuario@dominio.extensión (ejemplo: correo@ejemplo.com).";
        header("Location: ../vistas/registro.php");
        exit();
    } else if ($contr !== $confirmarContr) {
        $_SESSION['mensajeError'] = "Las contraseñas no coinciden.";
        header("Location: ../vistas/registro.php");
        exit();
    } else {
        // Si todos los campos son válidos y las contraseñas coinciden
        try {
            $usuario = new Usuario($nombre, $email, $contr);

            // Si se guarda correctamente, redirige al login con mensaje de éxito
            if ($usuario->guardarUsuario()) {
                $_SESSION['mensajeExito'] = "Usuario registrado exitosamente. Por favor, inicia sesión.";
                header("Location: ../vistas/login.php");
                exit();
            } else {
                // Si el usuario o email ya existen
                $_SESSION['mensajeError'] = "El nombre de usuario o el correo ya están registrados.";
            }
        } catch (Exception $ex) {
            // Si ocurre un error en la creación del usuario
            $_SESSION['mensajeError'] = "Error al registrar: " . $ex->getMessage();
        }
    }
} else {
    // Si se accede al script directamente sin enviar el formulario
    $_SESSION['mensajeError'] = "Acceso no permitido.";
}

// Redireccionar a la página de registro para mostrar mensajes
header("Location: ../vistas/registro.php");
