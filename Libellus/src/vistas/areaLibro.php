<?php
// Permite ver información. Si se accede desde la lista personal del usuario,
// también permite modificar estado/comentario y eliminar el libro de la lista.

require_once '../modelo/Usuario.php';
require_once '../modelo/Libro.php';
require_once '../modelo/Autor.php';
require_once '../modelo/Genero.php';
require_once '../modelo/Grupo.php';
require_once '../controlador/validaciones.php';

session_start();

// Solo usuarios logueados y no administradores pueden acceder
if (!isset($_SESSION['usuario']) || $_SESSION['administrador'] !== 0) {
    $_SESSION['mensajeError'] = "Por favor, inicie sesión.";
    header("Location: login.php");
    exit();
}

$nomUsuario = $_SESSION['usuario'];
$usuario = Usuario::verUsuarioPorNom($nomUsuario);
$fotoUsu = $_SESSION['fotoUsu'];

// Comprobar si el usuario existe realmente en la BD
if (!$usuario) {
    $_SESSION['mensajeError'] = "Usuario no válido. Debes iniciar sesión.";
    header("Location: login.php");
    exit();
}

$libro = null;
$datosLibroGuardado = null;
$errorCarga = null;
$idLibroActual = null;
$libroPermitido = false;
$mostrarOpiniones = true;
$grupoOrigen = false;

// Validar y obtener el ID del libro de la URL
$idLibroActualGet = validarEnteroPositivo(filter_input(INPUT_GET, 'libro'));
if (!$idLibroActualGet) {
    $errorCarga = "No se ha especificado un ID de libro válido.";
} else {
    $idLibroActual = $idLibroActualGet;
}

//  Acceso según el origen 

// Si viene de un grupo
if (filter_has_var(INPUT_GET, 'origenGrupo')) {
    $grupoOrigen = validarCadena(filter_input(INPUT_GET, 'origenGrupo'));
    if ($grupoOrigen) {
        $grupo = Grupo::obtenerGrupoPorNombre($grupoOrigen);
        if (!$grupo) {
            $_SESSION['mensajeError'] = "Grupo no encontrado.";
            header("Location: areaGrupo.php?grupo=" . urlencode($grupoOrigen));
            exit();
        }
        // Comprobar si el usuario es miembro del grupo
        $esMiembro = false;
        foreach ($grupo->getMiembros() as $miembro) {
            if (isset($miembro['nom_usu']) && $miembro['nom_usu'] === $nomUsuario) {
                $esMiembro = true;
            }
        }
        if (!$esMiembro) {
            $_SESSION['mensajeError'] = "No eres miembro de este grupo.";
            header("Location: areaUsuario.php");
            exit();
        }
        // Comprobar si el libro pertenece al grupo
        $libroEnGrupo = false;
        foreach ($grupo->getLibros() as $infoLibro) {
            if (isset($infoLibro['id_libro']) && $infoLibro['id_libro'] == $idLibroActual) {
                $libroEnGrupo = true;
                break;
            }
        }
        if (!$libroEnGrupo) {
            $_SESSION['mensajeError'] = "El libro no pertenece a este grupo.";
            header("Location: areaGrupo.php?grupo=" . urlencode($grupoOrigen));
            exit();
        }
        $libroPermitido = true;
        $mostrarOpiniones = false; // No mostrar opiniones si viene de grupo
    } else {
        // origenGrupo no válido, redirigir a área de usuario
        $_SESSION['mensajeError'] = "Grupo no válido.";
        header("Location: areaUsuario.php");
        exit();
    }
} else {
    // Si viene de la página de usuario, comprobar si el usuario tiene guardado el libro
    $librosGuardadosUsu = $usuario->getLibrosGuardados();
    $libroGuardadoEncontrado = false;
    if (is_array($librosGuardadosUsu)) {
        foreach ($librosGuardadosUsu as $infoLibro) {
            if (isset($infoLibro['id_libro']) && $infoLibro['id_libro'] == $idLibroActual) {
                $libroGuardadoEncontrado = true;
                $datosLibroGuardado = $infoLibro;
                break;
            }
        }
    }
    if ($libroGuardadoEncontrado) {
        $libroPermitido = true;
        $mostrarOpiniones = true;
    } else {
        // Si no lo tiene guardado y no accede desde grupo, redirige a área de usuario
        $_SESSION['mensajeError'] = "No tienes acceso a este libro desde tu lista personal.";
        header("Location: areaUsuario.php");
        exit();
    }
}

// Cargar datos del libro si corresponde
if ($idLibroActual && !$errorCarga) {
    $libro = Libro::verLibro($idLibroActual);
    if (!$libro) {
        $errorCarga = "Libro no encontrado o no se pudieron cargar sus datos (ID: " . $idLibroActual . ").";
        $libroPermitido = false;
    }
}

