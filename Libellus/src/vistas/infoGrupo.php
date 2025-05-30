<?php

// Vista de información de grupo
// Muestra los detalles de un grupo, sus libros y miembros.
// Permite al líder añadir libros y a cualquier usuario salir del grupo.

// Carga de modelos y utilidades necesarias
require_once '../modelo/Usuario.php';
require_once '../modelo/Grupo.php';
require_once '../modelo/Libro.php';
require_once '../controlador/validaciones.php';

session_start();

// Comprobación de sesión y permisos
// Solo usuarios logueados y no administradores pueden acceder
if (!isset($_SESSION['usuario']) || $_SESSION['administrador'] !== 0) {
    $_SESSION['mensajeError'] = "Por favor, inicie sesión.";
    header("Location: login.php");
    exit();
}

// Recupera el usuario actual y su foto de perfil
$nomUsuario = $_SESSION['usuario'];
$usuario = Usuario::verUsuarioPorNom($nomUsuario);
$fotoUsu = $_SESSION['fotoUsu'];

// Si el usuario no existe, lo redirige a login
if (!$usuario) {
    $_SESSION['mensajeError'] = "Usuario no válido. Debes iniciar sesión.";
    header("Location: login.php");
    exit();
}

// Inicialización de variables
$grupo = null; // Objeto grupo actual
$librosDelGrupo = []; // Libros del grupo
$nombreGrupo = null; // Nombre del grupo recibido por GET
$errorCarga = null; // Mensaje de error si ocurre algún problema
$esLider = false; // Indica si el usuario es líder del grupo
$idsLibrosGrupoActual = []; // IDs de libros del grupo para JS

// Si se recibe el nombre del grupo por GET, se busca el grupo
if (filter_has_var(INPUT_GET, "grupo")) {
    $nombreGrupo = validarCadena(filter_input(INPUT_GET, 'grupo'));

    if ($nombreGrupo) {
        $grupo = Grupo::obtenerGrupoPorNombre($nombreGrupo);
        if ($grupo) {
            // Obtiene los libros del grupo (array de arrays)
            $librosDelGrupo = $grupo->getLibros();
            if (is_array($librosDelGrupo)) {
                foreach ($librosDelGrupo as $libroDelGrupo) {
                    if (isset($libroDelGrupo['id_libro'])) {
                        // Guardamos los IDs como string para pasarlos a JS y evitar problemas de tipo
                        $idsLibrosGrupoActual[] = (string)$libroDelGrupo['id_libro'];
                    }
                }
            }

            // Comprobamos si el usuario es el líder del grupo
            if ($usuario && $grupo->getIdLider() === $usuario->getNomUsu()) {
                $esLider = true;
            }
        } else {
            // Si el grupo no existe, mostramos error
            $errorCarga = "El grupo con nombre '" . validarCadena($nombreGrupo) . "' no fue encontrado.";
        }
    } else {
        $errorCarga = "No se especificó un nombre de grupo válido.";
    }
} else {
    $errorCarga = "No se especificó el nombre del grupo a mostrar.";
}

// Mensajes de éxito y error desde la sesión
$mensajeExito = $_SESSION['mensajeExito'] ?? null;
$mensajeError = $_SESSION['mensajeError'] ?? null;
unset($_SESSION['mensajeExito'], $_SESSION['mensajeError']);
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $grupo ? 'Grupo: ' . validarCadena($grupo->getNomGrupo()) : 'Grupo no encontrado'; ?> - Libellus</title>
    <!-- Bootstrap y estilos propios -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="./css/infoGrupo.css">
    <link rel="stylesheet" href="./css/nuevoLibro.css">
    <link rel="stylesheet" href="./css/header.css">
</head>

