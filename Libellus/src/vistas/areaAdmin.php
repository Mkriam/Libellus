<?php
// Vista de administración para el área de administrador de Libellus

require_once '../modelo/Autor.php';
require_once '../modelo/Libro.php';
require_once '../modelo/Genero.php';
require_once '../modelo/Usuario.php';
require_once '../controlador/validaciones.php';

session_start();

// Solo usuarios administradores pueden acceder
if (!isset($_SESSION['usuario']) || $_SESSION['administrador'] !== 1) {
    $_SESSION['$mensajeError'] = "Por favor, inicie sesión.";
    header("Location: login.php");
    exit();
}

// Obtener foto de perfil y datos del usuario
$fotoUsu = $_SESSION['fotoUsu'];
$nomUsuario = $_SESSION['usuario'];
$usuario = Usuario::verUsuarioPorNom($nomUsuario);

// Comprobar si el usuario existe realmente en la BD
if (!$usuario) {
    $_SESSION['mensajeError'] = "Usuario no válido. Debes iniciar sesión.";
    header("Location: login.php");
    exit();
}

// Obtiene los datos necesarios para los formularios
$listaAutores = [];
$listaGeneros = [];
$listaLibros = [];
try {
    $listaAutores = Autor::listarAutores();
    $listaGeneros = Genero::listarGeneros();
    $listaLibros = Libro::listarLibros();
} catch (Exception $e) {
    $errorCarga = "Error al cargar datos necesarios: " . $e->getMessage();
}

// Obtiene mensajes de éxito o error de la sesión
$mensajeExito = $_SESSION['mensajeExito'] ?? null;
$mensajeError = $_SESSION['mensajeError'] ?? null;
unset($_SESSION['mensajeExito'], $_SESSION['mensajeError']);

?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Libellus - Administración</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="./css/areaAdmin.css">
    <link rel="stylesheet" href="./css/header.css">

    <script>
        // Muestra solo el formulario seleccionado y oculta los demás
        function mostrarFormulario(idFormulario) {
            const formularios = document.querySelectorAll('.formu');
            formularios.forEach(form => {
                form.style.display = 'none';
            });

            const formularioSeleccionado = document.getElementById(idFormulario);
            if (formularioSeleccionado) {
                formularioSeleccionado.style.display = 'block';
            }
        }
    </script>
</head>