$mensajeExito = $_SESSION['mensajeExito'] ?? null;
$mensajeErrorSesion = $_SESSION['mensajeError'] ?? null;
unset($_SESSION['mensajeExito'], $_SESSION['mensajeError']);
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo ($libroPermitido && $libro) ? $libro->getTitulo() : 'Detalle de Libro'; ?> - Libellus</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="./css/header.css">
    <link rel="stylesheet" href="./css/areaLibro.css">
</head>

<body>
    <header>
        <div class="logo"><img src="../img/logo.png" alt="Logo Libellus"></div>
        <div class="titulo"><a href="areaUsuario.php">Libellus</a></div>
        <div class="dropdown areaUsu">
            <a href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                <img src="<?php echo validarCadena($fotoUsu); ?>" alt="Foto de perfil" class="dropdown-toggle">
            </a>
            <ul class="dropdown-menu dropdown-menu-end">
                <li><a class="dropdown-item" href="perfil.php">Mi Perfil</a></li>
                <li><a class="dropdown-item" href="areaUsuario.php">Mi Lista</a></li>
                <li>
                    <hr class="dropdown-divider">
                </li>
                <li><a class="dropdown-item" href="../controlador/cerrar_sesion.php">Cerrar sesión</a></li>
            </ul>
        </div>
    </header>

    <main class="containerPrincipalLibro">
        <?php if ($mensajeExito) { ?>
            <div class="mensajeExito"><?php echo validarCadena($mensajeExito); ?></div>
        <?php } ?>
        <?php if ($mensajeErrorSesion) { ?>
            <div class="mensajeError"><?php echo validarCadena($mensajeErrorSesion); ?></div>
        <?php } ?>

        <?php if (isset($errorCarga)) { ?>
            <div class="mensajeError"><?php echo validarCadena($errorCarga); ?></div>
            <div class="accionesPagina">
                <?php if ($grupoOrigen) { ?>
                    <a href="areaGrupo.php?grupo=<?php echo urlencode($grupoOrigen); ?>" class="botonAccion botonVolver">Volver al Grupo</a>
                <?php } else { ?>
                    <a href="areaUsuario.php" class="botonAccion botonVolver">Volver a Mis Libros</a>
                <?php } ?>
            </div>
        <?php } else if ($libroPermitido && $libro instanceof Libro) {
            $portada = $libro->getPortada();
            $srcPortada = (!empty($portada)) ? validarUrl($portada) : '../img/portadaPorDefecto.png';
        ?>
            <div class="libroDetalleGrid">
                <div class="libroPortada">
                    <img src="<?php echo $srcPortada; ?>" alt="Portada de <?php echo validarCadena($libro->getTitulo()); ?>">
                </div>
                <div class="libroInfo">
                    <h1><?php echo validarCadena($libro->getTitulo()); ?></h1>
                    <?php
                    $autores = $libro->getAutores();
                    if (!empty($autores)) { ?>
                        <p class="autores"><strong>Escritor(es):</strong>
                            <?php
                            $nombresAutores = [];
                            foreach ($autores as $autor) {
                                if ($autor instanceof Autor) {
                                    $nombresAutores[] = validarCadena($autor->getNomAutor());
                                }
                            }
                            echo implode(', ', $nombresAutores);
                            ?>
                        </p>
                    <?php } else { ?>
                        <p class="autores"><strong>Escritor(es):</strong> No disponible</p>
                    <?php } ?>

                    <?php
                    $generos = $libro->getGeneros();
                    if (!empty($generos)) { ?>
                        <p class="generos"><strong>Género(s):</strong>
                            <?php
                            $nombresGeneros = [];
                            foreach ($generos as $genero) {
                                if ($genero instanceof Genero) {
                                    $nombresGeneros[] = validarCadena($genero->getNomGenero());
                                }
                            }
                            echo implode(', ', $nombresGeneros);
                            ?>
                        </p>
                    <?php } else { ?>
                        <p class="generos"><strong>Género(s):</strong> No disponible</p>
                    <?php } ?>

                    <p class="fecPublicacion"><strong>Fecha de Publicación:</strong> <?php echo $libro->getFecPublicacion() ? validarCadena(validarFecha($libro->getFecPublicacion())) : 'No disponible'; ?></p>
                    <div class="sinopsis">
                        <h3>Sinopsis:</h3>
                        <p><?php echo nl2br(validarCadena($libro->getSinopsis() ?: 'No disponible.')); ?></p>
                    </div>

                    <?php if ($libro->getUrlCompra()) { ?>
                        <div class="urlCompraAcciones">
                            <a href="<?php echo validarCadena(validarUrl($libro->getUrlCompra())); ?>" target="_blank" rel="noopener noreferrer" class="botonAccion botonPrimario">Comprar este libro</a>
                            <a href="<?php echo $grupoOrigen ? "areaGrupo.php?grupo=" . urlencode($grupoOrigen) : "areaUsuario.php"; ?>" class="botonAccion botonVolver">Volver <?php echo $grupoOrigen ? "al Grupo" : "a Mis Libros"; ?></a>
                        </div>
                    <?php } else { ?>
                        <div class="urlCompraAcciones">
                            <a href="<?php echo $grupoOrigen ? "areaGrupo.php?grupo=" . urlencode($grupoOrigen) : "areaUsuario.php"; ?>" class="botonAccion botonVolver">Volver <?php echo $grupoOrigen ? "al Grupo" : "a Mis Libros"; ?></a>
                        </div>
                    <?php } ?>
                </div>
            </div>

            <?php if ($mostrarOpiniones && $datosLibroGuardado) { ?>
                <hr class="separadorSecciones">
                <div class="opinionesSeccion">
                    <h2>Tu Progreso y Opinión</h2>
                    <p><strong>Estado actual:</strong> <?php echo validarCadena($datosLibroGuardado['estado'] ?: 'No especificado'); ?></p>
                    <div class="comentarioActual">
                        <strong>Comentario:</strong>
                        <div class="comentarioContenido">
                            <?php echo nl2br(validarCadena($datosLibroGuardado['comentario'] ?: '<em>No has añadido un comentario aún.</em>')); ?>
                        </div>
                    </div>

                    <div class="espacioOpiniones"></div>

                    <h3 class="subtituloSeccion">Modificar Estado y Comentario:</h3>
                    <form action="../controlador/controladorLibro.php" method="POST" class="formularioEdicionLibro">
                        <input type="hidden" name="accion" value="editarLibroUsu">
                        <input type="hidden" name="idLibro" value="<?php echo $libro->getIdLibro(); ?>">
                        <?php if ($grupoOrigen) { ?>
                            <input type="hidden" name="origenGrupo" value="<?php echo validarCadena($grupoOrigen); ?>">
                        <?php } ?>

                        <div class="campoFormulario">
                            <label for="estadoLibro" class="formLabel">Nuevo Estado:</label>
                            <select class="formSelect" name="estadoLibro" id="estadoLibro">
                                <option value="Pendiente" <?php echo (isset($datosLibroGuardado['estado']) && $datosLibroGuardado['estado'] == 'Pendiente') ? 'selected' : ''; ?>>Pendiente</option>
                                <option value="Leyendo" <?php echo (isset($datosLibroGuardado['estado']) && $datosLibroGuardado['estado'] == 'Leyendo') ? 'selected' : ''; ?>>Leyendo</option>
                                <option value="Completado" <?php echo (isset($datosLibroGuardado['estado']) && $datosLibroGuardado['estado'] == 'Completado') ? 'selected' : ''; ?>>Completado</option>
                            </select>
                        </div>
                        <div class="campoFormulario">
                            <label for="comentarioLibro" class="formLabel">Nuevo Comentario (máx. 500 caracteres):</label>
                            <textarea class="formControl" name="comentarioLibro" id="comentarioLibro" rows="4" maxlength="500"><?php echo validarCadena($datosLibroGuardado['comentario'] ?? ''); ?></textarea>
                        </div>
                        <button type="submit" class="botonAccion botonPrimario">Actualizar</button>
                    </form>
                    <div class="libroAcciones libroAccionesCentrada">
                        <form action="../controlador/controladorLibro.php" method="POST" onsubmit="return confirm('¿Estás seguro de que quieres eliminar este libro de tu lista?');" style="margin: 0 auto;">
                            <input type="hidden" name="accion" value="eliminarLibroGuardado">
                            <input type="hidden" name="idLibro" value="<?php echo $libro->getIdLibro(); ?>">
                            <?php if ($grupoOrigen) { ?>
                                <input type="hidden" name="origenGrupo" value="<?php echo validarCadena($grupoOrigen); ?>">
                            <?php } ?>
                            <button type="submit" class="botonAccion botonPeligro">Eliminar de Mis Libros</button>
                        </form>
                    </div>
                </div>
            <?php } elseif ($mostrarOpiniones && !$datosLibroGuardado && $libroPermitido) { ?>
                <hr class="separadorSecciones">
                <div class="opinionesSeccion">
                    <p><em>Guarda este libro en tu lista para añadir tu estado y opinión.</em></p>
                </div>
            <?php } ?>

        <?php } ?>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>