<body>
    <!-- Cabecera con logo, título y menú de usuario -->
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
        <!-- Mensajes de éxito o error -->
        <?php if ($mensajeExito) { ?>
            <div class="mensajeExito"><?php echo validarCadena($mensajeExito); ?></div>
        <?php } ?>
        <?php if ($mensajeError) { ?>
            <div class="mensajeError"><?php echo validarCadena($mensajeError); ?></div>
        <?php } ?>
        <!-- Si hay error de carga, lo mostramos y damos opción de volver -->
        <?php if (isset($errorCarga)) { ?>
            <div class="mensajeError"><?php echo validarCadena($errorCarga); ?></div>
            <a href="areaUsuario.php" class="botonAccion">Volver a Mis Grupos</a>
        <?php } else if ($grupo) { ?>
            <!-- Información principal del grupo -->
            <div class="grupoHeader">
                <!-- Si no se encuentra una imagen de grupo, se pone una por defecto -->
                <img src="<?php echo validarCadena($grupo->getImgGrupo() ?? '../img/grupo.png'); ?>"
                    alt="Imagen de <?php echo validarCadena($grupo->getNomGrupo()); ?>"
                    class="grupoImagenGrande">
                <div class="grupoInfoDetallada">
                    <h1><?php echo validarCadena($grupo->getNomGrupo()); ?></h1>
                    <?php if ($grupo->getDescripcion()) { ?>
                        <!-- Muestra la descripción del grupo si existe -->
                        <p class="grupoDescripcion"><?php echo validarCadena($grupo->getDescripcion()); ?></p>
                    <?php } ?>
                    <!-- Muestra el nombre del líder del grupo -->
                    <p class="grupoLider">Líder: <?php echo validarCadena($grupo->getIdLider()); ?></p>
                </div>
                <!-- Botón de administración solo visible para el líder del grupo -->
                <?php if ($esLider) { ?>
                    <a href="adminGrupo.php?grupo=<?php echo urlencode($grupo->getNomGrupo()); ?>" class="botonAdminGrupo" title="Administrar Grupo">
                        <img src="../img/ajustes.png" alt="Administrar">
                    </a>
                <?php } ?>
            </div>

            <!-- Acciones disponibles según el rol del usuario -->
            <div class="accionesGrupoContainer">
                <?php if ($esLider) { ?>
                    <!-- El líder puede añadir libros al grupo -->
                    <button id="botonAbrirModalAnadirLibro" class="botonAccion botonAnadirLibro">Añadir Libro al Grupo</button>
                    <!-- Campo oculto para pasar el nombre del grupo al modal de añadir libro -->
                    <input type="hidden" id="hiddenNomGrupoParaModal" value="<?php echo validarCadena($grupo->getNomGrupo()); ?>">
                <?php } else { ?>
                    <!-- Los miembros pueden salir del grupo -->
                    <button onclick="confirmarSalidaGrupo('<?php echo validarCadena($grupo->getNomGrupo()); ?>')" class="botonAccion botonSalirGrupo">Salir del Grupo</button>
                <?php } ?>
                <!-- Botón para volver a la página anterior del grupo -->
                <a href="areaGrupo.php?grupo=<?php echo $nombreGrupo ?>" class="botonAccion botonVolver">Volver</a>
            </div>

            <!-- Listado de libros del grupo -->
            <section class="librosDelGrupoSeccion">
                <h2>Libros en este Grupo</h2>
                <?php if (!empty($librosDelGrupo)) { ?>
                    <div class="listaLibrosGrupo">
                        <?php foreach ($librosDelGrupo as $libroEnGrupo) { ?>
                            <article class="libroItemGrupo">
                                <!-- Enlace a la página de datos del libro -->
                                <a href="areaLibro.php?libro=<?php echo validarEnteroPositivo($libroEnGrupo['id_libro']); ?>&origenGrupo=<?php echo validarCadena($grupo->getNomGrupo()); ?>">
                                    <!-- Si no hay portada, se muestra una imagen por defecto -->
                                    <img src="<?php echo htmlspecialchars($libroEnGrupo['portada'] ?? '../img/portadaPorDefecto.png'); ?>"
                                        alt="Portada de <?php echo validarCadena($libroEnGrupo['titulo']); ?>"
                                        class="libroPortadaGrupo">
                                    <div class="libroInfoGrupo">
                                        <h3><?php echo validarCadena($libroEnGrupo['titulo']); ?></h3>
                                        <p class="fechaAnadido">
                                            <!-- Mostramos la fecha de añadido o un mensaje si no está disponible -->
                                            Añadido el: <?php echo !empty($libroEnGrupo['fecha']) ? date("d/m/Y H:i", strtotime($libroEnGrupo['fecha'])) : 'Fecha desconocida'; ?>
                                        </p>
                                        <?php if (!empty($libroEnGrupo['autores'])) { ?>
                                            <!-- Muestra los autores si existen -->
                                            <p class="autoresLibro">Por: <?php echo validarCadena($libroEnGrupo['autores']); ?></p>
                                        <?php } ?>
                                        <?php if (!empty($libroEnGrupo['generos'])) { ?>
                                            <!-- Muestra los géneros si existen -->
                                            <p class="generosLibro">Géneros: <?php echo validarCadena($libroEnGrupo['generos']); ?></p>
                                        <?php } ?>
                                    </div>
                                </a>
                            </article>
                        <?php } ?>
                    </div>
                <?php } else { ?>
                    <!-- Mensaje si el grupo aún no tiene libros -->
                    <p class="noResultados">Este grupo aún no tiene libros.</p>
                <?php } ?>
            </section>

            <!-- Listado de miembros del grupo -->
            <?php
                $miembros = $grupo->getMiembros();
                if (!empty($miembros)) { ?>
                <section class="miembrosDelGrupoSeccion">
                    <h2>Miembros</h2>
                    <ul class="listaMiembrosGrupo">
                        <?php foreach ($miembros as $miembro) { ?>
                            <li>
                                <!-- Si el usuario no tiene foto de perfil, se muestra un avatar por defecto -->
                                <img src="<?php echo validarCadena($miembro['foto_perfil'] ?? '../img/avatar.png'); ?>" alt="<?php echo validarCadena($miembro['nom_usu']); ?>">
                                <span><?php echo validarCadena($miembro['nom_usu']); ?></span>
                            </li>
                        <?php } ?>
                    </ul>
                </section>
            <?php
                }
            }
            ?>
    </main>

    <!-- Template para el modal de información de libro -->
    <template id="modalLibro">
        <article class="datosLibroModal" data-idlibro="" data-titulo="">
            <div class="portadaModal">
                <img class="portadaLibroModal" src="../img/portadaPorDefecto.png" alt="Portada">
            </div>
            <div class="infoLibroModal">
                <h3 class="libroTituloModal">Título no disponible</h3>
                <p><span class="autoresLibroModal">Autores no disponibles</span></p>
                <p><span class="generosLibroModal">Géneros no disponibles</span></p>
            </div>
        </article>
    </template>

    <!-- Scripts de Bootstrap y funcionalidades propias -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="./js/modalComun.js"></script>
    <script src="./js/nuevoLibro.js"></script>
    <script>
        /**
         * Muestra un cuadro de confirmación para salir del grupo.
         * Si el usuario acepta, se crea y envía un formulario POST al backend para procesar la salida.
         * @param {string} nombreGrupo - Nombre del grupo del que se quiere salir.
         */
        function confirmarSalidaGrupo(nombreGrupo) {
            if (confirm("¿Estás seguro de que quieres salir del grupo '" + nombreGrupo + "'? Esta acción no se puede deshacer.")) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = '../controlador/controladorInfoGrupo.php';

                // Campo oculto para la acción
                const accionInput = document.createElement('input');
                accionInput.type = 'hidden';
                accionInput.name = 'accion';
                accionInput.value = 'salirGrupo';
                form.appendChild(accionInput);

                // Campo oculto con el nombre del grupo
                const grupoInput = document.createElement('input');
                grupoInput.type = 'hidden';
                grupoInput.name = 'nomGrupo';
                grupoInput.value = nombreGrupo;
                form.appendChild(grupoInput);

                document.body.appendChild(form);
                form.submit();
            }
        }

        // Pasar los IDs de los libros ya en el grupo a JavaScript para que nuevoLibro.js los use.
        // Esto hace que el modal de añadir libro no muestre libros que ya estan guardados en el grupo.
        <?php if ($grupo instanceof Grupo && $esLider) { ?>
            const idsLibrosEnGrupoActual = <?php echo json_encode($idsLibrosGrupoActual); ?>;
        <?php } ?>
    </script>
</body>

</html>