<body>
    <header>
        <div class="logo">
            <img src="../img/logo.png" alt="">
        </div>
        <div class="titulo">
            <a href="areaAdmin.php">Libellus</a>
        </div>
        <div class="dropdown areaUsu">
            <a href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                <img src="<?php echo validarCadena($fotoUsu); ?>" alt="Foto de perfil" class="dropdown-toggle">
            </a>
            <ul class="dropdown-menu dropdown-menu-end">
                <li><a class="dropdown-item" href="perfil.php">Mi Perfil</a></li>
                <li>
                    <hr class="dropdown-divider">
                </li>
                <li><a class="dropdown-item" href="../controlador/cerrar_sesion.php">Cerrar sesión</a></li>
            </ul>
        </div>
    </header>

    <main>
        <h1>PANTALLA DE ADMINISTRACIÓN</h1>

        <!-- Botones para mostrar los distintos formularios de gestión -->
        <div class="botones">
            <!-- Cada botón muestra un formulario diferente usando JS -->
            <button onclick="mostrarFormulario('formNuevoAutor')">Añadir Nuevo Autor</button>
            <button onclick="mostrarFormulario('formNuevoLibro')">Añadir Nuevo Libro</button>
            <button onclick="mostrarFormulario('formBorrarAutor')">Borrar Autor</button>
            <button onclick="mostrarFormulario('formBorrarLibro')">Borrar Libro</button>
        </div>

        <!-- Mensajes de éxito o error tras acciones -->
        <?php if ($mensajeExito) { ?> 
            <div class="mensajeExito"><?php echo validarCadena($mensajeExito); ?></div> 
        <?php } ?>
        <?php if ($mensajeError) { ?> 
            <div class="mensajeError"><?php echo validarCadena($mensajeError); ?></div> 
        <?php } ?>
        <?php if (!isset($errorCarga)) { ?>

            <div>
                <!-- Formulario para añadir un nuevo autor -->
                <div id="formNuevoAutor" class="formu" style="display: none;">
                    <div>
                        <h2>Añadir Nuevo Autor</h2>
                    </div>
                    <form action="../controlador/controladorAdmin.php" method="POST">
                        <div>
                            <label for="nomAutor">Nombre del Autor:</label>
                            <input type="text" id="nomAutor" name="nomAutor" required maxlength="100">
                        </div>
                        <button type="submit" name="accion" value="nuevoAutor">Guardar Autor</button>
                    </form>
                </div>

                <!-- Formulario para añadir un nuevo libro -->
                <div id="formNuevoLibro" class="formu" style="display: none;">
                    <div>
                        <h2>Añadir Nuevo Libro</h2>
                    </div>
                    <form action="../controlador/controladorAdmin.php" method="POST">
                        <div>
                            <label for="titulo">Título:</label>
                            <input type="text" id="titulo" name="titulo" required maxlength="200">
                        </div>
                        <div>
                            <label for="portada">URL Portada:</label>
                            <input type="url" id="portada" name="portada" placeholder="https://ejemplo.com/portada.jpg">
                        </div>
                        <div>
                            <label for="sinopsis">Sinopsis:</label>
                            <textarea id="sinopsis" name="sinopsis" required maxlength="400"></textarea>
                        </div>
                        <div>
                            <label for="fecPubli">Fecha Publicación:</label>
                            <input type="date" id="fecPubli" required name="fecPubli">
                        </div>
                        <div>
                            <label for="urlComprar">URL Compra:</label>
                            <input type="url" id="urlComprar" name="urlComprar" placeholder="https://tienda.com/libro">
                        </div>

                        <!-- Selección de autores para el libro -->
                        <div class="opcionesLibro">
                            <h3>Autores:</h3>
                            <?php if (!empty($listaAutores)) { ?>
                                <!-- Se listan todos los autores disponibles como checkboxes -->
                                <?php foreach ($listaAutores as $autor) { ?>
                                    <label>
                                        <!-- El value es el ID del autor, útil para el backend -->
                                        <input type="checkbox" name="autores[]" value="<?php echo $autor->getIdAutor(); ?>">
                                        <?php echo validarCadena($autor->getNomAutor() ?? 'Nombre no disponible'); ?>
                                    </label>
                                <?php } ?>
                            <?php } else { ?>
                                <!-- Si no hay autores, se avisa al usuario -->
                                <p>No hay autores disponibles. Añade autores primero.</p>
                            <?php } ?>
                        </div>

                        <!-- Selección de géneros para el libro -->
                        <div class="opcionesLibro">
                            <h3>Géneros:</h3>
                            <?php if (!empty($listaGeneros)) { ?>
                                <!-- Se listan todos los géneros disponibles como checkboxes -->
                                <?php foreach ($listaGeneros as $genero) { ?>
                                    <label>
                                        <!-- El value es el ID del género, útil para el backend -->
                                        <input type="checkbox" name="generos[]" value="<?php echo $genero->getIdGenero(); ?>">
                                        <?php echo validarCadena($genero->getNomGenero() ?? 'Género no disponible'); ?>
                                    </label>
                                <?php } ?>
                            <?php } else { ?>
                                <!-- Si no hay géneros, se avisa al usuario -->
                                <p>No hay géneros disponibles.</p>
                            <?php } ?>
                        </div>

                        <!-- Botón para guardar el libro nuevo -->
                        <button type="submit" name="accion" value="nuevoLibro">Guardar Libro</button>
                    </form>
                </div>

                <!-- Formulario para borrar un autor -->
                <div id="formBorrarAutor" class="formu" style="display: none;">
                    <div>
                        <h2>Borrar Autor</h2>
                    </div>
                    <form action="../controlador/controladorAdmin.php" method="POST" onsubmit="return confirm('¿Estás seguro de que quieres borrar este autor? Esta acción no se puede deshacer.');">
                        <div>
                            <label for="idAutorBorrar">Selecciona Autor a Borrar:</label>
                            <select id="idAutorBorrar" name="idAutorBorrar" required>
                                <option value="">-- Selecciona un autor --</option>
                                <?php foreach ($listaAutores as $autor) { ?>
                                    <option value="<?php echo $autor->getIdAutor(); ?>">
                                        <?php echo validarCadena($autor->getNomAutor() ?? 'Nombre no disponible'); ?> (ID: <?php echo $autor->getIdAutor(); ?>)
                                    </option>
                                <?php } ?>
                            </select>
                        </div>
                        <!-- Botón para borrar el autor seleccionado -->
                        <button type="submit" name="accion" value="borrarAutor">Borrar Autor Seleccionado</button>
                    </form>
                </div>

                <!-- Formulario para borrar un libro -->
                <div id="formBorrarLibro" class="formu" style="display: none;">
                    <div>
                        <h2>Borrar Libro</h2>
                    </div>
                    <form action="../controlador/controladorAdmin.php" method="POST" onsubmit="return confirm('¿Estás seguro de que quieres borrar este libro? Esta acción no se puede deshacer.');">
                        <div>
                            <label for="idLibroBorrar">Selecciona Libro a Borrar:</label>
                            <select id="idLibroBorrar" name="idLibroBorrar" required>
                                <option value="">-- Selecciona un libro --</option>
                                <?php foreach ($listaLibros as $libro) { ?>
                                    <option value="<?php echo $libro->getIdLibro(); ?>">
                                        <?php echo validarCadena($libro->getTitulo() ?? 'Título no disponible'); ?> (ID: <?php echo $libro->getIdLibro(); ?>)
                                    </option>
                                <?php } ?>
                            </select>
                        </div>
                        <!-- Botón para borrar el libro seleccionado -->
                        <button type="submit" name="accion" value="borrarLibro">Borrar Libro Seleccionado</button>
                    </form>
                </div>

            </div>
        <?php } ?>
    </main>

    <!-- Bootstrap JS para componentes interactivos del header -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>

</html>