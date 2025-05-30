<?php
require_once 'Conexion.php';
require_once 'Grupo.php';
require_once 'Autor.php';
require_once 'Genero.php';
require_once '../controlador/validaciones.php';

/**
 * Clase Libro
 * 
 * Representa un libro en la aplicación.
 * Permite crear, consultar, listar, actualizar y eliminar libros, así como gestionar sus autores y géneros.
 * 
 * @package Modelo
 * @author Miriam Rodríguez Antequera
 */
class Libro {
    /**
     * @var int|null ID del libro
     */
    private $idLibro;
    /**
     * @var string Título del libro
     */
    private $titulo;
    /**
     * @var string|null URL de la portada
     */
    private $portada;
    /**
     * @var string Sinopsis del libro
     */
    private $sinopsis;
    /**
     * @var string Fecha de publicación (YYYY-MM-DD)
     */
    private $fecPublicacion;
    /**
     * @var string|null URL de compra
     */
    private $urlCompra;
    /**
     * @var Autor[] Lista de autores
     */
    private $autores = [];
    /**
     * @var Genero[] Lista de géneros
     */
    private $generos = [];

    /**
     * Constructor de la clase Libro.
     * 
     * @param string $titulo
     * @param string $sinopsis
     * @param string $fecPublicacion
     * @param string|null $portada
     * @param string|null $urlCompra
     * @param int|null $idLibro
     * @throws Exception Si algún dato no es válido
     */
    public function __construct($titulo, $sinopsis, $fecPublicacion, ?string $portada = null, ?string $urlCompra = null, ?int $idLibro = null) {
        $errores = [];
        // Validar título
        if (!validarCadena($titulo, 1, 200)) {
            $errores[] = "El título del libro no es válido.";
        }
        // Validar sinopsis
        if (!validarCadena($sinopsis, 1, 400)) {
            $errores[] = "La sinopsis del libro no es válida.";
        }
        $portadaValidada = null;
        if (!is_null($portada) && trim($portada) !== '') {
            if (!validarUrl($portada)) {
                $errores[] = "La URL de la portada no es válida.";
            } else {
                $portadaValidada = $portada;
            }
        }
        $urlCompraValidada = null;
        if (!is_null($urlCompra) && trim($urlCompra) !== '') {
            if (!validarUrl($urlCompra)) {
                $errores[] = "La URL de compra no es válida.";
            } else {
                $urlCompraValidada = $urlCompra;
            }
        }
        // Validar fecha
        if (!validarFecha($fecPublicacion)) {
             $errores[] = "Fecha de publicación no válida."; 
         }
        if (!empty($errores)) {
            throw new Exception(implode(" - ", $errores));
        }
        $this->titulo = $titulo;
        $this->sinopsis = $sinopsis;
        $this->idLibro = $idLibro;
        $this->portada = $portadaValidada;
        $this->fecPublicacion = $fecPublicacion;
        $this->urlCompra = $urlCompraValidada;
        $this->autores = [];
        $this->generos = [];
    }

    // Getters

    public function getIdLibro() {
        return $this->idLibro;
    }
    public function getTitulo() {
        return $this->titulo;
    }
    public function getPortada() {
        return $this->portada;
    }
    public function getSinopsis() {
        return $this->sinopsis;
    }
    public function getFecPublicacion() {
        return $this->fecPublicacion;
    }
    public function getUrlCompra() {
        return $this->urlCompra;
    }
    public function getAutores() {
        return $this->autores;
    }
    public function getGeneros() {
        return $this->generos;
    }

    // Setters

    public function setTitulo($titulo) {
        $this->titulo = validarCadena($titulo);
    }
    public function setPortada($portada) {
        $this->portada = validarUrl($portada);
    }
    public function setSinopsis($sinopsis) {
        $this->sinopsis = validarCadena($sinopsis);
    }
    public function setFecPublicacion($fecPublicacion) {
        $this->fecPublicacion = validarFecha($fecPublicacion);
    }
    public function setUrlCompra($urlCompra) {
        $this->urlCompra = validarCadena($urlCompra);
    }

    public function agregarAutor(Autor $autor) {
        $this->autores[] = $autor;
    }
    public function agregarGenero(Genero $genero) {
        $this->generos[] = $genero;
    }

    // Métodos estáticos

