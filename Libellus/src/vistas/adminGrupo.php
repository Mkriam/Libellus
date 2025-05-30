<?php
// Vista de administración de grupo para el líder en Libellus

require_once '../modelo/Usuario.php';
require_once '../modelo/Grupo.php';
require_once '../controlador/validaciones.php';

// Inicia la sesión para acceder a variables de usuario
session_start();

// Si no hay usuario en sesión, redirige a login
if (!isset($_SESSION['usuario'])) {
    $_SESSION['mensajeError'] = "Sesión inválida. Por favor, inicie sesión.";
    header("Location: login.php");
    exit();
}

// Recupera datos del usuario desde la sesión
$fotoUsu = $_SESSION['fotoUsu'];
$nomUsuario = $_SESSION['usuario'];
$usuario = Usuario::verUsuarioPorNom($nomUsuario);

// Si el usuario no existe en la base de datos, lo mando al login
if (!$usuario) {
    $_SESSION['mensajeError'] = "Usuario no válido. Debes iniciar sesión.";
    header("Location: login.php");
    exit();
}

// Inicializa variables para el grupo y mensajes
$grupo = null;
$nomGrupo = null;
$errorCarga = null;
$miembrosGrupo = [];
$idGrupo = null;

// Si se recibe el nombre del grupo por GET, intenta cargarlo
if (filter_has_var(INPUT_GET, 'grupo')) {
    $nomGrupo = validarCadena(filter_input(INPUT_GET, 'grupo'));
    if ($nomGrupo) {
        $grupo = Grupo::obtenerGrupoPorNombre($nomGrupo);
        if (!$grupo) {
            $errorCarga = "Grupo no encontrado.";
        } else if ($grupo->getIdLider() !== $nomUsuario) {
            // Solo el líder puede administrar el grupo
            $errorCarga = "No tienes permiso para administrar este grupo.";
            $grupo = null;
        } else {
            $miembrosGrupo = $grupo->getMiembros();
            $idGrupo = $grupo->getIdGrupo();
        }
    } else {
        $errorCarga = "ID de grupo inválido.";
    }
} else {
    $errorCarga = "No se especificó un grupo para administrar.";
}

