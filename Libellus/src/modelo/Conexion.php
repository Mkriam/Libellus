<?php

/**
 * Clase Conexion
 * 
 * Gestiona la conexión a la base de datos usando PDO.
 * Permite abrir y cerrar conexiones de forma sencilla.
 */
class Conexion {

    /**
     * @var string Nombre de la base de datos
     */
    private $bd;

    /**
     * @var string Host de la base de datos
     */
    private $host;

    /**
     * @var string Usuario de la base de datos
     */
    private $usu;

    /**
     * @var string Contraseña de la base de datos
     */
    private $contra;

    /**
     * @var PDO|null Objeto de conexión PDO
     */
    private $conexion;

    /**
     * Constructor de la clase Conexion.
     * 
     * @param string $bd Nombre de la base de datos
     * @param string $host Host de la base de datos
     * @param string $usu Usuario de la base de datos
     * @param string $contra Contraseña de la base de datos (opcional)
     */
    public function __construct($bd, $host, $usu, $contra = "") {
        $this->bd = $bd;
        $this->host = $host;
        $this->usu = $usu;
        $this->contra = $contra;

        try {
            // Crea la conexión PDO a MySQL
            $this->conexion = new PDO(
            "mysql:host=$this->host;port=3306;dbname=$this->bd;charset=utf8mb4", $this->usu, $this->contra);
            // Configura el modo de errores para lanzar excepciones
            $this->conexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (Exception $e) {
            // Si falla la conexión, detiene la ejecución y muestra el error
            die("Error: ".$e->getMessage());
        }
    }
    
    /**
     * Devuelve la conexión PDO.
     * @return PDO|null
     */
    public function getConexion() {
        return $this->conexion;
    }

    /**
     * Cierra la conexión.
     * @return void
     */
    public function cerrarConexion() {
        $this->conexion = null;
    }
    
}

