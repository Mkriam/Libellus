<?php
/**
 * Funciones de validación para entradas de usuario.
 * 
 * Estas funciones ayudan a validar y limpiar datos recibidos en formularios,
 * como cadenas, emails, fechas, URLs y enteros positivos.
 * 
 * @package Controlador
 * @author Miriam Rodríguez Antequera
 */

/**
 * Limpia y valida una cadena.
 * Elimina espacios, etiquetas HTML y convierte caracteres especiales.
 * 
 * @param mixed $valor Valor a validar
 * @return string|false Cadena limpia o false si no es válida
 */
function validarCadena($valor)
{
    if (is_null($valor)) {
        $valor = false;
    } else {
        $valor = trim($valor); // Quita espacios al inicio y final
        $valor = strip_tags($valor); // Elimina etiquetas HTML
        $valor = htmlspecialchars($valor, ENT_QUOTES); // Convierte caracteres especiales

        if (empty($valor)) {
            $valor = false;
        }
    }
    return $valor;
}

/**
 * Valida un nombre de usuario.
 * Solo permite letras, números, guiones y guiones bajos (1-20 caracteres).
 * 
 * @param string $valor
 * @return string|false
 */
function validarUsu($valor)
{
    $valor = validarCadena($valor);
    $patron = "/^[A-Za-z0-9_-]{1,20}$/";
    if (!preg_match($patron, $valor)) {
        $valor = false;
    }
    return $valor;
}


/**
 * Valida una contraseña con seguridad básica:
 * - Mínimo 8 caracteres
 * - Al menos una letra
 * - Al menos un número
 * - Permite letras, números y símbolos básicos
 *
 * @param string $valor
 * @return string|false
 */
function validarContr($valor)
{
    $valor = validarCadena($valor);
    
    $patron = "/^(?=.*[A-Za-z])(?=.*\d)[A-Za-z\d!@#$%^&*()_+\-=\[\]{};':\"\\|,.<>\/?]{8,200}$/";
    if (!preg_match($patron, $valor)) {
        $valor = false;
    }
    return $valor;
}

/**
 * Valida un email.
 * 
 * @param string $valor
 * @return string|false
 */
function validarEmail($valor)
{
    $valor = validarCadena($valor);
    $patron = "/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/";
    if (!preg_match($patron, $valor)) {
        $valor = false;
    }
    return $valor;
}

/**
 * Valida una fecha en formato YYYY-MM-DD.
 * 
 * @param string $fecha
 * @return string|false
 */
function validarFecha($fecha)
{
    $fechaValidada = validarCadena($fecha);

    if ($fechaValidada) {
        $formato = 'Y-m-d';
        $fecha = DateTime::createFromFormat($formato, $fechaValidada);
        // Comprueba que la fecha sea válida y coincida con el formato
        if (!($fecha && $fecha->format($formato) == $fechaValidada)) {
            $fechaValidada = false;
        }
    }
    return $fechaValidada;
}

/**
 * Valida una URL
 * 
 * @param string $valor
 * @return string|false
 */
function validarUrl($valor)
{
    $valor = validarCadena($valor);
    $patron = "/^(https?:\/\/)[^\s\"']+$/i";
    if (!preg_match($patron, $valor)) {
        $valor = false;
    }
    return $valor;
}

/**
 * Valida que el valor sea un entero positivo.
 * 
 * @param mixed $valor
 * @return int|false
 */
function validarEnteroPositivo($valor)
{
    $valor = validarCadena($valor);
    $valor_int = filter_var($valor, FILTER_VALIDATE_INT);
    
    if ($valor_int <= 0) {
        $valor = false;
    } else {
        $valor = $valor_int;
    }
    return $valor;
}
