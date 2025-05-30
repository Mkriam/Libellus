<?php

/**
 * Controlador de información y acciones sobre grupos.
 *
 * Permite a los usuarios salir de un grupo o añadir libros a un grupo (si son líderes).
 * Valida la sesión, los permisos y los datos recibidos. Responde en JSON para AJAX o redirige para peticiones normales.
 *
 * @package Controlador
 * @author Miriam Rodríguez Antequera
 */

require_once '../modelo/Usuario.php';
require_once '../modelo/Grupo.php';
require_once '../modelo/Libro.php';
require_once '../controlador/validaciones.php';

session_start();

// Verifica que el usuario haya iniciado sesión
if (!isset($_SESSION['usuario'])) {
    $_SESSION['mensajeError'] = "Acceso denegado. Debes iniciar sesión.";
    // Si es una petición AJAX, responde en JSON
    if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
        http_response_code(403); 
        echo json_encode(['error' => $_SESSION['mensajeError']]);
        exit();
    }
    header("Location: ../vistas/login.php");
    exit();
}

$nomUsu = $_SESSION['usuario'];
$datosUsu = Usuario::verUsuarioPorNom($nomUsu);

// Comprobar si el usuario existe realmente
if (!$datosUsu) {
    $_SESSION['mensajeError'] = "Usuario no válido. Debes iniciar sesión.";
    // Si es una petición AJAX, responde en JSON (aunque menos probable en este punto)
    if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
        http_response_code(401); // Unauthorized
        echo json_encode(['error' => $_SESSION['mensajeError']]);
        exit();
    }
    header("Location: ../vistas/login.php");
    exit();
}

// URL de redirección por defecto o basada en el grupo actual (si se accede directamente a este controlador con GET)
// Para las acciones POST, la redirección se maneja dentro de cada case o al final.
$redirectUrl = "../vistas/areaUsuario.php";
if (filter_has_var(INPUT_GET, "grupo")) { // Corregido 'nomGrupo' a 'grupo' para coincidir con infoGrupo.php
    $nombreGrupoDesdeGet = validarCadena(filter_input(INPUT_GET, 'grupo'));
    if ($nombreGrupoDesdeGet) {
        $redirectUrl = "../vistas/infoGrupo.php?grupo=" . urlencode($nombreGrupoDesdeGet);
    }
}


