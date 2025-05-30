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
$ultimo = end($autores);
$id = $ultimo->getIdAutor();
$autorEncontrado = Autor::verAutor($id);
if ($autorEncontrado && $autorEncontrado->getIdAutor() === $id) {
    echo "verAutor OK\n";
} else {
    echo "verAutor falló\n";
}

// Prueba 8: eliminarAutor (autor recién creado)
$autorEliminar = new Autor("AutorEliminarTest");
if ($autorEliminar->guardarAutor()) {
    $autores = Autor::listarAutores();
    $ultimo = end($autores);
    $idEliminar = $ultimo->getIdAutor();
    if (Autor::eliminarAutor($idEliminar)) {
        echo "eliminarAutor OK\n";
    } else {
        echo "eliminarAutor falló\n";
    }
} else {
    echo "guardarAutor para prueba de eliminación falló\n";
}

// Prueba 9: eliminarAutor con ID inexistente
if (!Autor::eliminarAutor(999999)) {
    echo "eliminarAutor inexistente fue correctamente rechazado\n";
} else {
    echo "eliminarAutor inexistente no falló\n";
}

echo "==== FIN DE PRUEBAS ====\n";