// Mensajes de éxito o error de la sesión (se eliminan después de usarse)
$mensajeExito = $_SESSION['mensajeExito'] ?? null;
$mensajeError = $_SESSION['mensajeError'] ?? null;
unset($_SESSION['mensajeExito'], $_SESSION['mensajeError']);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>
        <?php echo $grupo ? 'Administrar Grupo: ' . validarCadena($grupo->getNomGrupo()) : 'Error Administración'; ?> - Libellus
    </title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="./css/perfil.css">
    <link rel="stylesheet" href="./css/header.css">
    <link rel="stylesheet" href="./css/adminGrupo.css">
    <script>
        // Muestra solo el formulario de administración seleccionado y oculta los demás
        function mostrarFormularioAdminGrupo(idFormulario) {
            document.querySelectorAll('.formularioAdminOcultable').forEach(function(form) {
                form.style.display = 'none';
            });
            const formSeleccionado = document.getElementById(idFormulario);
            if (formSeleccionado) {
                formSeleccionado.style.display = 'block';
                formSeleccionado.scrollIntoView({
                    behavior: 'smooth',
                    block: 'nearest'
                });
            }
        }
    </script>
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
                <li><hr class="dropdown-divider"></li>
                <li><a class="dropdown-item" href="../controlador/cerrar_sesion.php">Cerrar sesión</a></li>
            </ul>
        </div>
    </header>

    <main>
        <h1>
            <?php echo $grupo ? 'Administrar Grupo: ' . validarCadena($grupo->getNomGrupo()) : 'Administración de Grupo'; ?>
        </h1>

        <!-- Mensaje de éxito si existe -->
        <?php if ($mensajeExito) { ?>
            <div class="mensajeExito"><?php echo validarCadena($mensajeExito); ?></div>
        <?php } ?>

        <!-- Mensaje de error si existe -->
        <?php if ($mensajeError) { ?>
            <div class="mensajeError"><?php echo validarCadena($mensajeError); ?></div>
        <?php } ?>

        <!-- Si hay error de carga, lo muestra y da opción de volver -->
        <?php if (isset($errorCarga)) { ?>
            <div class="mensajeError"><?php echo validarCadena($errorCarga); ?></div>
            <a href="areaUsuario.php" class="botonAccion">Volver a Mis Grupos</a>
        <?php } else if ($grupo) { ?>
            <div class="botonesAccionContainer">
                <button type="button" onclick="mostrarFormularioAdminGrupo('formEditarInfoGrupo')">Editar Información</button>
                <button type="button" onclick="mostrarFormularioAdminGrupo('formGestionarMiembros')">Gestionar Miembros</button>
                <button type="button" onclick="mostrarFormularioAdminGrupo('formAgregarMiembros')">Agregar Miembro</button>
                <button type="button" onclick="mostrarFormularioAdminGrupo('formEliminarLibroGrupo')" class="zonaPeligroBotonInicial">Eliminar Libro del Grupo</button>
                <button type="button" onclick="mostrarFormularioAdminGrupo('formEliminarGrupo')" class="zonaPeligroBotonInicial">Eliminar Grupo</button>
                <a href="infoGrupo.php?grupo=<?php echo urlencode(validarCadena($grupo->getNomGrupo())); ?>" class="botonAccion botonVolver">Volver al Grupo</a>
            </div>

            <!-- Formulario: Editar Información del Grupo -->
            <div id="formEditarInfoGrupo" class="formularioContenedor formularioAdminOcultable" style="display:none;">
                <h2>Editar Información del Grupo</h2>
                <form action="../controlador/controladorGrupo.php" method="POST">
                    <input type="hidden" name="id_grupo" value="<?php echo $idGrupo; ?>">
                    <div>
                        <label for="nom_grupo_edit">Nombre del Grupo:</label>
                        <input type="text" id="nom_grupo_edit" name="nom_grupo" value="<?php echo validarCadena($grupo->getNomGrupo()); ?>" required>
                    </div>
                    <div>
                        <label for="img_grupo_edit">URL de la Imagen del Grupo:</label>
                        <input type="url" id="img_grupo_edit" name="img_grupo" value="<?php echo validarCadena($grupo->getImgGrupo() ?? ''); ?>" placeholder="https://ejemplo.com/imagen.jpg">
                        <small>Deja en blanco para no cambiar o usa una URL válida.</small>
                    </div>
                    <div>
                        <label for="descripcion_grupo_edit">Descripción:</label>
                        <textarea id="descripcion_grupo_edit" name="descripcion_grupo" rows="4"><?php echo validarCadena($grupo->getDescripcion()); ?></textarea>
                    </div>
                    <button type="submit" name="accion" value="editarInfoGrupo" class="botonAccion">Guardar Cambios</button>
                </form>
            </div>

            <!-- Formulario: Gestionar Miembros -->
            <div id="formGestionarMiembros" class="formularioContenedor formularioAdminOcultable" style="display:none;">
                <h2>Gestionar Miembros</h2>
                <?php if (!empty($miembrosGrupo)) { ?>
                    <ul class="listaGestionMiembros">
                        <?php foreach ($miembrosGrupo as $miembro) { 
                            $nomMiembro = validarUsu($miembro['nom_usu']);
                        ?>
                            <li>
                                <img src="<?php echo validarCadena($miembro['foto_perfil'] ?? '../img/avatar.png'); ?>" alt="<?php echo $nomMiembro; ?>">
                                <span><?php echo $nomMiembro; ?></span>
                                <?php if ($nomMiembro !== $grupo->getIdLider()) { ?>
                                    <form action="../controlador/controladorGrupo.php" method="POST" style="display:inline;">
                                        <input type="hidden" name="id_grupo" value="<?php echo $idGrupo; ?>">
                                        <input type="hidden" name="nomMiembro" value="<?php echo $nomMiembro; ?>">
                                        <button type="submit" name="accion" value="eliminarMiembro" class="botonEliminarMiembro" onclick="return confirm('¿Seguro que quieres eliminar a este miembro del grupo?');">Eliminar</button>
                                    </form>
                                <?php } else { ?>
                                    <span class="labelLider">(Líder)</span>
                                <?php } ?>
                            </li>
                        <?php } ?>
                    </ul>
                <?php } else { ?>
                    <p>No hay otros miembros en este grupo además del líder.</p>
                <?php } ?>
            </div>

            <!-- Formulario: Agregar Nuevo Miembro -->
            <div id="formAgregarMiembros" class="formularioContenedor formularioAdminOcultable" style="display:none;">
                <h2>Agregar Nuevo Miembro</h2>
                <form action="../controlador/controladorGrupo.php" method="POST">
                    <input type="hidden" name="id_grupo" value="<?php echo $idGrupo; ?>">
                    <label for="nomUsuAdd">Nombre de usuario del nuevo miembro:</label>
                    <input type="text" id="nomUsuAdd" name="nomUsu" required>
                    <button type="submit" name="accion" value="agregarMiembro" class="botonAccion">Agregar Miembro</button>
                </form>
            </div>

            <!-- Formulario: Eliminar Grupo Permanentemente -->
            <div id="formEliminarGrupo" class="formularioContenedor formularioAdminOcultable zonaPeligro" style="display:none;">
                <h2>Eliminar Grupo Permanentemente</h2>
                <p>¡Esta acción es irreversible y eliminará el grupo para todos los miembros!</p>
                <form action="../controlador/controladorGrupo.php" method="POST" onsubmit="return confirm('¿Estás TOTALMENTE seguro de que quieres eliminar este grupo? ¡Esta acción es IRREVERSIBLE!');">
                    <input type="hidden" name="id_grupo" value="<?php echo $idGrupo; ?>">
                    <?php 
                    $claveGrupoHash = $grupo->getClaveGrupoHash();
                        if (!empty($claveGrupoHash)) { ?>
                            <label for="claveConfirmGrupo">Introduce tu contraseña para confirmar:</label>
                            <input type="password" id="claveConfirmGrupo" name="claveConfirmGrupo" placeholder="Contraseña actual"> 
                    <?php } ?>
                    <button type="submit" name="accion" value="eliminarGrupo" class="botonAccion">Eliminar Grupo Ahora</button>
                </form>
            </div>

            <!-- Formulario para eliminar un libro del grupo -->
            <div id="formEliminarLibroGrupo" class="formularioContenedor formularioAdminOcultable" style="display:none;">
                <h2>Eliminar Libro del Grupo</h2>
                <form action="../controlador/controladorGrupo.php" method="POST" onsubmit="return confirm('¿Seguro que quieres eliminar este libro del grupo?');">
                    <input type="hidden" name="id_grupo" value="<?php echo $idGrupo; ?>">
                    <label for="idLibroEliminar">Selecciona el libro a eliminar:</label>
                    <select id="idLibroEliminar" name="id_libro" required>
                        <option value="" disabled selected>Selecciona un libro</option>
                        <?php
                        // Si el grupo tiene método getLibros, muestra los libros para eliminar
                        if ($grupo && method_exists($grupo, 'getLibros')) {
                            $librosGrupo = $grupo->getLibros();
                            foreach ($librosGrupo as $libro) {
                                $titulo = isset($libro['titulo']) ? validarCadena($libro['titulo']) : 'Sin título';
                                $idLibro = isset($libro['id_libro']) ? (int)$libro['id_libro'] : '';
                                ?><option value= "<?php echo $idLibro; ?> "><?php echo $titulo; ?></option> <?php
                            }
                        }
                        ?>
                    </select>
                    <button type="submit" name="accion" value="eliminarLibroGrupo" class="botonAccion zonaPeligro">Eliminar Libro</button>
                </form>
            </div>
        <?php } ?>
    </main>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>