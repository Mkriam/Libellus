<?php

require_once 'Conexion.php';
require_once __DIR__ . '/../controlador/validaciones.php';
require_once 'Libro.php';

/**
 * Clase Genero
 * 
 * Representa los posibles géneros de los libros.
 * Permite crear, listar y consultar géneros.
 */
class Genero {
    /**
     * @var int|null ID del género
     */
    private $idGenero;

    /**
     * @var string Nombre del género
     */
    private $nomGenero;

    /**
     * Constructor de la clase Genero.
     * 
     * @param string $nomGenero Nombre del género
     * @param int|null $idGenero ID del género (opcional)
     * @throws Exception Si el nombre no es válido
     */
    public function __construct($nomGenero, ?int $idGenero = null) {
        // Validar el nombre del género antes de asignar
        $nombreValidado = validarCadena($nomGenero);
        if ($nombreValidado === false) {
            throw new Exception("Nombre de género no válido.");
        }
        $this->idGenero = $idGenero;
        $this->nomGenero = $nombreValidado;
    }

    // Getters

    /**
     * Obtiene el ID del género.
     * @return int|null
     */
    public function getIdGenero(){
        return $this->idGenero;
    }

    /**
     * Obtiene el nombre del género.
     * @return string
     */
    public function getNomGenero(){
        return $this->nomGenero;
    }

    // Métodos estáticos

    /**
     * Lista todos los géneros de la base de datos.
     * @return Genero[] Array de objetos Genero
     */
    public static function listarGeneros(){
        $generos = [];
        try {
            $conexion = new Conexion("libellus", "db", "miriam", "libreria123");
            // Consulta todos los géneros ordenados alfabéticamente
            $con = $conexion->getConexion()->prepare("SELECT id_genero, nom_genero FROM GENERO ORDER BY nom_genero ASC");
            $con->execute();
            $datosGeneros = $con->fetchAll(PDO::FETCH_ASSOC);
            foreach ($datosGeneros as $dato) {
                // Crea un objeto Genero por cada registro
                $generos[] = new Genero($dato['nom_genero'], (int)$dato['id_genero']); 
            }
            $conexion->cerrarConexion();
        } catch (PDOException $e) {
            die("Error al listar los géneros: " . $e->getMessage());
        }
        return $generos;
    }

    /**
     * Busca un género por su ID.
     * @param int $idGenero
     * @return Genero|null El género encontrado o null si no existe
     */
    public static function verGenero($idGenero){
        $idGenero = validarEnteroPositivo($idGenero); // Validar el ID recibido
        $generoSalida = null;

        if ($idGenero) {
            try {
                $conexion = new Conexion("libellus", "db", "miriam", "libreria123");
                // Consulta el género por su ID
                $con = $conexion->getConexion()->prepare("SELECT * FROM GENERO WHERE id_genero = :id_genero");
                $con->bindParam(":id_genero", $idGenero);
                $con->execute();
                $datosGenero = $con->fetch(PDO::FETCH_ASSOC);
                $conexion->cerrarConexion();

                if ($datosGenero) {
                    // Si existe, crea el objeto Genero
                    $generoSalida = new Genero($datosGenero['nom_genero'], (int)$datosGenero['id_genero']);
                }
            } catch (PDOException $e) {
                die("Error al buscar el género: " . $e->getMessage());
            }
        }
        return $generoSalida;
    }
}
?>