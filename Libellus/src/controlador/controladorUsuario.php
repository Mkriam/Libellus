<?php

/**
 * Controlador de acciones AJAX y de usuario.
 * 
 * Gestiona peticiones AJAX relacionadas con libros guardados, grupos y otras acciones del área de usuario.
 * Valida la sesión, procesa la acción recibida y responde en formato JSON.
 * 
 * @package Controlador
 * @author Miriam Rodríguez Antequera
 */

require_once '../modelo/Autor.php';
require_once '../modelo/Libro.php';
require_once '../modelo/Genero.php';
require_once '../modelo/Usuario.php';
require_once '../controlador/validaciones.php';
require_once '../modelo/Conexion.php';

session_start();

$nomUsuario = $_SESSION['usuario'];
$datosUsu = Usuario::verUsuarioPorNom($nomUsuario);

// Si no se pudo cargar el usuario, responde con error.
if (!$datosUsu) {
    $_SESSION['mensajeError'] = "Usuario no válido. Debes iniciar sesión.";
    header("Location: ../vistas/login.php");
    exit();
}

$accion = null;
if (filter_has_var(INPUT_POST, "accion")) {
    $accion = validarCadena(filter_input(INPUT_POST, 'accion'));
} elseif (filter_has_var(INPUT_GET, "accion")) {
    $accion = validarCadena(filter_input(INPUT_GET, 'accion'));
}

