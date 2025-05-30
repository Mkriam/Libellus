<?php
require_once __DIR__ . '/../modelo/Genero.php';
require_once __DIR__ . '/../controlador/validaciones.php';

echo "==== INICIO DE PRUEBAS DE GÉNERO ====\n";

// Prueba 1: Constructor con nombre válido
try {
    $genero = new Genero("Género Prueba 1");
    echo "Constructor con nombre válido OK\n";
} catch (Exception $e) {
    echo "Constructor con nombre válido falló: " . $e->getMessage() . "\n";
}

// Prueba 2: Constructor con nombre inválido
try {
    $genero = new Genero("");
    echo "Constructor con nombre inválido no lanzó excepción\n";
} catch (Exception $e) {
    echo "Constructor con nombre inválido lanzó excepción\n";
}

// Prueba 3: listarGeneros
$generos = Genero::listarGeneros();
if (is_array($generos) && count($generos) > 0) {
    echo "listarGeneros OK (total: " . count($generos) . ")\n";
} else {
    echo "listarGeneros falló\n";
}

// Prueba 4: verGenero con ID válido
$idGenero = $generos[count($generos) - 1]->getIdGenero();
$generoEncontrado = Genero::verGenero($idGenero);
if ($generoEncontrado && $generoEncontrado->getIdGenero() === $idGenero) {
    echo "verGenero OK\n";
} else {
    echo "verGenero falló\n";
}

echo "==== FIN DE PRUEBAS ====\n";
