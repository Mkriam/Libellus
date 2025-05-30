<?php

require_once '../modelo/Usuario.php';
require_once '../modelo/Grupo.php';
require_once '../controlador/validaciones.php';

session_start();

// Verifica que el usuario haya iniciado sesión
if (!isset($_SESSION['usuario'])) {
    $_SESSION['mensajeError'] = "Acceso denegado. Debes iniciar sesión."; // Corregido: quitado el '$' extra
    header("Location: ../vistas/login.php");
    exit();
}

$nomUsuario = $_SESSION['usuario'];
$datosUsu = Usuario::verUsuarioPorNom($nomUsuario);

if (!$datosUsu) {
    $_SESSION['mensajeError'] = "Usuario no válido. Debes iniciar sesión.";
    header("Location: ../vistas/login.php");
    exit();
}

if (filter_has_var(INPUT_POST, "accion") && filter_has_var(INPUT_POST, "id_grupo")) {
    $accion = validarCadena(filter_input(INPUT_POST, 'accion'));
    $id_grupo = validarEnteroPositivo(filter_input(INPUT_POST, 'id_grupo'));

    if (!$id_grupo) {
        $_SESSION['mensajeError'] = "ID de grupo inválido.";
        header("Location: ../vistas/areaUsuario.php");
        exit();
    }

    $grupo = Grupo::obtenerGrupo($id_grupo);

    if (!$grupo) {
        $_SESSION['mensajeError'] = "Grupo no encontrado.";
        header("Location: ../vistas/areaUsuario.php");
        exit();
    }

    $nombreGrupo = $grupo->getNomGrupo(); // Usado para redirección

    if ($grupo->getIdLider() !== $datosUsu->getNomUsu()) {
        $_SESSION['mensajeError'] = "No tienes permisos para administrar este grupo.";
        header("Location: ../vistas/infoGrupo.php?grupo=" . urlencode($nombreGrupo)); // urlencode para el nombre del grupo
        exit();
    }

    try {
        switch ($accion) {
            case 'editarInfoGrupo':
                $nuevoNomGrupo = filter_input(INPUT_POST, 'nom_grupo');
                $imgGrupoNueva = filter_input(INPUT_POST, 'img_grupo');
                $descripcionNueva = filter_input(INPUT_POST, 'descripcion_grupo');
                $erroresSalida = [];

                $nomGrupoVal = validarCadena($nuevoNomGrupo, 1, 100);
                if (!$nomGrupoVal) {
                    $erroresSalida[] = "El nombre del grupo no es válido (1-100 caracteres).";
                } else {
                    $nombreGrupoExiste = Grupo::obtenerGrupoPorNombre($nomGrupoVal);
                    if ($nombreGrupoExiste && $nombreGrupoExiste->getIdGrupo() != $id_grupo) {
                        $erroresSalida[] = "El nombre de grupo '" . $nomGrupoVal . "' ya lo usa otro grupo.";
                    }
                }

                $imgGrupo = null; // Por defecto
                if (!empty(trim($imgGrupoNueva))) {
                    if (!validarUrl($imgGrupoNueva)) { // validarUrl debería devolver la URL validada o false
                        $erroresSalida[] = "La URL de la foto no es válida.";
                    } else {
                        $imgGrupo = $imgGrupoNueva; // Usar la URL validada si es necesario, o la original si validarUrl solo retorna true/false
                    }
                }

                $descripcionVal = validarCadena($descripcionNueva, 0, 200);
                if ($descripcionNueva !== '' && $descripcionNueva !== null && $descripcionVal === false) {
                    $erroresSalida[] = "La descripción no es válida (máximo 200 caracteres).";
                }
                // Asignar descripción validada o la original si está vacía/null y la validación no la marcó como error por contenido inválido
                $descripcionFinal = ($descripcionVal === false && ($descripcionNueva === '' || $descripcionNueva === null)) ? $descripcionNueva : $descripcionVal;


                if (empty($erroresSalida)) {
                    $grupo->setNomGrupo($nomGrupoVal);
                    $grupo->setImgGrupo($imgGrupo); // Usar la variable que puede ser null
                    $grupo->setDescripcion($descripcionFinal);

                    if ($grupo->actualizarGrupo()) {
                        $_SESSION['gruposUsu'] = $datosUsu->getGrupos();
                        $_SESSION['mensajeExito'] = "Información del grupo actualizada.";
                        // El nombre del grupo podría haber cambiado, así que usar $nomGrupoVal para la redirección
                        header("Location: ../vistas/adminGrupo.php?grupo=" . urlencode($nomGrupoVal));
                        exit();
                    } else {
                        $_SESSION['mensajeError'] = "Error al actualizar información del grupo.";
                    }
                } else {
                    $_SESSION['mensajeError'] = "No se pudo actualizar. Errores: " . implode(" - ", $erroresSalida);
                }
                break;

            case 'eliminarMiembro':
                $nomMiembro = validarUsu(filter_input(INPUT_POST, 'nomMiembro')); // validarUsu en lugar de validarCadena
                if ($nomMiembro && $nomMiembro !== $grupo->getIdLider()) {
                    if ($grupo->eliminarMiembro($nomMiembro)) {
                        // $_SESSION['gruposUsu'] no se actualiza aquí generalmente, solo los miembros del grupo
                        $_SESSION['mensajeExito'] = "Miembro '" . $nomMiembro . "' eliminado.";
                    } else {
                        $_SESSION['mensajeError'] = "No se pudo eliminar al miembro.";
                    }
                } elseif ($nomMiembro === $grupo->getIdLider()) {
                    $_SESSION['mensajeError'] = "No puedes eliminar al líder del grupo.";
                } else {
                    $_SESSION['mensajeError'] = "Nombre de miembro inválido.";
                }
                break;

            case 'agregarMiembro':
                $nomUsuVal = validarUsu(filter_input(INPUT_POST, 'nomUsu'));
                if (!$nomUsuVal) { // Comprobar si el nombre de usuario es válido después de la validación
                    $_SESSION['mensajeError'] = "Nombre de usuario para agregar no es válido.";
                } else {
                    $usuNuevo = Usuario::verUsuarioPorNom($nomUsuVal);

                    if ($usuNuevo && !$usuNuevo->esAdministrador()) {
                        $miembrosDelGrupoActual = $grupo->getMiembros(); // Renombrar para claridad
                        $yaMiembro = false;
                        foreach ($miembrosDelGrupoActual as $miembroExistente) {
                            if (strtolower($miembroExistente['nom_usu']) === strtolower($nomUsuVal)) {
                                $yaMiembro = true;
                            }
                        }

                        if (!$yaMiembro) {
                            if ($grupo->agregarMiembro($nomUsuVal)) {
                                $_SESSION['mensajeExito'] = "Usuario '" . $nomUsuVal . "' agregado al grupo.";
                            } else {
                                $_SESSION['mensajeError'] = "No se pudo agregar al miembro (error de BD o lógico).";
                            }
                        } else {
                            $_SESSION['mensajeError'] = "El usuario '" . $nomUsuVal . "' ya pertenece al grupo.";
                        }
                    } else if (!$usuNuevo) {
                        $_SESSION['mensajeError'] = "Usuario '" . $nomUsuVal . "' no encontrado.";
                    } else if ($usuNuevo->esAdministrador()) {
                        $_SESSION['mensajeError'] = "Los administradores no pueden ser añadidos a grupos.";
                    }
                }

                break;

            case 'eliminarGrupo':
                $errorValido = false;
                $claveIntroducida = filter_input(INPUT_POST, 'claveConfirmGrupo');
                $claveEliminar = validarContr($claveIntroducida);

                $claveGrupoHash = $grupo->getClaveGrupoHash();

                if (!empty($claveGrupoHash)) {
                    // El grupo SÍ tiene contraseña (es privado).
                    if (empty($claveEliminar)) { 
                        $_SESSION['mensajeError'] = "Debes introducir la contraseña del grupo para confirmar su eliminación.";
                        $errorValido = true;
                    } else if (!password_verify((string)$claveEliminar, $claveGrupoHash)) { 
                        $_SESSION['mensajeError'] = "La contraseña del grupo es incorrecta. No se ha eliminado el grupo.";
                        $errorValido = true;
                    }
                }

                if (!$errorValido) {
                    // Si no hubo errores de validación de contraseña, intentar eliminar el grupo.
                    if (Grupo::eliminarGrupo($grupo->getIdGrupo())) {
                        // Actualizar la lista de grupos del usuario
                        $_SESSION['gruposUsu'] = $datosUsu->getGrupos();
                        $_SESSION['mensajeExito'] = "Grupo '" . validarCadena($grupo->getNomGrupo()) . "' eliminado correctamente.";
                        header("Location: ../vistas/areaUsuario.php");
                        exit();
                    } else {
                        $_SESSION['mensajeError'] = "Error al eliminar el grupo. Puede que ya no exista o haya ocurrido un problema en el servidor.";
                    }
                }

                break;

            case 'eliminarLibroGrupo':
                $idLibro = validarEnteroPositivo(filter_input(INPUT_POST, 'id_libro'));
                if ($idLibro && $grupo->eliminarLibro($idLibro)) {
                    $_SESSION['mensajeExito'] = "Libro eliminado del grupo correctamente.";
                } else {
                    $_SESSION['mensajeError'] = "No se pudo eliminar el libro del grupo.";
                }
                break;

            default:
                $_SESSION['mensajeError'] = "Acción desconocida para la administración del grupo.";
                break;
        }
    } catch (Exception $e) {
        error_log("Error en controladorGrupo.php: " . $e->getMessage());
        $_SESSION['mensajeError'] = "Error procesando la solicitud: " . $e->getMessage();
    }
    header("Location: ../vistas/adminGrupo.php?grupo=" . urlencode($nombreGrupo));
    exit();
} else {
    $_SESSION['mensajeError'] = "Solicitud no válida o faltan parámetros.";
    header("Location: ../vistas/areaUsuario.php");
    exit();
}
