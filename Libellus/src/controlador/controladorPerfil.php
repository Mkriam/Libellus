<?php
/**
 * Controlador de perfil de usuario.
 * 
 * Permite al usuario cambiar su nombre, email, foto de perfil, contraseña o eliminar su cuenta.
 * Valida los datos recibidos y muestra mensajes de éxito o error mediante variables de sesión.
 * 
 * @package Controlador
 * @author Miriam Rodríguez Antequera
 */

require_once '../modelo/Usuario.php';
require_once 'validaciones.php';

session_start();

// Verifica que el usuario haya iniciado sesión
if (!isset($_SESSION['usuario'])) {
    $_SESSION['mensajeError'] = "Acceso denegado. Debes iniciar sesión.";
    header("Location: ../vistas/login.php");
    exit();
}

$nomUsuario =  $_SESSION['usuario'];
$datosUsu = Usuario::verUsuarioPorNom($nomUsuario);

// Comprobar si el usuario existe realmente en la base de datos
if (!$datosUsu) {
    $_SESSION['mensajeError'] = "Usuario no válido. Debes iniciar sesión.";
    header("Location: ../vistas/login.php");
    exit();
}

// Procesa la acción recibida por POST
if (filter_has_var(INPUT_POST, "accion")) {
    $accion = validarCadena(filter_input(INPUT_POST, 'accion'));

    if ($accion) {
        try {
            switch ($accion) {
                case 'cambiarNombre':
                    // Cambiar nombre de usuario
                    $nomNuevo = validarUsu(filter_input(INPUT_POST, 'nomNuevo'));
                    $claveUsu = validarContr(filter_input(INPUT_POST, 'claveUsu'));
                    $nomViejo = validarUsu($nomUsuario);
                    $claveHash = $datosUsu->getClaveHash();


                    // Verifica la contraseña actual
                    if (empty($claveUsu) || !$claveHash || !password_verify($claveUsu, $claveHash)) {
                        $_SESSION['mensajeError'] = "La contraseña  es incorrecta.";
                    } else if ($nomNuevo === $nomViejo) {
                        // El nuevo nombre debe ser diferente al actual
                        $_SESSION['mensajeError'] = "El nombre nuevo es igual al actual.";
                    } else if (!$nomNuevo) {
                        // El nuevo nombre debe tener formato válido
                        $_SESSION['mensajeError'] = "El nuevo nombre de usuario no tiene un formato válido.";
                    } else {
                        // Comprobar si ya existe el nombre de usuario
                        $nomExiste = Usuario::verUsuarioPorNom($nomNuevo);
                        if ($nomExiste) {
                            $_SESSION['mensajeError'] = "El nuevo nombre de usuario ('" . $nomNuevo . "') ya está en uso.";
                        } else {
                            // Intentar actualizar el nombre
                            if ($datosUsu->cambiarNombreUsu($nomNuevo)) {
                                $_SESSION['mensajeExito'] = "Nombre de usuario actualizado correctamente.";
                                $_SESSION['usuario'] = $nomNuevo;
                            } else {
                                $_SESSION['mensajeError'] = "Error al actualizar el nombre de usuario en la BD.";
                            }
                        }
                    }
                    break;

                case 'cambiarEmail':
                    // Cambiar email del usuario
                    $emailNuevo = validarEmail(filter_input(INPUT_POST, 'emailNuevo'));
                    $claveUsu = validarContr(filter_input(INPUT_POST, 'claveUsu'));
                    $emailViejo = $datosUsu->getEmail();
                    $nomViejo = $nomUsuario;
                    $claveHash = $datosUsu->getClaveHash();


                    // Verifica la contraseña actual
                    if (empty($claveUsu) || !$claveHash || !password_verify($claveUsu, $claveHash)) {
                        $_SESSION['mensajeError'] = "La contraseña de confirmación es incorrecta o no fue proporcionada.";
                    } else if ($emailNuevo === $emailViejo) {
                        $_SESSION['mensajeError'] = "El nuevo email es igual al actual.";
                    } else if (!$emailNuevo) {
                        $_SESSION['mensajeError'] = "El nuevo email no es válido.";
                    } else {
                        // Comprobar si ya existe el email en otro usuario
                        $emailExiste = Usuario::verUsuarioPorEmail($emailNuevo);
                        if ($emailExiste !== null && $emailExiste->getNomUsu() !== $nomViejo) {
                            $_SESSION['mensajeError'] = "El nuevo email ('" . $emailNuevo . "') ya está en uso.";
                        } else if ($datosUsu->setEmail($emailNuevo)) {
                            // Intentar actualizar el email
                            if ($datosUsu->actualizarDatos()) {
                                $_SESSION['mensajeExito'] = "Email actualizado correctamente.";
                                $_SESSION['usuario'] = $nomUsuario;
                            } else {
                                $_SESSION['mensajeError'] = "Error al actualizar el email en la base de datos.";
                                $datosUsu->setEmail($emailViejo); // Revertir el cambio si da error
                            }
                        } else {
                            $_SESSION['mensajeError'] = "Error al intentar cambiar el email.";
                        }
                    }
                    break;

                case 'cambiarFoto':
                    // Cambiar foto de perfil
                    $nuevaFoto = filter_input(INPUT_POST, 'fotoNueva');
                    $error = false;
                    if (empty(trim($nuevaFoto))) {
                        $nuevaFoto = null;
                    } else if (!validarUrl($nuevaFoto)) {
                        $_SESSION['mensajeError'] = "La URL de la foto no es válida.";
                        $error = true;
                    }

                    if (!$error) {
                        $datosUsu->setFotoPerfil($nuevaFoto);
                        if ($datosUsu->actualizarDatos()) {
                            $_SESSION['usuario'] = $nomUsuario;
                            // Si no hay foto, se pone la imagen por defecto
                            if (is_null($nuevaFoto)) {
                                $_SESSION['fotoUsu'] = '../img/avatar.png';
                            } else {
                                $_SESSION['fotoUsu'] = $nuevaFoto;
                            }
                            $_SESSION['mensajeExito'] = "Foto de perfil actualizada correctamente.";
                        } else {
                            $_SESSION['mensajeError'] = "No se pudo actualizar la foto de perfil.";
                        }
                    }
                    break;

                case 'cambiarClave':
                    // Cambiar contraseña del usuario
                    $claveVieja = validarContr(filter_input(INPUT_POST, 'claveVieja'));
                    $claveNueva = validarContr(filter_input(INPUT_POST, 'claveNueva'));
                    $claveNuevaConfirm = validarContr(filter_input(INPUT_POST, 'claveNuevaConfirm'));
                    $claveHash = $datosUsu->getClaveHash();

                    // Validaciones de entrada
                    if (empty($claveVieja) || empty($claveNueva) || empty($claveNuevaConfirm)) {
                        $_SESSION['mensajeError'] = "Rellene todos los campos.";
                    } else if ($claveNueva !== $claveNuevaConfirm) {
                        $_SESSION['mensajeError'] = "Las nuevas contraseñas no coinciden.";
                    } else if (!validarContr($claveNueva)) {
                        $_SESSION['mensajeError'] = "La nueva contraseña no cumple con los requisitos.";
                    } else if ($claveNueva === $claveVieja) {
                        $_SESSION['mensajeError'] = "La nueva contraseña no puede ser igual a la actual.";
                    } else {
                        // Verificar contraseña actual
                        if (!$claveHash || !password_verify($claveVieja, $claveHash)) {
                            $_SESSION['mensajeError'] = "La contraseña actual es incorrecta.";
                        } else if ($datosUsu->setClave($claveNueva)) {
                            // Guardar el nuevo hash en la base de datos
                            if ($datosUsu->cambiarClave()) {
                                $_SESSION['usuario'] = $nomUsuario;
                                $_SESSION['mensajeExito'] = "Contraseña actualizada correctamente.";
                            } else {
                                $_SESSION['mensajeError'] = "No se pudo actualizar la contraseña en la base de datos.";
                            }
                        } else {
                            $_SESSION['mensajeError'] = "Error al procesar la nueva contraseña (formato inválido).";
                        }
                    }
                    break;

                case 'eliminarCuenta':
                    // Eliminar la cuenta del usuario
                    $claveConfirm = validarContr(filter_input(INPUT_POST, 'claveConfirm'));

                    if (empty($claveConfirm)) {
                        $_SESSION['mensajeError'] = "Debes introducir tu contraseña actual para poder eliminar la cuenta.";
                        header("Location: ../vistas/perfil.php");
                        exit();
                    }

                    $claveHash = $datosUsu->getClaveHash();

                    // Verificar si la contraseña es correcta
                    if (!$claveHash || !password_verify($claveConfirm, $claveHash)) {
                        $_SESSION['mensajeError'] = "Contraseña incorrecta. No se pudo eliminar la cuenta.";
                    } else {
                        if (Usuario::eliminarUsuario($nomUsuario)) {
                            // Cierra la sesión y muestra mensaje de éxito
                            session_unset();
                            session_destroy();
                            session_start();
                            $_SESSION['mensajeExito'] = "Tu cuenta ha sido eliminada permanentemente.";
                            header("Location: ../vistas/login.php");
                            exit();
                        } else {
                            $_SESSION['mensajeError'] = "Ocurrió un error al intentar eliminar tu cuenta. Por favor, inténtalo de nuevo más tarde.";
                        }
                    }
                    break;

                default:
                    $_SESSION['mensajeError'] = "Acción no válida.";
                    break;
            }
        } catch (Exception $ex) {
            // Captura errores inesperados y los muestra al usuario
            $_SESSION['mensajeError'] = "Error con su solicitud: " . $ex->getMessage();
        }
    } else {
        $_SESSION['mensajeError'] = "No eligió ninguna opción válida.";
    }
}

// Redirige siempre al perfil tras procesar la acción
header("Location: ../vistas/perfil.php");
exit();
