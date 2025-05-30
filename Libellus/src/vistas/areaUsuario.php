<?php
// Vista principal del área de usuario de Libellus
// Muestra los libros guardados, los grupos del usuario y permite gestionar ambos

require_once '../modelo/Autor.php';
require_once '../modelo/Libro.php';
require_once '../modelo/Genero.php';
require_once '../modelo/Usuario.php';
require_once '../controlador/validaciones.php';
session_start();

// Solo usuarios logueados y no administradores pueden acceder
if (!isset($_SESSION['usuario']) || $_SESSION['administrador'] !== 0) {
    $_SESSION['mensajeError'] = "Por favor, inicie sesión.";
    header("Location: login.php");
    exit();
}

// Recupera datos de sesión y del usuario
$fotoUsu = $_SESSION['fotoUsu'];
$nomUsuario = $_SESSION['usuario'];
$usuario = Usuario::verUsuarioPorNom($nomUsuario);
$gruposUsuario = $_SESSION['gruposUsu'];

// Comprobar si el usuario existe realmente en la BD
if (!$usuario) {
    $_SESSION['mensajeError'] = "Usuario no válido. Debes iniciar sesión.";
    header("Location: login.php");
    exit();
}

// Carga los libros guardados del usuario
$librosGuardadosInfoInicial = $usuario->getLibrosGuardados();
$librosCompletosIniciales = [];
$idsLibrosGuardadosPorUsuarioInicial = [];

$errorCarga = null;

try {
    // Procesa los libros guardados para mostrar información completa
    if (is_array($librosGuardadosInfoInicial)) {
        foreach ($librosGuardadosInfoInicial as $libroGuardado) {
            if (isset($libroGuardado['id_libro'])) {
                $idsLibrosGuardadosPorUsuarioInicial[] = $libroGuardado['id_libro'];
                $infoLibro = Libro::verLibro($libroGuardado['id_libro']);
                if ($infoLibro instanceof Libro) {
                    $librosCompletosIniciales[] = [
                        'objeto' => $infoLibro,
                        'estadoGuardado' => $libroGuardado['estado'] ?? 'No especificado' // camelCase para consistencia en JS
                    ];
                }
            }
        }
    } else if (!$librosGuardadosInfoInicial) { // Corrección: era if y no elseif
        throw new Exception("Error al obtener los libros guardados del usuario.");
    }


    // Procesa los grupos del usuario para JS
    $idsGruposDelUsuarioActualPhp = [];
    if (!empty($gruposUsuario) && is_array($gruposUsuario)) {
        foreach ($gruposUsuario as $grupoInfo) {
            if (is_array($grupoInfo) && isset($grupoInfo['id_grupo'])) {
                $idsGruposDelUsuarioActualPhp[] = (string)$grupoInfo['id_grupo'];
            } else if (is_object($grupoInfo) && method_exists($grupoInfo, 'getIdGrupo')) {
                $idsGruposDelUsuarioActualPhp[] = (string)$grupoInfo->getIdGrupo();
            }
        }
    }
} catch (Exception $e) {
    $errorCarga = "Error al cargar datos necesarios para la página: " . validarCadena($e->getMessage());
    error_log("Error en areausuario.php carga inicial: " . $e->getMessage());
}

// Recupera mensajes de éxito o error de la sesión
$mensajeExito = $_SESSION['mensajeExito'] ?? null;
$mensajeError = $_SESSION['mensajeError'] ?? null;
unset($_SESSION['mensajeExito'], $_SESSION['mensajeError']);

?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Libellus - Mis Libros</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="./css/areaUsuario.css">
    <link rel="stylesheet" href="./css/nuevoLibro.css">
    <link rel="stylesheet" href="./css/nuevoGrupo.css">
    <link rel="stylesheet" href="./css/header.css">
</head>

