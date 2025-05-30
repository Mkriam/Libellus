<?php
/**
 * Script para cerrar la sesión del usuario.
 * 
 * Destruye la sesión actual y redirige al inicio.
 * 
 * @package Controlador
 * @author Miriam Rodríguez Antequera
 */

session_start(); // Inicia la sesión si no está iniciada

session_destroy(); // Elimina todos los datos de la sesión

header("Location: ../index.html"); // Redirige al usuario a la página principal
exit();

