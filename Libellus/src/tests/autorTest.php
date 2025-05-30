<?php
require_once __DIR__ . '/../modelo/Autor.php';
require_once __DIR__ . '/../controlador/validaciones.php';

echo "==== INICIO DE PRUEBAS DE AUTOR ====\n";

// Prueba 1: Constructor con nombre válido
try {
    $autor = new Autor("Autor Prueba 1");
    echo "Constructor con nombre válido OK\n";
} catch (Exception $e) {
    echo "Constructor con nombre válido falló: " . $e->getMessage() . "\n";
}

// Prueba 2: Constructor con nombre vacío
try {
    $autor = new Autor("");
    echo "Constructor con nombre inválido no lanzó excepción\n";
} catch (Exception $e) {
    echo "Constructor con nombre inválido lanzó excepción\n";
}

// Prueba 3: setNomAutor válido
$autor = new Autor("Autor Prueba 2");
if ($autor->setNomAutor("Autor Modificado")) {
    echo "setNomAutor con nombre válido OK\n";
} else {
    echo "setNomAutor válido falló\n";
}

// Prueba 4: setNomAutor inválido
if (!$autor->setNomAutor("")) {
    echo "setNomAutor inválido fue rechazado\n";
} else {
    echo "setNomAutor inválido no fue rechazado\n";
}

// Prueba 5: guardarAutor (nuevo autor)
try {
    $autorNuevo = new Autor("AutorNuevoTest");
    if ($autorNuevo->guardarAutor()) {
        echo "guardarAutor nuevo autor OK\n";
    } else {
        echo "guardarAutor nuevo autor falló\n";
    }
} catch (Exception $e) {
    echo "Excepción en guardarAutor: " . $e->getMessage() . "\n";
}

// Prueba 6: listarAutores
$autores = Autor::listarAutores();
if (is_array($autores) && count($autores) > 0) {
    echo "listarAutores OK (total: " . count($autores) . ")\n";
} else {
    echo "listarAutores falló\n";
}

// Prueba 7: verAutor
$id = $autores[count($autores) - 1]->getIdAutor();
$autorEncontrado = Autor::verAutor($id);
if ($autorEncontrado && $autorEncontrado->getIdAutor() === $id) {
    echo "verAutor OK\n";
} else {
    echo "verAutor falló\n";
}

// Prueba 8: eliminarAutor (autor recién creado)
$autorEliminar = new Autor("AutorEliminarTest");
if ($autorEliminar->guardarAutor()) {
    // Obtener el último ID correctamente
    $conexion = new Conexion("libellus", "db", "miriam", "libreria123");
    $con = $conexion->getConexion()->query("SELECT id_autor FROM AUTOR ORDER BY id_autor DESC LIMIT 1");
    $idEliminar = $con->fetchColumn();
    $conexion->cerrarConexion();

    // Intentar eliminar al autor
    if (Autor::eliminarAutor($idEliminar)) {
        echo "eliminarAutor OK\n";
    } else {
        echo "eliminarAutor falló\n";
    }
} else {
    echo "guardarAutor para prueba de eliminación falló\n";
}

// 🔹 **Eliminar "AutorNuevoTest" al final**
$conexion = new Conexion("libellus", "db", "miriam", "libreria123");
$con = $conexion->getConexion()->prepare("SELECT id_autor FROM AUTOR WHERE nom_autor = 'AutorNuevoTest' LIMIT 1");
$con->execute();
$idNuevoAutor = $con->fetchColumn();
$conexion->cerrarConexion();

if ($idNuevoAutor) {
    if (Autor::eliminarAutor($idNuevoAutor)) {
        echo "AutorNuevoTest eliminado correctamente.\n";
    } else {
        echo "No se pudo eliminar AutorNuevoTest.\n";
    }
}

// Prueba 9: eliminarAutor con ID inexistente
if (!Autor::eliminarAutor(999999)) {
    echo "eliminarAutor inexistente fue correctamente rechazado\n";
} else {
    echo "eliminarAutor inexistente no falló\n";
}

echo "==== FIN DE PRUEBAS ====\n";
