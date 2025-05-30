<?php
/**
 * Controlador de administración para el área de administrador.
 * 
 * Permite crear, modificar y eliminar autores y libros.
 * Valida los datos recibidos y muestra mensajes de éxito o error mediante variables de sesión.
 * 
 * @package Controlador
 * @author Miriam Rodríguez Antequera
 */

require_once '../modelo/Conexion.php';
require_once '../modelo/Autor.php';
require_once '../modelo/Libro.php';
require_once '../modelo/Genero.php';
require_once 'validaciones.php';

session_start();


// Procesa la acción recibida por POST
if (filter_has_var(INPUT_POST, "accion")) {
    $accion = validarCadena(filter_input(INPUT_POST, 'accion'));

    try {
        switch ($accion) {
            case 'nuevoAutor':
                // Crea un nuevo autor
                $nomAutor = validarCadena(filter_input(INPUT_POST, 'nomAutor'));

                $autor = new Autor($nomAutor);
                if ($autor->guardarAutor()) {
                    $_SESSION['mensajeExito'] = "El autor '$nomAutor' se ha guardado correctamente.";
                } else {
                    $_SESSION['mensajeError'] = "No se pudo guardar el autor (el nombre introducido puede que ya exista).";
                }
                break;

            case 'nuevoLibro':
                // Crea un nuevo libro con sus autores y géneros
                $titulo = validarCadena(filter_input(INPUT_POST, 'titulo'));
                $portada = validarUrl(filter_input(INPUT_POST, 'portada'));
                $sinopsis = validarCadena(filter_input(INPUT_POST, 'sinopsis'));
                $fecPubli = validarFecha(filter_input(INPUT_POST, 'fecPubli'));
                $urlComprar = validarUrl(filter_input(INPUT_POST, 'urlComprar'));

                // Recoge los IDs de autores y géneros seleccionados
                $autoresSelec = filter_input(INPUT_POST, 'autores', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY);
                $ids_autores = ($autoresSelec === null || $autoresSelec === false) ? [] : $autoresSelec;

                $generosSelec = filter_input(INPUT_POST, 'generos', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY);
                $idGeneros = ($generosSelec === null || $generosSelec === false) ? [] : $generosSelec;

                // Crea el libro
                $libro = new Libro($titulo, $sinopsis, $fecPubli, $portada, $urlComprar, null);

                // Asocia autores al libro
                foreach ($ids_autores as $id_autor) {
                    if (validarEnteroPositivo($id_autor)) {
                        $autorVal = Autor::verAutor((int)$id_autor);
                        
                        if ($autorVal){ // Solo agrega el autor si existe
                            $libro->agregarAutor($autorVal);
                        }
                    }
                }

                // Asocia géneros al libro
                foreach ($idGeneros as $idGenero) {
                    if (validarEnteroPositivo($idGenero)) {
                        $generoVal = Genero::verGenero((int)$idGenero);
                        if ($generoVal){ // Solo agrega el género si existe
                            $libro->agregarGenero($generoVal);
                        }
                    }
                }

                // Guarda el libro en la base de datos
                if ($libro->guardarLibro()) {
                    $_SESSION['mensajeExito'] = "El libro '$titulo' se ha guardado correctamente.";
                } else {
                    $_SESSION['mensajeError'] = "No se pudo guardar el libro.";
                }
                break;

            case 'borrarAutor':
                // Elimina un autor por su ID
                $idAutorBorrar = validarEnteroPositivo(filter_input(INPUT_POST, 'idAutorBorrar'));
                if ($idAutorBorrar) {
                    if (Autor::eliminarAutor((int)$idAutorBorrar)) {
                        $_SESSION['mensajeExito'] = "Autor eliminado correctamente.";
                    } else {
                        $_SESSION['mensajeError'] = "No se pudo eliminar el autor con ID: $idAutorBorrar.";
                    }
                } else {
                    $_SESSION['mensajeError'] = "ID de autor no válida.";
                }
                break;

            case 'borrarLibro':
                // Elimina un libro por su ID
                $idLibroBorrar = validarEnteroPositivo(filter_input(INPUT_POST, 'idLibroBorrar'));
                if ($idLibroBorrar) {
                    if (Libro::eliminarLibro((int)$idLibroBorrar)) {
                        $_SESSION['mensajeExito'] = "Libro eliminado correctamente.";
                    } else {
                        $_SESSION['mensajeError'] = "No se pudo eliminar el libro con ID: $idLibroBorrar.";
                    }
                } else {
                    $_SESSION['mensajeError'] = "ID de libro no válida.";
                }
                break;

            default:
                // Acción no reconocida
                $_SESSION['mensajeError'] = "Acción desconocida.";
                break;
        }
    } catch (Exception $e) {
        // Captura errores inesperados y los muestra al usuario
        $_SESSION['mensajeError'] = "Error: " . $e->getMessage();
    }
}

// Redirige siempre al área de administración tras la acción
header("Location: ../vistas/areaAdmin.php");    