// Recibe la acción por POST
if (filter_has_var(INPUT_POST, "accion")) {
    $accion = validarCadena(filter_input(INPUT_POST, 'accion'));

    if ($accion) {
        try {
            switch ($accion) {
                case 'salirGrupo':
                    $nomGrupoParaSalir = validarCadena(filter_input(INPUT_POST, 'nomGrupo'));
                    if (!$nomGrupoParaSalir) {
                        $_SESSION['mensajeError'] = "Nombre del grupo no especificado.";
                        header("Location:" . $redirectUrl); // Usa la URL de redirección general si no hay grupo específico
                        exit();
                    }

                    $grupo = Grupo::obtenerGrupoPorNombre($nomGrupoParaSalir);
                    if (!$grupo instanceof Grupo) {
                        $_SESSION['mensajeError'] = "Grupo no encontrado.";
                        header("Location: ../vistas/areaUsuario.php"); // Redirige a un lugar seguro
                        exit();
                    }

                    // Actualizar redirectUrl para este contexto específico
                    $redirectUrlGrupoEspecifico = "../vistas/infoGrupo.php?grupo=" . urlencode($nomGrupoParaSalir);


                    if ($grupo->getIdLider() === $datosUsu->getNomUsu()) {
                        $_SESSION['mensajeError'] = "Eres el líder del grupo. No puedes salir directamente. Considera transferir el liderazgo o eliminar el grupo.";
                        header("Location: " . $redirectUrlGrupoEspecifico);
                        exit();
                    }

                    if ($grupo->eliminarMiembro($datosUsu->getNomUsu())) {
                        $_SESSION['mensajeExito'] = "Has salido del grupo '" . $nomGrupoParaSalir . "'.";
                        $_SESSION['gruposUsu'] = $datosUsu->getGrupos(); // Actualizar la lista de grupos en sesión
                        header("Location: ../vistas/areaUsuario.php"); // Redirigir a la página principal de grupos
                        exit();
                    } else {
                        $_SESSION['mensajeError'] = "Error al intentar salir del grupo.";
                        header("Location: " . $redirectUrlGrupoEspecifico); // Volver a la página del grupo si falla
                        exit();
                    }
                    break;

                case 'addLibroGrupo':
                    header('Content-Type: application/json');

                    $idLibro = validarEnteroPositivo(filter_input(INPUT_POST, 'id_libro'));
                    $nomGrupoAddLibro = validarCadena(filter_input(INPUT_POST, 'nomGrupoAdd'));

                    if (!$idLibro || !$nomGrupoAddLibro) {
                        http_response_code(400); // Bad Request
                        echo json_encode(['error' => 'Faltan datos: ID de libro o nombre de grupo.']);
                        exit();
                    }

                    $grupo = Grupo::obtenerGrupoPorNombre($nomGrupoAddLibro);
                    if (!$grupo) {
                        http_response_code(404);
                        echo json_encode(['error' => "Grupo con nombre '" . $nomGrupoAddLibro . "' no encontrado."]);
                        exit();
                    }

                    if ($grupo->getIdLider() !== $datosUsu->getNomUsu()) {
                        http_response_code(403); 
                        echo json_encode(['error' => 'No tienes permiso para añadir libros a este grupo.']);
                        exit();
                    }

                    $libro = Libro::verLibro($idLibro);
                    if (!$libro) {
                        http_response_code(404); // Not Found
                        echo json_encode(['error' => "Libro con ID $idLibro no encontrado."]);
                        exit();
                    }

                    // CORRECCIÓN: Obtener los libros actuales del grupo ANTES de la comprobación
                    $librosActualesDelGrupo = $grupo->getLibros(); // Asume que esto devuelve un array de arrays con 'id_libro'
                    $libroYaEnGrupo = false;

                    if (is_array($librosActualesDelGrupo)) {
                        foreach ($librosActualesDelGrupo as $libroEnGrupo) {
                            // Comprobar si $libroEnGrupo es un array y tiene la clave 'id_libro'
                            if (is_array($libroEnGrupo) && isset($libroEnGrupo['id_libro']) && $libroEnGrupo['id_libro'] == $idLibro) {
                                $libroYaEnGrupo = true;
                            }
                        }
                    }

                    if ($libroYaEnGrupo) {
                        http_response_code(200); // OK, pero con mensaje informativo
                        echo json_encode(['mensaje' => "El libro '" . $libro->getTitulo() . "' ya está en el grupo."]);
                    } else {
                        if ($grupo->agregarLibro($idLibro)) {
                            http_response_code(200); // OK
                            echo json_encode(['mensaje' => "Libro '" . $libro->getTitulo() . "' añadido correctamente al grupo '" . $grupo->getNomGrupo() . "'."]);
                        } else {
                            http_response_code(500);
                            echo json_encode(['error' => 'Error al añadir el libro al grupo en la base de datos.']);
                        }
                    }
                    exit(); // Importante para peticiones AJAX
                    break;

                default:
                    $_SESSION['mensajeError'] = "Acción desconocida o no válida.";
                    // Para default, la redirección general al final del script se encargará.
                    break;
            }
        } catch (Exception $e) {
            error_log("Error en controladorInfoGrupo.php: " . $e->getMessage()); // Loguear el error
            if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
                http_response_code(500);
                echo json_encode(['error' => "Error en el servidor: " . $e->getMessage()]);
                exit();
            }
            $_SESSION['mensajeError'] = "Se produjo un error inesperado. Inténtalo de nuevo más tarde.";
        }
    } else {
        $_SESSION['mensajeError'] = "No se especificó ninguna acción válida.";
    }
} else {
    // Si no es una petición POST con 'accion', podría ser un acceso directo o incorrecto.
    // Podrías simplemente redirigir o mostrar un error genérico si es necesario.
    // La redirección general al final del script se encargará si no hay acción POST.
    // Si se accede a este controlador sin 'accion' POST, es probable que sea un error de flujo.
     $_SESSION['mensajeError'] = "Acceso no permitido o solicitud incorrecta.";
}

// Redirige si la acción no era AJAX o si falló antes de la salida JSON
header("Location: " . $redirectUrl);
exit();