<?php
/**
 * Controlador de acciones sobre libros guardados del usuario.
 * 
 * Permite eliminar o modificar libros guardados en la lista personal del usuario.
 * Solo accesible para usuarios logueados que no sean administradores.
 * Muestra mensajes de éxito o error mediante variables de sesión.
 * 
 * @package Controlador
 * @author Miriam Rodríguez Antequera
 */

require_once '../modelo/Conexion.php';
require_once '../modelo/Usuario.php'; 
require_once '../modelo/Libro.php'; 
require_once '../controlador/validaciones.php';

session_start();

// Verificar que el usuario esté logueado y no sea administrador
if (!isset($_SESSION['usuario']) || $_SESSION['administrador'] !== 0) {
    $_SESSION['mensajeError'] = "Acceso no autorizado. Por favor, inicie sesión.";
    header("Location: ../vistas/login.php"); 
    exit();
}

$nomUsuario = $_SESSION['usuario']; 
$datosUsu = Usuario::verUsuarioPorNom($nomUsuario);

// Comprobar si el usuario existe realmente
if (!$datosUsu) {
    $_SESSION['mensajeError'] = "Usuario no válido. Debes iniciar sesión.";
    header("Location: ../vistas/login.php");
    exit();
}


// Procesa la acción recibida por POST
if (filter_has_var(INPUT_POST, "accion")) {
    $accion = validarCadena(filter_input(INPUT_POST, 'accion'));

    try {
        switch ($accion) {

            case 'eliminarLibroGuardado':
                // Elimina un libro de la lista guardada del usuario
                $idLibroEliminar = validarEnteroPositivo(filter_input(INPUT_POST, 'idLibro'));

                if ($idLibroEliminar) {
                    if ($datosUsu->eliminarLibroGuardado($idLibroEliminar)) {
                        $_SESSION['mensajeExito'] = "Libro eliminado de tu lista correctamente.";
                    } else {
                        $_SESSION['mensajeError'] = "No se pudo eliminar el libro de tu lista. Puede que ya haya sido eliminado o no se encontró.";
                    }
                } else {
                    $_SESSION['mensajeError'] = "ID de libro no válido para eliminar.";
                }
                break;

            case 'editarLibroUsu':
                // Edita el estado o comentario de un libro guardado
                $idLibroEditar = validarEnteroPositivo(filter_input(INPUT_POST, 'idLibro'));
                $nuevoEstado = validarCadena(filter_input(INPUT_POST, 'estadoLibro'));
                $comentarioNuevo = filter_input(INPUT_POST, 'comentarioLibro');

                // Validar el estado (entre los válidos)
                $estadosValidos = ['Completado', 'Leyendo', 'Pendiente'];
                if (!in_array($nuevoEstado, $estadosValidos)) {
                    $_SESSION['mensajeError'] = "El estado no es válido.";
                    header("Location: ../vistas/areaLibro.php?libro=" . $idLibroEditar);
                    exit();
                }
                
                // Validar comentario (puede ser nulo o cadena válida)
                $comentarioVal = is_null($comentarioNuevo) ? null : validarCadena($comentarioNuevo, 0, 500); 

                if ($idLibroEditar) {
                    // Actualiza el libro usando el método guardarLibro de Usuario
                    if ($datosUsu->guardarLibro($idLibroEditar, $nuevoEstado, $comentarioVal)) {
                        $_SESSION['mensajeExito'] = "Los datos del libro ".(Libro::verLibro($idLibroEditar))->getTitulo()." han sido actualizados.";
                        header("Location: ../vistas/areaLibro.php?libro=" . $idLibroEditar);
                        exit();
                    } else {
                        $_SESSION['mensajeError'] = "No se pudieron actualizar los datos del libro.";
                        header("Location: ../vistas/areaLibro.php?libro=" . $idLibroEditar);
                        exit();
                    }
                } else {
                    $_SESSION['mensajeError'] = "ID de libro no válido para modificar.";
                }
                break;

            default:
                // Acción no reconocida
                $_SESSION['mensajeError'] = "Acción de usuario desconocida.";
                break;
        }
    } catch (Exception $e) {
        // Guarda errores inesperados y los muestra al usuario
        $_SESSION['mensajeError'] = "Error: " . $e->getMessage();
        error_log("Error: " . $e->getMessage());
    }

} else {
    // Si no se especificó ninguna acción
    $_SESSION['mensajeError'] = "No se especificó ninguna acción.";
}

// Redirige por defecto al área de usuario
header("Location: ../vistas/areaUsuario.php");
exit();

?>