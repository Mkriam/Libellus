<?php

require_once 'Conexion.php';
require_once '../controlador/validaciones.php';

/**
 * Clase Autor
 * 
 * Representa un autor de libros.
 * Permite crear, consultar, listar, actualizar y eliminar autores.
 */
class Autor{

    /**
     * @var int|null ID del autor
     */
    private $idAutor;

    /**
     * @var string Nombre del autor
     */
    private $nomAutor;

    /**
     * Constructor de la clase Autor.
     * 
     * @param string $nomAutor Nombre del autor
     * @param int|null $idAutor ID del autor (opcional)
     * @throws Exception Si el nombre no es válido
     */
    public function __construct($nomAutor, ?int $idAutor = null)
    {
        $error = "";
        // Validar el nombre antes de asignar
        if (!validarCadena($nomAutor)) {
            $error = "El nombre del autor no es válido o está vacío.";
        }

        if (!empty($error)) {
            throw new Exception($error);
        }

        $this->idAutor = $idAutor;
        $this->nomAutor = validarCadena($nomAutor);
    }

    // Getters

    /**
     * Obtiene el ID del autor.
     * @return int|null
     */
    public function getIdAutor(){
        return $this->idAutor;
    }

    /**
     * Obtiene el nombre del autor.
     * @return string
     */
    public function getNomAutor(){
        return $this->nomAutor;
    }

    // Setters

    /**
     * Establece el nombre del autor.
     * @param string $nomAutor
     * @return bool True si se estableció correctamente, false en caso contrario
     */
    public function setNomAutor($nomAutor){
        $salida = false;
        $nombreValidado = validarCadena($nomAutor);
        if ($nombreValidado) {
            $this->nomAutor = $nombreValidado;
            $salida = true;
        }
        return $salida;
    }

    // Métodos estáticos

    /**
     * Busca un autor por su ID.
     * @param int $idAutor
     * @return Autor|null El autor encontrado o null si no existe
     */
    public static function verAutor($idAutor)
    {
        $idAutor = validarEnteroPositivo($idAutor); // Validar el ID recibido
        $autorSalida = null;

        if ($idAutor) {
            try {
                $conexion = new Conexion("libellus", "db", "miriam", "libreria123");
                // Consulta el autor por su ID
                $con = $conexion->getConexion()->prepare("SELECT * FROM AUTOR WHERE id_autor = :id_autor");
                $con->bindParam(":id_autor", $idAutor);
                $con->execute();
                $datosAutor = $con->fetch(PDO::FETCH_ASSOC);
                $conexion->cerrarConexion();

                if ($datosAutor) {
                    // Si existe, crea el objeto Autor
                    $autorSalida = new Autor($datosAutor['nom_autor'], (int)$datosAutor['id_autor']);
                }
            } catch (PDOException $e) {
                die("Error al buscar al autor: " . $e->getMessage());
            }
        }
        return $autorSalida;
    }

    /**
     * Lista todos los autores de la base de datos.
     * @return Autor[] Array de objetos Autor
     */
    public static function listarAutores()
    {
        $autores = [];
        try {
            $conexion = new Conexion("libellus", "db", "miriam", "libreria123");
            // Consulta todos los autores ordenados alfabéticamente
            $con = $conexion->getConexion()->prepare("SELECT * FROM AUTOR ORDER BY nom_autor ASC");
            $con->execute();
            $datosAutores = $con->fetchAll(PDO::FETCH_ASSOC);
            foreach ($datosAutores as $datosAutor) {
                // Crea un objeto Autor por cada registro
                $autores[] = new Autor($datosAutor['nom_autor'], (int)$datosAutor['id_autor']);
            }
            $conexion->cerrarConexion();
        } catch (PDOException $e) {
            die("Error al listar los autores: " . $e->getMessage());
        }
        return $autores;
    }

    // Métodos de instancia (CRUD)

    /**
     * Guarda o actualiza el autor en la base de datos.
     * Si el autor ya existe (por ID), lo actualiza; si no, lo inserta.
     * @return bool True si se guardó correctamente, false en caso contrario
     */
    public function guardarAutor(){
        $salida = false;
        try {
            $conexion = new Conexion("libellus", "db", "miriam", "libreria123");
            $con = $conexion->getConexion();

            // Verifica si ya existe un autor con ese nombre
            $busqueda = $con->prepare("SELECT * FROM AUTOR WHERE nom_autor = :nom_autor");
            $busqueda->bindParam(":nom_autor", $this->nomAutor);
            $busqueda->execute();
            $autorExistente = $busqueda->fetch(PDO::FETCH_ASSOC);

            if ($autorExistente) {
                throw new Exception("Ya existe un autor con ese nombre.");
            }

            // Si el autor existe por ID, actualiza; si no, inserta
            if (Autor::verAutor($this->idAutor)) {
                $con = $conexion->getConexion()->prepare("UPDATE AUTOR SET nom_autor = :nom_autor WHERE id_autor = :id_autor");
                $con->bindParam(":id_autor", $this->idAutor);
            } else {
                $con = $conexion->getConexion()->prepare("INSERT INTO AUTOR (nom_autor) VALUES (:nom_autor)");
            }
            $con->bindParam(":nom_autor", $this->nomAutor);
            $con->execute();
            $conexion->cerrarConexion();
            $salida=true;
        } catch (PDOException $e) {
            die("Error al guardar el autor: " . $e->getMessage());
        }
        return $salida;
    }

    /**
     * Elimina un autor por su ID.
     * Solo elimina si el autor no está asociado a ningún libro.
     * @param int $idAutor
     * @return bool True si se eliminó correctamente, false en caso contrario
     */
    public static function eliminarAutor($idAutor){
        $idAutor = validarCadena(filter_var($idAutor, FILTER_VALIDATE_INT));
        $salida = false;
        if ($idAutor && $idAutor > 0) {
            if (Autor::verAutor($idAutor)) {
                try {
                    $conexion = new Conexion("libellus", "db", "miriam", "libreria123");
                    $con = $conexion->getConexion();
                    $con->beginTransaction();

                    // Verifica si el autor está asociado a algún libro
                    $comprobacion = $con->prepare("SELECT COUNT(*) FROM ESCRIBE WHERE id_autor = :id_autor");
                    $comprobacion->bindParam(":id_autor", $idAutor);
                    $comprobacion->execute();
                    $numLibros = $comprobacion->fetchColumn();

                    if ($numLibros == 0) {
                        // Si no está asociado, elimina el autor
                        $borrarAutor=$con->prepare("DELETE FROM AUTOR WHERE id_autor = :id_autor");
                        $borrarAutor->bindParam(":id_autor", $idAutor);
                        $borrarAutor->execute();
                        $conexion->cerrarConexion();

                        if ($borrarAutor->rowCount() > 0) {
                            $con->commit();
                            $salida = true;
                            error_log("Autor ID {$idAutor} eliminado correctamente.");
                        } else {
                             $con->rollBack();
                             error_log("Error al eliminar autor ID {$idAutor}: No se encontró o no se pudo eliminar.");
                        }
                    } else {
                        // Si está asociado a libros, no elimina y muestra error
                        error_log("No se pudo eliminar al autor con ID {$idAutor}: El autor está asociado a {$numLibros} libro(s).");
                    }
                } catch (PDOException $e) {
                    die("Error al eliminar el autor: " . $e->getMessage());
                }
            }
        }
        return $salida;
    }
}