    public static function verLibro($idLibro) {
        $idLibro = validarEnteroPositivo($idLibro);
        $salida = false;
        try {
            $conexion = new Conexion("libellus", "db", "miriam", "libreria123");
            $con = $conexion->getConexion()->prepare("SELECT * FROM LIBRO WHERE id_libro = :id_libro");
            $con->bindParam(":id_libro", $idLibro);
            $con->execute();
            $datosLibro = $con->fetch(PDO::FETCH_ASSOC);

            if ($datosLibro) {
                // Si existe, crea el objeto Libro y carga autores y géneros
                $libro = new Libro(
                    $datosLibro['titulo'],
                    $datosLibro['sinopsis'],
                    $datosLibro['fec_publicacion'],
                    $datosLibro['portada'],
                    $datosLibro['url_compra'],
                    (int)$datosLibro['id_libro']
                );
                $libro->cargarAutores();
                $libro->cargarGeneros();
                $conexion->cerrarConexion();
                $salida = $libro;
            }
            $conexion->cerrarConexion();
        } catch (PDOException $e) {
            die("Error al buscar el libro: " . $e->getMessage());
        }
        return $salida;
    }

    public static function listarLibros() {
        $libros = [];
        try {
            $conexion = new Conexion("libellus", "db", "miriam", "libreria123");
            $sql = "SELECT id_libro, titulo, portada, sinopsis, fec_publicacion, url_compra
                    FROM LIBRO
                    ORDER BY titulo ASC";
            $con = $conexion->getConexion()->prepare($sql);
            $con->execute();
            $datosLibros = $con->fetchAll(PDO::FETCH_ASSOC);

            foreach ($datosLibros as $datosLibro) {
                try {
                    // Crea cada libro y carga autores y géneros
                    $libro = new Libro(
                        $datosLibro['titulo'],
                        $datosLibro['sinopsis'],
                        $datosLibro['fec_publicacion'],
                        $datosLibro['portada'],
                        $datosLibro['url_compra'],
                        (int)$datosLibro['id_libro']
                    );
                    $libro->cargarAutores();
                    $libro->cargarGeneros();
                    $libros[] = $libro;
                } catch (Exception $e) {
                    die("Error creando el libro(ID: {$datosLibro['id_libro']}): " . $e->getMessage());
                }
            }
        } catch (PDOException $e) {
            die("Error en listarLibros: " . $e->getMessage());
        }
        $conexion->cerrarConexion();
        return $libros;
    }

    public function guardarLibro() {
        $salida = false;
        try {
            $conexion = new Conexion("libellus", "db", "miriam", "libreria123");
            
            // Si el libro ya existe, actualiza; si no, inserta
            if (Libro::verLibro($this->idLibro)) {
                $consulta = $conexion->getConexion()->prepare("UPDATE LIBRO SET titulo = :titulo, portada = :portada, sinopsis = :sinopsis, fec_publicacion = :fec_publicacion, url_compra = :url_compra WHERE id_libro = :id_libro");
                $consulta->bindParam(":id_libro", $this->idLibro);
            } else {
                $consulta = $conexion->getConexion()->prepare("INSERT INTO LIBRO (titulo, portada, sinopsis, fec_publicacion, url_compra) VALUES (:titulo, :portada, :sinopsis, :fec_publicacion, :url_compra)");
            }
            $consulta->bindParam(":titulo", $this->titulo);
            $consulta->bindParam(":portada", $this->portada);
            $consulta->bindParam(":sinopsis", $this->sinopsis);
            $consulta->bindParam(":fec_publicacion", $this->fecPublicacion);
            $consulta->bindParam(":url_compra", $this->urlCompra);
            $consulta->execute();

            // Obtener el ID del libro recién guardado (por título)
            $con = $conexion->getConexion()->prepare("SELECT id_libro FROM LIBRO WHERE titulo = :titulo ORDER BY id_libro DESC LIMIT 1");
            $con->bindParam(":titulo", $this->titulo);
            $con->execute();
            $idEsteLibro = $con->fetch(PDO::FETCH_ASSOC);
            
            $this->idLibro = $idEsteLibro['id_libro'];

            // Guardar relaciones con autores y géneros
            $this->guardarAutores();
            $this->guardarGeneros();

            $salida = true;
            $conexion->cerrarConexion();
        } catch (PDOException $e) {
            die("Error al guardar el libro: " . $e->getMessage());
        }
        return $salida;
    }

    public static function eliminarLibro($idLibro) {
        $idLibro = validarEnteroPositivo(filter_var($idLibro, FILTER_VALIDATE_INT));
        $salida = false;
        if (Libro::verLibro($idLibro)) {
            try {
                $conexion = new Conexion("libellus", "db", "miriam", "libreria123");
                $con = $conexion->getConexion()->prepare("DELETE FROM LIBRO WHERE id_libro = :id_libro");
                $con->bindParam(":id_libro", $idLibro);
                $con->execute();
                $conexion->cerrarConexion();
                if ($con->rowCount() > 0) {
                    $salida = true;
                    error_log("Libro ID {$idLibro} eliminado.");
                } else {
                    error_log("No se eliminó el libro ID {$idLibro}.");
                }
            } catch (PDOException $e) {
                die("Error al eliminar el libro: " . $e->getMessage());
            }
        }
        return $salida;
    }

