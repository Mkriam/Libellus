<?php
require_once __DIR__ . '/../modelo/Grupo.php';
require_once __DIR__ . '/../controlador/validaciones.php';

echo "==== INICIO DE PRUEBAS DE GRUPO ====\n";

// Prueba 1: Constructor con datos válidos
try {
    $grupo = new Grupo("Grupo Prueba", "Descripción de prueba", "LiderTest");
    echo "Constructor con datos válidos OK\n";
} catch (Exception $e) {
    echo "Constructor con datos válidos falló: " . $e->getMessage() . "\n";
}

// Prueba 2: obtenerGrupo con ID válido
$grupoEncontrado = Grupo::obtenerGrupo(1); // ID 1 como ejemplo
if ($grupoEncontrado && $grupoEncontrado->getIdGrupo() === 1) {
    echo "obtenerGrupo OK\n";
} else {
    echo "obtenerGrupo falló\n";
}

// Prueba 3: agregarLibro
if ($grupoEncontrado && $grupoEncontrado->agregarLibro(1)) { // ID de ejemplo
    echo "agregarLibro OK\n";
} else {
    echo "agregarLibro falló\n";
}

// Prueba 4: obtener libros del grupo
$librosGrupo = $grupoEncontrado->getLibros();
if (is_array($librosGrupo) && count($librosGrupo) > 0) {
    echo "getLibros OK (total: " . count($librosGrupo) . ")\n";
} else {
    echo "getLibros falló\n";
}

// Prueba 5: eliminarLibro
if ($grupoEncontrado && $grupoEncontrado->eliminarLibro(1)) { // ID del libro agregado
    echo "eliminarLibro OK\n";
} else {
    echo "eliminarLibro falló\n";
}

// 🔹 **Eliminar "GrupoNuevoTest" al final**
$conexion = new Conexion("libellus", "db", "miriam", "libreria123");
$con = $conexion->getConexion()->prepare("SELECT id_grupo FROM GRUPO WHERE nom_grupo = 'GrupoNuevoTest' LIMIT 1");
$con->execute();
$idNuevoGrupo = $con->fetchColumn();
$conexion->cerrarConexion();

if ($idNuevoGrupo) {
    if (Grupo::eliminarGrupo($idNuevoGrupo)) {
        echo "GrupoNuevoTest eliminado correctamente.\n";
    } else {
        echo "No se pudo eliminar GrupoNuevoTest.\n";
    }
}

echo "==== FIN DE PRUEBAS ====\n";
