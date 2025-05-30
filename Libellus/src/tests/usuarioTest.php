<?php
require_once __DIR__ . '/../modelo/Usuario.php';
require_once __DIR__ . '/../controlador/validaciones.php';

echo "==== INICIO DE PRUEBAS DE USUARIO ====\n";

// Prueba 1: Constructor con datos válidos
try {
    $usuario = new Usuario("UsuarioPrueba", "usuario@correo.com", "ClaveSegura123");
    echo "Constructor con datos válidos OK\n";
} catch (Exception $e) {
    echo "Constructor con datos válidos falló: " . $e->getMessage() . "\n";
}

// Prueba 2: verUsuarioPorNom con nombre válido
$usuarioEncontrado = Usuario::verUsuarioPorNom("UsuarioPrueba");
if ($usuarioEncontrado && $usuarioEncontrado->getNomUsu() === "UsuarioPrueba") {
    echo "verUsuarioPorNom OK\n";
} else {
    echo "verUsuarioPorNom falló\n";
}

// Prueba 3: verUsuarioPorEmail con email válido
$usuarioPorEmail = Usuario::verUsuarioPorEmail("usuario@correo.com");
if ($usuarioPorEmail && $usuarioPorEmail->getEmail() === "usuario@correo.com") {
    echo "verUsuarioPorEmail OK\n";
} else {
    echo "verUsuarioPorEmail falló\n";
}

// Prueba 4: guardarUsuario (nuevo usuario)
try {
    $usuarioNuevo = new Usuario("UsuarioNuevoTest", "nuevo@correo.com", "ClaveSegura123");
    if ($usuarioNuevo->guardarUsuario()) {
        echo "guardarUsuario nuevo usuario OK\n";
    } else {
        echo "guardarUsuario nuevo usuario falló\n";
    }
} catch (Exception $e) {
    echo "Excepción en guardarUsuario: " . $e->getMessage() . "\n";
}

// Prueba 5: actualizarDatos
$usuarioEncontrado->setEmail("actualizado@correo.com");
$usuarioEncontrado->setFotoPerfil("https://ejemplo.com/foto.jpg");
if ($usuarioEncontrado->actualizarDatos()) {
    echo "actualizarDatos OK\n";
} else {
    echo "actualizarDatos falló\n";
}

// Prueba 6: cambiarClave
if ($usuarioEncontrado->setClave("NuevaClaveSegura123") && $usuarioEncontrado->cambiarClave()) {
    echo "cambiarClave OK\n";
} else {
    echo "cambiarClave falló\n";
}

// Prueba 7: verificarLogin
$loginUsuario = Usuario::verificarLogin("actualizado@correo.com", "NuevaClaveSegura123");
if ($loginUsuario) {
    echo "verificarLogin OK\n";
} else {
    echo "verificarLogin falló\n";
}

// Prueba 8: eliminarUsuario
if ($usuarioNuevo && Usuario::eliminarUsuario("UsuarioNuevoTest")) {
    echo "eliminarUsuario OK\n";
} else {
    echo "eliminarUsuario falló\n";
}

// 🔹 **Eliminar "UsuarioNuevoTest" al final**
$conexion = new Conexion("libellus", "db", "miriam", "libreria123");
$con = $conexion->getConexion()->prepare("SELECT nom_usu FROM USUARIO WHERE nom_usu = 'UsuarioNuevoTest' LIMIT 1");
$con->execute();
$nomUsuarioNuevo = $con->fetchColumn();
$conexion->cerrarConexion();

if ($nomUsuarioNuevo) {
    if (Usuario::eliminarUsuario($nomUsuarioNuevo)) {
        echo "UsuarioNuevoTest eliminado correctamente.\n";
    } else {
        echo "No se pudo eliminar UsuarioNuevoTest.\n";
    }
}

echo "==== FIN DE PRUEBAS ====\n";
