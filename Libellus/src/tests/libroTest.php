<?php
require_once __DIR__ . '/../modelo/Libro.php';
require_once __DIR__ . '/../controlador/validaciones.php';

echo "==== INICIO DE PRUEBAS DE LIBRO ====\n";

// Prueba 1: Constructor con datos válidos
try {
    $libro = new Libro("Libro Prueba", "Sinopsis de prueba", "2024-05-30");
    echo "Constructor con datos válidos OK\n";
} catch (Exception $e) {
    echo "Constructor con datos válidos falló: " . $e->getMessage() . "\n";
}

// Prueba 2: verLibro con ID válido
$libroEncontrado = Libro::verLibro(1); // ID 1 como ejemplo
if ($libroEncontrado && $libroEncontrado->getIdLibro() == 1) {
    echo "verLibro OK\n";
} else {
    echo "verLibro falló\n";
}

// Prueba 3: listarLibros
$libros = Libro::listarLibros();
if (is_array($libros) && count($libros) > 0) {
    echo "listarLibros OK (total: " . count($libros) . ")\n";
} else {
    echo "listarLibros falló\n";
}

// Prueba 4: guardarLibro (nuevo libro)
try {
    $libroNuevo = new Libro("LibroNuevoTest", "Sinopsis nueva", "2024-05-30");
    if ($libroNuevo->guardarLibro()) {
        echo "guardarLibro nuevo libro OK\n";
    } else {
        echo "guardarLibro nuevo libro falló\n";
    }
} catch (Exception $e) {
    echo "Excepción en guardarLibro: " . $e->getMessage() . "\n";
}

// Prueba 5: cargarAutores y cargarGeneros
if ($libroEncontrado) {
    $autores = $libroEncontrado->getAutores();
    $generos = $libroEncontrado->getGeneros();
    
    if (is_array($autores) && count($autores) > 0) {
        echo "cargarAutores OK (total: " . count($autores) . ")\n";
    } else {
        echo "cargarAutores falló\n";
    }

    if (is_array($generos) && count($generos) > 0) {
        echo "cargarGeneros OK (total: " . count($generos) . ")\n";
    } else {
        echo "cargarGeneros falló\n";
    }
}

// Prueba 6: eliminarLibro
if ($libroNuevo && $libroNuevo->getIdLibro()) {
    if (Libro::eliminarLibro($libroNuevo->getIdLibro())) {
        echo "eliminarLibro OK\n";
    } else {
        echo "eliminarLibro falló\n";
    }
}

// 🔹 **Eliminar "LibroNuevoTest" al final**
$conexion = new Conexion("libellus", "db", "miriam", "libreria123");
$con = $conexion->getConexion()->prepare("SELECT id_libro FROM LIBRO WHERE titulo = 'LibroNuevoTest' LIMIT 1");
$con->execute();
$idNuevoLibro = $con->fetchColumn();
$conexion->cerrarConexion();

if ($idNuevoLibro) {
    if (Libro::eliminarLibro($idNuevoLibro)) {
        echo "LibroNuevoTest eliminado correctamente.\n";
    } else {
        echo "No se pudo eliminar LibroNuevoTest.\n";
    }
}

echo "==== FIN DE PRUEBAS ====\n";
