<?php
// Vista de detalle de grupo para Libellus 
// Muestra los libros del grupo y permite navegar a la info del grupo 

require_once '../modelo/Usuario.php';
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

$grupo = null;
$librosDelGrupo = [];
$errorCarga = null;
$nombreGrupo = null; // Para el enlace al infoGrupo y botón volver

// Recupera mensajes de éxito o error de la sesión 
$mensajeExito = $_SESSION['mensajeExito'] ?? null;
$mensajeError = $_SESSION['mensajeError'] ?? null;
unset($_SESSION['mensajeExito'], $_SESSION['mensajeError']);

// Obtiene el grupo a mostrar según el parámetro GET 
if (filter_has_var(INPUT_GET, "grupo")) {
    $nombreGrupo = validarCadena(filter_input(INPUT_GET, 'grupo'));

    if ($nombreGrupo) {
        $grupo = Grupo::obtenerGrupoPorNombre($nombreGrupo);
        if ($grupo) {
            $librosDelGrupo = $grupo->getLibros(); // Esto ya devuelve un array de arrays asociativos
        } else {
            $errorCarga = "El grupo con nombre '" . validarCadena($nombreGrupo) . "' no fue encontrado.";
        }
    } else {
        $errorCarga = "No se especificó un nombre de grupo válido.";
    }
} else {
    $errorCarga = "No se especificó el grupo a mostrar.";
}

// Extraer el libro destacado (el más reciente) si hay libros
$libroDestacado = null;
if (!empty($librosDelGrupo)) {
    $libroDestacado = array_shift($librosDelGrupo);
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $grupo ? 'Grupo: ' . validarCadena($grupo->getNomGrupo()) : 'Grupo no encontrado'; ?> - Libellus</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="./css/areaGrupo.css">
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

    <main class="containerPrincipalGrupo">
        <?php if ($mensajeExito) { ?>
            <div class="mensajeExito"><?php echo validarCadena($mensajeExito); ?></div>
        <?php } ?>
        <?php if ($mensajeError) { ?>
            <div class="mensajeError"><?php echo validarCadena($mensajeError); ?></div>
        <?php } ?>
        <?php if (isset($errorCarga)) { ?>
            <div class="mensajeError"><?php echo validarCadena($errorCarga); ?></div>
            <div class="accionesPagina"> <a href="areaUsuario.php" class="botonAccion botonVolver">Volver a Mis Grupos</a> </div>
        <?php } else if ($grupo) { ?>
            <div class="grupoEncabezadoArea">
                <img src="<?php echo validarCadena($grupo->getImgGrupo() ?? '../img/grupo.png'); ?>"
                    alt="Imagen de <?php echo validarCadena($grupo->getNomGrupo()); ?>"
                    class="grupoImagen">
                <div class="grupoTituloEnlace">
                    <h1><?php echo validarCadena($grupo->getNomGrupo()); ?></h1>
                </div>
            </div>
            <!-- Botones: Más Info y Volver a Mis Grupo -->
            <div class="accionesGrupoBotones">
                <a href="infoGrupo.php?grupo=<?php echo urlencode($grupo->getNomGrupo()); ?>" class="botonAccion botonMasInfo">Más Info y Miembros</a>
                <a href="areaUsuario.php" class="botonAccion botonVolver">Volver a Mis Grupos</a>
            </div>

            <?php if (!empty($libroDestacado)) { ?>
                <section class="libroDestacadoSeccion">
                    <h2>Libro Actual</h2>
                    <article class="libroDestacado">
                        <div class="libroPortadaContenedor">
                            <img src="<?php echo validarUrl($libroDestacado['portada'] ?? '../img/portadaPorDefecto.png'); ?>"
                                 alt="Portada de <?php echo validarCadena($libroDestacado['titulo']); ?>"
                                 class="libroPortadaDestacada">
                        </div>
                        <div class="libroInfo">
                            <h3><?php echo validarCadena($libroDestacado['titulo']); ?></h3>
                            <p class="autores">
                                <strong>Autor/es:</strong> <?php echo validarCadena($libroDestacado['autores'] ?? 'No disponible'); ?>
                            </p>
                            <p class="generos">
                                <strong>Género/s:</strong> <?php echo validarCadena($libroDestacado['generos'] ?? 'No disponible'); ?>
                            </p>
                            <p class="fechaAnadido">
                                Añadido el: <?php echo !empty($libroDestacado['fecha']) ? date("d/m/Y H:i", strtotime($libroDestacado['fecha'])) : 'Fecha desconocida'; ?>
                            </p>
                            <a href="areaLibro.php?libro=<?php echo validarEnteroPositivo($libroDestacado['id_libro']); ?>&origenGrupo=<?php echo validarCadena($grupo->getNomGrupo()); ?>" class="botonAccion verDetalles">Ver Detalles</a>
                        </div>
                    </article>
                </section>
            <?php } ?>

            <?php if (!empty($librosDelGrupo)) { ?>
                <section class="librosAnterioresSeccion">
                    <h2>Libros Anteriores</h2>
                    <div class="listaLibrosGrupo">
                        <?php foreach ($librosDelGrupo as $libroEnGrupo) { ?>
                            <article class="libroItem">
                                <a href="areaLibro.php?libro=<?php echo validarEnteroPositivo($libroEnGrupo['id_libro']); ?>&origenGrupo=<?php echo validarCadena($grupo->getNomGrupo()); ?>">
                                    <div class="libroPortadaContenedor">
                                        <img src="<?php echo validarcadena($libroEnGrupo['portada'] ?? '../img/portadaPorDefecto.png'); ?>"
                                             alt="Portada de <?php echo validarCadena($libroEnGrupo['titulo']); ?>"
                                             class="libroPortada">
                                    </div>
                                    <div class="libroInfo">
                                        <h4><?php echo validarCadena($libroEnGrupo['titulo']); ?></h4>
                                        <p class="fechaAnadido">
                                            Añadido el: <?php echo $libroEnGrupo['fecha'] ? date("d/m/Y H:i", strtotime($libroEnGrupo['fecha'])) : 'Fecha desconocida'; ?>
                                        </p>
                                    </div>
                                </a>
                            </article>
                        <?php } ?>
                    </div>
                </section>
            <?php } else { ?>
                <section class="librosDelGrupoSeccion">
                    <h2>Libros en este Grupo</h2>
                    <p class="noResultados">Este grupo aún no tiene libros archivados aún.</p>
                </section>
            <?php } ?>
        <?php } ?>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>