    private function cargarAutores() {
        if ($this->idLibro) {
            try {
                $conexion = new Conexion("libellus", "db", "miriam", "libreria123");
                // Consulta los autores relacionados con el libro
                $con = $conexion->getConexion()->prepare("SELECT a.id_autor, a.nom_autor FROM AUTOR a JOIN ESCRIBE e ON a.id_autor = e.id_autor WHERE e.id_libro = :id_libro");
                $con->bindParam(":id_libro", $this->idLibro);
                $con->execute();
                $autoresDatos = $con->fetchAll(PDO::FETCH_ASSOC);
                foreach ($autoresDatos as $autorDatos) {
                    $this->agregarAutor(new Autor($autorDatos['nom_autor'], (int)$autorDatos['id_autor']));
                }
                $conexion->cerrarConexion();
            } catch (PDOException $e) {
                die(("Error al cargar los autores del libro: " . $e->getMessage()));
            }
        }
    }

    private function guardarAutores() {
        $salida = false;
        if ($this->idLibro) {
            $libroId = $this->idLibro;
            try {
                $conexion = new Conexion("libellus", "db", "miriam", "libreria123");
                if (!empty($this->autores)) {
                    // Prepara la consulta para insertar relación ESCRIBE
                    $con = $conexion->getConexion()->prepare("INSERT INTO ESCRIBE (id_libro, id_autor) VALUES (:id_libro, :id_autor)");
                    
                    foreach ($this->autores as $autor) {
                        // Solo guarda si el autor es válido y tiene ID
                        if ($autor instanceof Autor && $autor->getIdAutor()) {
                            $idAutor = $autor->getIdAutor();
                            $con->bindParam(":id_libro", $libroId);
                            $con->bindParam(":id_autor", $idAutor);
                            $con->execute();
                            $salida = true;
                        } else {
                            error_log("Intento de guardar relación ESCRIBE para Libro ID {$libroId} con autor no válido.");
                        }
                    }
                }
                $conexion->cerrarConexion();
            } catch (PDOException $e) {
                die("Error al guardar los autores del libro: " . $e->getMessage());
            }
        }
        return $salida;
    }

    private function cargarGeneros() {
        if ($this->idLibro) {
            try {
                $conexion = new Conexion("libellus", "db", "miriam", "libreria123");
                // Consulta los géneros relacionados con el libro
                $con = $conexion->getConexion()->prepare("SELECT g.id_genero, g.nom_genero FROM GENERO g JOIN POSEE p ON g.id_genero = p.id_genero WHERE p.id_libro = :id_libro");
                $con->bindParam(":id_libro", $this->idLibro);
                $con->execute();
                $generosDatos = $con->fetchAll(PDO::FETCH_ASSOC);
                foreach ($generosDatos as $generoDatos) {
                    $this->agregarGenero(new Genero($generoDatos['nom_genero'], (int)$generoDatos['id_genero']));
                }
                $conexion->cerrarConexion();
            } catch (PDOException $e) {
                die("Error al cargar los géneros del libro: " . $e->getMessage());
            }
        }
    }

    private function guardarGeneros() {
        $salida = false;
        if ($this->idLibro) {
            $libroId = $this->idLibro;
            try {
                $conexion = new Conexion("libellus", "db", "miriam", "libreria123");
                $con = $conexion->getConexion();

                if (!empty($this->generos)) {
                    // Prepara la consulta para insertar relación POSEE
                    $insertGenero = $con->prepare("INSERT INTO POSEE (id_libro, id_genero) VALUES (:id_libro, :id_genero)");
                    foreach ($this->generos as $genero) {
                        // Solo guarda si el género es válido y tiene ID
                        if ($genero instanceof Genero && $genero->getIdGenero()) {
                            $idGenero = $genero->getIdGenero();
                            $insertGenero->bindParam(":id_libro", $libroId);
                            $insertGenero->bindParam(":id_genero", $idGenero);
                            $insertGenero->execute();
                            $salida = true;
                        } else {
                            error_log("Intento de guardar relación POSEE para Libro ID {$libroId} con género inválido.");
                        }
                    }
                }
                $conexion->cerrarConexion();
            } catch (PDOException $e) {
                die("Error al guardar los géneros del libro: " . $e->getMessage());
            }
        }
        return $salida;
    }
}