<body>
    <header>
        <div class="logo">
            <img src="../img/logo.png" alt="Logo Libellus">
        </div>
        <div class="titulo">
            <a href="areaUsuario.php">Libellus</a>
        </div>
        <div class="dropdown areaUsu"> <a href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
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

    <div class="containerPrincipal"> <aside class="sidebarGrupos"> <h2>GRUPOS</h2>
            <button id="botonAbrirModalNuevoGrupo">Nuevo Grupo</button> <?php if (!empty($gruposUsuario)) { ?>
                <ul class="listaGrupos" id="listaGrupos"> <?php foreach ($gruposUsuario as $grupoUsu) { ?>
                        <li class="itemGrupo"> <a href="areaGrupo.php?grupo=<?php echo urlencode(validarCadena($grupoUsu['nom_grupo'])); ?>" class="enlaceGrupo"> <img src="<?php echo validarCadena($grupoUsu['img_grupo'] ?? '../img/grupo.png'); ?>" alt="Imagen de <?php echo validarCadena($grupoUsu['nom_grupo']); ?>" class="imgGrupo"> <span class="nombreGrupoTexto"><?php echo validarCadena($grupoUsu['nom_grupo']); ?></span> </a>
                        </li>
                    <?php } ?>
                </ul>
            <?php } else { ?>
                <p>No estás en ningún grupo aún.</p>
            <?php } ?>
        </aside>

        <main class="contenidoLibros"> <h1>Mis Libros Guardados</h1>

            <?php if ($mensajeExito) { ?> <div class="mensajeExito"><?php echo validarCadena($mensajeExito); ?></div> <?php } ?> <?php if ($mensajeError) { ?> <div class="mensajeError"><?php echo validarCadena($mensajeError); ?></div> <?php } ?> <?php if (isset($errorCarga)) { ?>
                <div class="mensajeError"><?php echo validarCadena($errorCarga); ?></div> <a href="areaUsuario.php" class="botonAccion">Volver a Mis Libros</a> <?php } else { ?>

            <div class="barraBusquedaContenedor"> <input type="text" id="campoBusqueda" placeholder="Buscar mis libros por título o autor..." class="campoBusqueda"> <button id="botonAbrirModalAnadirLibro" class="botonAccion">Añadir Libro</button> </div>

            <section class="listaLibros" id="listaLibrosResult"> <?php if (!empty($librosCompletosIniciales)) { ?>
                    <?php foreach ($librosCompletosIniciales as $itemInicial) { 
                        $libro = $itemInicial['objeto'];
                        $estadoUsuarioInicial = validarCadena($itemInicial['estadoGuardado']);

                        $autoresIni = [];
                        $autoresDelLibro = $libro->getAutores();
                        if (is_array($autoresDelLibro)) {
                            foreach ($autoresDelLibro as $autor) {
                                if ($autor instanceof Autor){
                                    $autoresIni[] = validarCadena($autor->getNomAutor());
                                }else{ 
                                    $autoresIni[] = 'Desconocido';
                                }
                            }
                        }

                        $generosIni = [];
                        $generosDelLibro = $libro->getGeneros();
                        if (is_array($generosDelLibro)) {
                            foreach ($generosDelLibro as $genero) {
                                if ($genero instanceof Genero){
                                    $generosIni[] = validarCadena($genero->getNomGenero());

                                }else{ 
                                    $generosIni[] = 'Desconocido';
                                }
                            }
                        }
                        $portada_url_inicial = $libro->getPortada() ? validarCadena($libro->getPortada()) : '../img/portadaPorDefecto.png';
                        ?>
                        <article class="datosLibro" data-idlibro="<?php echo validarEnteroPositivo($libro->getIdLibro()); ?>"> <div class="portadaLibro"> <img src="<?php echo $portada_url_inicial; ?>" alt="Portada de <?php echo validarCadena($libro->getTitulo()); ?>">
                            </div>
                            <div class="datosInfo"> <h2><?php echo validarCadena($libro->getTitulo()); ?></h2>
                                <p><strong>Autor(es):</strong> <?php echo !empty($autoresIni) ? implode(', ', $autoresIni) : 'No disponible'; ?></p>
                                <p><strong>Género(s):</strong> <?php echo !empty($generosIni) ? implode(', ', $generosIni) : 'No disponible'; ?></p>
                                <p><strong>Estado:</strong> <span class="estadoLibro"><?php echo $estadoUsuarioInicial; ?></span></p> </div>
                        </article>
                    <?php } ?>
                <?php } else if (!$errorCarga && empty($mensajeError) && empty($mensajeExito)) { ?>
                    <p class="noResultados">Aún no has guardado ningún libro. ¡Explora y añade tus favoritos!</p> <?php }; ?>
            </section>
            <?php } ?>
        </main>
    </div>

    <template id="libroTemplate"> <article class="datosLibro" data-idlibro=""> <div class="portadaLibro"> <img class="imgPortada" src="" alt="Portada de libro"> </div>
            <div class="datosInfo"> <h2 class="tituloLibro"></h2>   <p><strong>Autor(es):</strong> <span class="autoresLibro"></span></p> <p><strong>Género(s):</strong> <span class="generosLibro"></span></p> <p><strong>Estado:</strong> <span class="estadoLibro"></span></p>     </div>
        </article>
    </template>

    <template id="modalLibro"> <article class="datosLibroModal" data-idlibro="" data-titulo=""> <div class="portadaModal"> <img class="portadaLibroModal" src="" alt=""> </div>
            <div class="infoLibroModal"> <h3 class="libroTituloModal"></h3> <p><strong>Autor(es):</strong> <span class="autoresLibroModal"></span></p> <p><strong>Género(s):</strong> <span class="generosLibroModal"></span></p> </div>
        </article>
    </template>

    <template id="grupoModalTemplate"> <article class="grupoItemModal" data-idgrupo="" data-nombregrupo="" data-necesitaClave="false"> <div class="grupoImagenModal"> <img class="grupoImg" src="../img/grupo.png" alt="Imagen del grupo"> </div>
            <div class="grupoInfoModal"> <h3 class="grupoNombreModal"></h3> </div>
        </article>
    </template>

    <?php
    $idsGruposDelUsuarioActual = [];
    if (!empty($gruposUsuario)) {
        foreach ($gruposUsuario as $grupoInfo) {
            if (isset($grupoInfo['id_grupo'])) $idsGruposDelUsuarioActual[] = $grupoInfo['id_grupo'];
            elseif (is_object($grupoInfo) && method_exists($grupoInfo, 'getIdGrupo')) $idsGruposDelUsuarioActual[] = $grupoInfo->getIdGrupo();
        }
    }
    ?>
    <script>
        const idsLibrosGuardadosPorUsuarioGlobal = <?php echo json_encode($idsLibrosGuardadosPorUsuarioInicial); ?>;
        const idsGruposDelUsuarioActualGlobal = <?php echo json_encode($idsGruposDelUsuarioActualPhp); ?>;
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="./js/modalComun.js" defer></script>
    <script src="./js/nuevoLibro.js" defer></script>
    <script src="./js/nuevoGrupo.js" defer></script>
    <script src="./js/buscarLibro.js" defer></script>
    <script src="./js/selecLibro.js" defer></script>
</body>
</html>