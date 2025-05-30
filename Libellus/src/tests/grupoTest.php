<?php
require_once __DIR__ . '/../modelo/Grupo.php';
require_once __DIR__ . '/../modelo/Usuario.php';
require_once __DIR__ . '/../controlador/validaciones.php';

echo "==== INICIO DE PRUEBAS DE GRUPO ====\n";

// Prueba 1: Constructor con datos válidos
try {
    $lider = new Usuario("LiderTest", "lider@correo.com", "ClaveSegura123");
    if ($lider->guardarUsuario()) {
        echo "Usuario líder guardado OK\n";
    } else {
        echo "Error al guardar el usuario líder.\n";
    }

    $grupo = new Grupo("Grupo Prueba", "Descripción de prueba", "LiderTest");
    echo "Constructor con datos válidos OK\n";
} catch (Exception $e) {
    echo "Constructor con datos válidos falló: " . $e->getMessage() . "\n";
}

// Prueba 2: guardarGrupo (nuevo grupo)
try {
    if ($grupo->guardarGrupo()) {
        echo "guardarGrupo nuevo grupo OK\n";
    } else {
        echo "guardarGrupo nuevo grupo falló\n";
    }
} catch (Exception $e) {
    echo "Excepción en guardarGrupo: " . $e->getMessage() . "\n";
}

// Obtener ID del grupo recién guardado
$conexion = new Conexion("libellus", "db", "miriam", "libreria123");
$con = $conexion->getConexion()->prepare("SELECT id_grupo FROM GRUPO WHERE nom_grupo = 'Grupo Prueba' LIMIT 1");
$con->execute();
$idNuevoGrupo = $con->fetchColumn();
$conexion->cerrarConexion();

if ($idNuevoGrupo) {
    $grupo = Grupo::obtenerGrupo($idNuevoGrupo);

    // Prueba 3: agregarLibro
    if ($grupo && $grupo->agregarLibro(1)) { // ID de ejemplo
        echo "agregarLibro OK\n";
    } else {
        echo "agregarLibro falló\n";
    }

    // Prueba 4: obtener libros del grupo
    $librosGrupo = $grupo->getLibros();
    if (is_array($librosGrupo) && count($librosGrupo) > 0) {
        echo "getLibros OK (total: " . count($librosGrupo) . ")\n";
    } else {
        echo "getLibros falló\n";
    }

    // Prueba 5: eliminarLibro
    if ($grupo && $grupo->eliminarLibro(1)) { // ID del libro agregado
        echo "eliminarLibro OK\n";
    } else {
        echo "eliminarLibro falló\n";
    }

    // Prueba 6: eliminar grupo
    if (Grupo::eliminarGrupo($idNuevoGrupo)) {
        echo "Grupo Prueba eliminado correctamente.\n";
    } else {
        echo "No se pudo eliminar Grupo Prueba.\n";
    }
}

// Reabrir conexión y eliminar usuario "LiderTest"
$conexion = new Conexion("libellus", "db", "miriam", "libreria123");
$con = $conexion->getConexion()->prepare("DELETE FROM USUARIO WHERE nom_usu = 'LiderTest'");
$con->execute();
$conexion->cerrarConexion();

echo "==== FIN DE PRUEBAS ====\n";