if ($accion) {
    header('Content-Type: application/json');
    try {
        switch ($accion) {
            case 'buscarLibrosAjax':
                // Busca los libros guardados del usuario, filtrando por término si se proporciona.
                $terminoBusqueda = validarCadena(filter_input(INPUT_GET, 'buscar'));
                $librosJson = [];

                $librosGuardadosInfo = $datosUsu->getLibrosGuardados();

                if ($librosGuardadosInfo === false) {
                    throw new Exception("Error al obtener la lista de libros guardados del usuario.");
                }
                if (is_array($librosGuardadosInfo)) {
                    foreach ($librosGuardadosInfo as $libroBase) {
                        // Carga el libro completo para obtener autores
                        $libroCompleto = Libro::verLibro($libroBase['id_libro']);
                        $coincide = false;

                        if (empty($terminoBusqueda)) {
                            $coincide = true;
                        } else {
                            $busqueda = mb_strtolower($terminoBusqueda);
                            // Busca por título
                            if (mb_strpos(mb_strtolower($libroBase['titulo']), $busqueda) !== false) {
                                $coincide = true;
                            } else if ($libroCompleto instanceof Libro) {
                                // Busca por autor
                                foreach ($libroCompleto->getAutores() as $autorObj) {
                                    if ($autorObj instanceof Autor && mb_strpos(mb_strtolower($autorObj->getNomAutor()), $busqueda) !== false) {
                                        $coincide = true;
                                        break;
                                    }
                                }
                            }
                        }

                        if ($coincide && $libroCompleto instanceof Libro) {
                            // Extrae nombres de autores y géneros
                            $nomAutores = [];
                            if ($libroCompleto->getAutores()) {
                                foreach ($libroCompleto->getAutores() as $autorObj) {
                                    if ($autorObj instanceof Autor) $nomAutores[] = validarCadena($autorObj->getNomAutor());
                                }
                            }
                            $nomGeneros = [];
                            if ($libroCompleto->getGeneros()) {
                                foreach ($libroCompleto->getGeneros() as $genero) {
                                    if ($genero instanceof Genero) $nomGeneros[] = validarCadena($genero->getNomGenero());
                                }
                            }
                            $librosJson[] = [
                                'id_libro' => $libroCompleto->getIdLibro(),
                                'titulo' => $libroCompleto->getTitulo(),
                                'portada' => $libroCompleto->getPortada() ?: '../img/portadaPorDefecto.png',
                                'autores_nombres' => $nomAutores,
                                'generos_nombres' => $nomGeneros,
                                'estado_guardado' => validarCadena($libroBase['estado'] ?? 'No especificado')
                            ];
                        }
                    }
                }
                echo json_encode(['libros' => $librosJson]);
                break;

            case 'librosAjax':
                // Devuelve todos los libros de la base de datos, con autores y géneros.
                error_log("Error: Acción 'librosAjax'");
                $todosLosLibros = [];
                $listaLibros = Libro::listarLibros();

                if ($listaLibros === false) {
                    throw new Exception("Error al obtener la lista completa de libros.");
                }
                if (is_array($listaLibros)) {
                    foreach ($listaLibros as $libro) {
                        if ($libro instanceof Libro) {
                            $nombresAutores = [];
                            if ($libro->getAutores()) {
                                foreach ($libro->getAutores() as $autorObj) {
                                    if ($autorObj instanceof Autor) $nombresAutores[] = validarCadena($autorObj->getNomAutor());
                                }
                            }
                            $nombresGeneros = [];
                            if ($libro->getGeneros()) {
                                foreach ($libro->getGeneros() as $genero) {
                                    if ($genero instanceof Genero) $nombresGeneros[] = validarCadena($genero->getNomGenero());
                                }
                            }
                            $todosLosLibros[] = [
                                'idLibro' => $libro->getIdLibro(),
                                'titulo' => validarCadena($libro->getTitulo()),
                                'portadaUrl' => validarCadena($libro->getPortada() ?: '../img/portadaPorDefecto.png'),
                                'autores' => $nombresAutores,
                                'generos' => $nombresGeneros,
                            ];
                        }
                    }
                }
                echo json_encode($todosLosLibros);
                break;

            case 'guardarLibroUsu':
                // Guarda un libro en la lista del usuario con estado 'Pendiente'
                $idLibro = validarEnteroPositivo(filter_input(INPUT_POST, 'id_libro'));
                if (!$idLibro) {
                    http_response_code(400);
                    echo json_encode(['error' => 'Falta el parámetro id_libro.']);
                } else {
                    // Siempre se guarda con estado 'Pendiente' desde aquí
                    $estadoFijoParaGuardar = 'Pendiente';
                    $comentarioFijoParaGuardar = null;

                    $guardadoExitoso = $datosUsu->guardarLibro($idLibro, $estadoFijoParaGuardar, $comentarioFijoParaGuardar);

                    if ($guardadoExitoso) {
                        http_response_code(200);
                        echo json_encode(['mensaje' => 'Libro guardado correctamente.']);
                    } else {
                        http_response_code(500);
                        echo json_encode(['error' => 'No se pudo añadir el libro.']);
                    }
                }
                break;

            case 'gruposAjax':
                // Devuelve todos los grupos para mostrar en el modal de grupos
                $gruposParaModal = Grupo::listarGruposParaModal();

                if ($gruposParaModal === false) {
                    http_response_code(500);
                    echo json_encode(['error' => 'Error al obtener la lista de grupos desde el servidor.']);
                } else {
                    echo json_encode($gruposParaModal);
                }
                break;

            case 'unirseGrupoAjax':
                // Permite al usuario unirse a un grupo, validando contraseña si es necesario
                $idGrupo = validarEnteroPositivo(filter_input(INPUT_POST, 'id_grupo'));
                if (!$idGrupo) {
                    http_response_code(400);
                    echo json_encode(['error' => 'Falta el ID del grupo o no es válido.']);
                    break;
                }

                $grupo = Grupo::obtenerGrupo($idGrupo);
                if (!$grupo) {
                    http_response_code(404);
                    echo json_encode(['error' => 'El grupo no existe.']);
                    break;
                }

                // Verificar si el usuario ya es miembro del grupo
                $miembros = $grupo->getMiembros();
                $esMiembro = false;
                foreach ($miembros as $miembro) {
                    if (isset($miembro['nom_usu']) && $miembro['nom_usu'] === $nomUsuario) {
                        $esMiembro = true;
                        break;
                    }
                }
                if ($esMiembro) {
                    http_response_code(409); // Conflict
                    echo json_encode(['error' => 'Ya eres miembro de este grupo.']);
                    break;
                }

                $claveIngresada = filter_input(INPUT_POST, 'clave_grupo');
                $hashClaveGrupo = $grupo->getClaveGrupoHash();

                // Si el grupo tiene contraseña, la verifica
                if ($hashClaveGrupo) {
                    if ($claveIngresada === null || !password_verify($claveIngresada, $hashClaveGrupo)) {
                        http_response_code(403);
                        echo json_encode(['error' => 'Contraseña incorrecta para el grupo.']);
                        break;
                    }
                }

                // Si todo esta bien, agregar miembro
                $exitoUnion = $grupo->agregarMiembro($nomUsuario);
                if ($exitoUnion) {
                    // Actualiza la sesión con los nuevos grupos del usuario
                    $_SESSION['gruposUsu'] = $datosUsu->getGrupos();
                    echo json_encode(['mensaje' => 'Te has unido al grupo exitosamente.', 'success' => true]);
                } else {
                    http_response_code(500);
                    echo json_encode(['error' => 'No se pudo unir al grupo. Inténtalo de nuevo.']);
                }
                break;

            case 'crearGrupoAjax':
                // Permite crear un nuevo grupo desde el modal
                $nomGrupo = validarCadena(filter_input(INPUT_POST, 'nombre_grupo'), 1, 100);
                $descripcion = validarCadena(filter_input(INPUT_POST, 'descripcion_grupo'), 1, 200);
                $claveGrupoInput = filter_input(INPUT_POST, 'clave_grupo');
                $imgGrupo = validarCadena(filter_input(INPUT_POST, 'img_grupo'));
                if (!$imgGrupo || !validarUrl($imgGrupo)) {
                    $imgGrupo = null;
                }

                // Validación de contraseña: si está vacía, grupo público; si no, debe ser válida
                if ($claveGrupoInput === null || $claveGrupoInput === '') {
                    $claveGrupo = null; // Grupo público
                } else {
                    $claveGrupo = validarContr($claveGrupoInput);
                    if ($claveGrupo === false) {
                        http_response_code(400);
                        echo json_encode(['error' => 'Contraseña no válida. Debe tener entre 8 y 200 caracteres, al menos una letra y un número.']);
                        break;
                    }
                }

                // Validación de campos obligatorios
                if (!$nomGrupo || !$descripcion) {
                    http_response_code(400);
                    echo json_encode(['error' => 'Nombre y descripción del grupo son obligatorios y deben ser válidos.']);
                    break;
                }

                try {
                    // Verificar si ya existe un grupo con ese nombre
                    if (Grupo::obtenerGrupoPorNombre($nomGrupo)) {
                        http_response_code(409); // Conflict
                        echo json_encode(['error' => "Ya existe un grupo con el nombre '$nomGrupo'."]);
                        break;
                    }

                    $nuevoGrupo = new Grupo($nomGrupo, $descripcion, $nomUsuario, $imgGrupo, $claveGrupo);
                    $guardadoExitoso = $nuevoGrupo->guardarGrupo();

                    if ($guardadoExitoso) {
                        $grupoRecienCreado = Grupo::obtenerGrupoPorNombre($nomGrupo);
                        if ($grupoRecienCreado) {
                            // El creador se une automáticamente como miembro
                            $grupoRecienCreado->agregarMiembro($nomUsuario);
                            $_SESSION['gruposUsu'] = $datosUsu->getGrupos();
                            echo json_encode(['mensaje' => "Grupo '$nomGrupo' creado exitosamente.", 'success' => true, 'id_grupo_nuevo' => $grupoRecienCreado->getIdGrupo()]);
                        } else {
                            http_response_code(500);
                            echo json_encode(['error' => 'Grupo creado pero no se pudo recuperar para añadir al líder.']);
                        }
                    } else {
                        http_response_code(500);
                        echo json_encode(['error' => 'No se pudo crear el grupo.']);
                    }
                } catch (Exception $e) {
                    http_response_code(400);
                    error_log("Excepción al crear grupo: " . $e->getMessage());
                    echo json_encode(['error' => 'Error al procesar la creación del grupo: ' . $e->getMessage()]);
                }

                break;

            default:
                // Acción no reconocida
                http_response_code(404);
                echo json_encode(['error' => "Acción '{$accion}' no reconocida o no implementada."]);
                break;
        }
    } catch (Exception $e) {
        // Captura general para cualquier excepción no manejada dentro del switch
        error_log("Error: Excepción general no capturada en el switch: " . $e->getMessage() . " en " . $e->getFile() . ":" . $e->getLine());
        http_response_code(500);
        echo json_encode(['error' => 'Ocurrió un error inesperado en el servidor. Revise los logs.']);
    }
} else {
    // Si la petición es AJAX, responde con JSON y 400
    if (
        !empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
        strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest'
    ) {
        header('Content-Type: application/json');
        http_response_code(400);
        echo json_encode(['error' => "No eligió ninguna opción válida."]);
    } else {
        $_SESSION['mensajeError'] = "No eligió ninguna opción válida.";
        header("Location: ../vistas/areaUsuario.php");
    }
    exit();
}
