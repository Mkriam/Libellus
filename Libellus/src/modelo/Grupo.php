<?php

require_once 'Conexion.php';
require_once __DIR__ . '/../controlador/validaciones.php';
require_once 'Usuario.php';
require_once 'Libro.php';

/**
 * Clase Grupo
 * 
 * Representa un grupo de usuarios en la aplicación.
 * Permite crear, actualizar, eliminar grupos y gestionar sus miembros y libros.
 * Esta clase incluye métodos para validar datos, realizar operaciones CRUD y manejar la relación entre grupos, usuarios y libros.
 * 
 * @version     1.0
 * @author      Miriam Rodríguez Antequera
 * @package     Modelo
 * @subpackage  Entidades
 */
class Grupo
{
    // Atributos

    /**
     * @var int|null ID del grupo. Es null si el grupo aún no ha sido guardado ya que este se autogenera en la base de datos.
     */
    private $idGrupo;

    /**
     * @var string  Nombre del grupo
     */
    private $nomGrupo;

    /**
     * @var string|null URL de la imagen del grupo, puede ser null.
     */
    private $imgGrupo;

    /**
     * @var string|null Hash de la contraseña del grupo. Es null si el grupo es público.
     */
    private $claveGrupoHash;

    /**
     * @var string Descripción del grupo
     */
    private $descripcion;

    /**
     * @var string Nombre del usuario líder del grupo
     */
    private $idLider;

    /**
     * @var array Lista de miembros del grupo
     */
    private $miembros = [];

    /**
     * @var array Lista de libros del grupo
     */
    private $librosGrupo = [];

    /**
     * Constructor de la clase Grupo.
     *
     * @param string $nomGrupo Nombre del grupo
     * @param string $descripcion Descripción del grupo
     * @param string $idLider Nombre del líder
     * @param string|null $imgGrupo URL de la imagen del grupo (opcional)
     * @param string|null $claveNoHash Contraseña sin hash (opcional)
     * @param int|null $idGrupo ID del grupo (opcional)
     * @throws Exception Si hay errores de validación en algunos de los parámetros.
     */
    public function __construct($nomGrupo, $descripcion, $idLider, $imgGrupo = null, ?string $claveNoHash = null, ?int $idGrupo = null){
        $errores = [];
        // Validar nombre del grupo
        if (!validarCadena($nomGrupo, 1, 100)) {
            $errores[] = "El nombre del grupo no es válido (Como máximo 100 carácteres).";
        }
        // Validar descripción
        if (!validarCadena($descripcion, 1, 200)) {
            $errores[] = "La descripción del grupo no es válida (Como máximo 200 carácteres).";
        }
        // Validar URL de imagen si existe
        if (!is_null($imgGrupo) && !validarUrl($imgGrupo)) {
            $errores[] = "La URL de la imagen del grupo no es válida.";
        }
        // Validar que el líder exista
        if (is_null(Usuario::verUsuarioPorNom($idLider))) {
            $errores[] = "El usuario del líder no es válido.";
        }
        // Validar contraseña si se proporciona
        if (!is_null($claveNoHash)) {
            if (!validarContr($claveNoHash)) {
                $errores[] = "La contraseña no es válida.";
            } else {
                // Guardar el hash de la contraseña
                $this->claveGrupoHash = password_hash($claveNoHash, PASSWORD_DEFAULT);
            }
        } else {
            $this->claveGrupoHash = null;
        }
        // Si hay errores, lanzar excepción
        if (!empty($errores)) {
            throw new Exception(implode(" | ", $errores));
        }
        $this->nomGrupo = $nomGrupo;
        $this->descripcion = $descripcion;
        $this->idLider = $idLider;
        $this->idGrupo = $idGrupo;
        $this->imgGrupo = $imgGrupo;
    }

    // Getters

    /**
     * Obtiene el ID del grupo.
     * @return int|null
     */
    public function getIdGrupo() { return $this->idGrupo; }

    /**
     * Obtiene el nombre del grupo.
     * @return string
     */
    public function getNomGrupo() { return $this->nomGrupo; }

    /**
     * Obtiene la URL de la imagen del grupo.
     * @return string|null
     */
    public function getImgGrupo() { return $this->imgGrupo; }

    /**
     * Obtiene la descripción del grupo.
     * @return string
     */
    public function getDescripcion() { return $this->descripcion; }

    /**
     * Obtiene el nombre del líder del grupo.
     * @return string
     */
    public function getIdLider() { return $this->idLider; }

    /**
     * Obtiene el hash de la contraseña del grupo.
     * @return string|null
     */
    public function getClaveGrupoHash() { return $this->claveGrupoHash; }

    // Setters

    /**
     * Establece el nombre del grupo.
     * @param string $nombre
     * @return bool True si se validó correctamente, sino false
     */
    public function setNomGrupo($nombre)
    {
        $salida = false;
        // Validar nombre antes de asignar
        $nombreValidado = validarCadena($nombre, 1, 100);
        if ($nombreValidado) {
            $this->nomGrupo = $nombreValidado;
            $salida = true;
        }
        return $salida;
    }

    /**
     * Establece la descripción del grupo.
     * @param string $descripcion
     * @return bool True si se validó correctamente, sino false
     */
    public function setDescripcion($descripcion)
    {
        $salida = false;
        // Validar descripción antes de asignar
        $descValidada = validarCadena($descripcion, 1, 200);
        if ($descValidada) {
            $this->descripcion = $descValidada;
            $salida = true;
        }
        return $salida;
    }

    /**
     * Establece el líder del grupo.
     * @param string $lider
     * @return bool True si se validó correctamente, sino false
     */
    public function setIdLider($lider)
    {
        $salida = false;
        $liderValidado = validarUsu($lider);
        if ($liderValidado) {
            // Solo cambia si el usuario existe y es diferente al actual
            if (Usuario::verUsuarioPorNom($lider) && $liderValidado !== $this->idLider) {
                $this->idLider = $liderValidado;
                $salida = true;
            }
        }
        return $salida;
    }

    /**
     * Establece la imagen del grupo.
     * @param string $imgGrupo URL de la imagen
     * @return bool True si la URL es válida, sino false
     */
    public function setImgGrupo($imgGrupo)
    {
        $salida = false;
        // Validar la URL antes de asignar
        if (validarUrl($imgGrupo)) {
            $this->imgGrupo = $imgGrupo;
            $salida = true;
        }
        return $salida;
    }

    /**
     * Establece el hash de la contraseña del grupo.
     * @param string $hash
     * @return void
     */
    public function setClaveGrupoHash($hash)
    {
        $this->claveGrupoHash = $hash;
    }

    /**
     * Establece una nueva contraseña para el grupo.
     * @param string $claveNoHash Contraseña sin hash
     * @return bool True si la contraseña es válida y se actualizó, sino false
     */
    public function setClaveNueva($claveNoHash)
    {
        $salida = false;
        // Validar la contraseña antes de guardar el hash
        if (validarContr($claveNoHash)) {
            $this->claveGrupoHash = password_hash($claveNoHash, PASSWORD_DEFAULT);
            $salida = true;
        }
        return $salida;
    }

    // Métodos estáticos 

    /**
     * Obtiene un grupo por su ID.
     * @param int $idGrupo
     * @return Grupo|null El grupo encontrado o null si no existe
     */
    public static function obtenerGrupo($idGrupo)
    {
        $grupoBuscado = null;
        $idGrupoVal = validarEnteroPositivo($idGrupo);

        if ($idGrupoVal) {
            try {
                $conexion = new Conexion("libellus", "db", "miriam", "libreria123");
                $con = $conexion->getConexion()->prepare("SELECT * FROM GRUPO WHERE id_grupo = :id_grupo");
                $con->bindParam(":id_grupo", $idGrupoVal);
                $con->execute();
                $datosGrupo = $con->fetch(PDO::FETCH_ASSOC);
                $conexion->cerrarConexion();

                if ($datosGrupo) {
                    // Crear objeto Grupo con los datos obtenidos
                    $grupoBuscado = new Grupo(
                        $datosGrupo['nom_grupo'],
                        $datosGrupo['descripcion'],
                        $datosGrupo['id_lider'],
                        $datosGrupo['img_grupo'],
                        null,
                        (int)$datosGrupo['id_grupo']
                    );
                    $grupoBuscado->setClaveGrupoHash($datosGrupo['clave_grupo']);
                }
            } catch (PDOException $e) {
                error_log("Error al obtener el grupo: " . $e->getMessage());
            }
        }
        return $grupoBuscado;
    }

    /**
     * Obtiene un grupo por su nombre.
     * @param string $nombreGrupo
     * @return Grupo|null El grupo encontrado o null si no existe
     */
    public static function obtenerGrupoPorNombre($nombreGrupo)
    {
        $grupoBuscado = null;
        $nombreGrupoVal = validarCadena($nombreGrupo);

        if ($nombreGrupoVal) {
            try {
                $conexion = new Conexion("libellus", "db", "miriam", "libreria123");
                $con = $conexion->getConexion()->prepare("SELECT * FROM GRUPO WHERE nom_grupo = :nom_grupo");
                $con->bindParam(":nom_grupo", $nombreGrupoVal);
                $con->execute();
                $datosGrupo = $con->fetch(PDO::FETCH_ASSOC);
                $conexion->cerrarConexion();

                if ($datosGrupo) {
                    // Crear objeto Grupo con los datos obtenidos
                    $grupoBuscado = new Grupo(
                        $datosGrupo['nom_grupo'],
                        $datosGrupo['descripcion'],
                        $datosGrupo['id_lider'],
                        $datosGrupo['img_grupo'],
                        null,
                        (int)$datosGrupo['id_grupo']
                    );
                    $grupoBuscado->setClaveGrupoHash($datosGrupo['clave_grupo']);
                }
            } catch (PDOException $e) {
                error_log("Error al obtener el grupo por nombre: " . $e->getMessage());
            }
        } else {
            error_log("Nombre de grupo no válido para obtenerGrupoPorNombre: " . print_r($nombreGrupo, true));
        }

        return $grupoBuscado;
    }

    /**
     * Lista todos los grupos.
     * @return Grupo[] Array de objetos Grupo
     */
    public static function listarGrupos()
    {
        $grupos = [];
        try {
            $conexion = new Conexion("libellus", "db", "miriam", "libreria123");
            $con = $conexion->getConexion()->prepare("SELECT id_grupo, nom_grupo, descripcion FROM GRUPO ORDER BY nom_grupo");
            $con->execute();
            $datosGrupos = $con->fetchAll(PDO::FETCH_ASSOC);
            $conexion->cerrarConexion();

            foreach ($datosGrupos as $dato) {
                // Solo se usan algunos campos para crear el objeto Grupo
                $grupos[] = new Grupo($dato['nom_grupo'], $dato['descripcion'], null, null, null, (int)$dato['id_grupo']);
            }
        } catch (PDOException $e) {
            die("Error al listar los grupos: " . $e->getMessage());
        }
        return $grupos;
    }

    /**
     * Busca grupos por nombre.
     * @param string $nombre
     * @return Grupo[] Array de objetos Grupo
     */
    public static function buscarGrupos($nombre)
    {
        $grupos = [];
        try {
            $conexion = new Conexion("libellus", "db", "miriam", "libreria123");
            $con = $conexion->getConexion()->prepare(
                "SELECT id_grupo FROM GRUPO
                  WHERE nom_grupo LIKE :nombre ORDER BY nom_grupo"
            );
            $nombreLike = "%" . $nombre . "%";
            $con->bindParam(":nombre", $nombreLike);
            $con->execute();
            $ids = $con->fetchAll(PDO::FETCH_COLUMN);
            $conexion->cerrarConexion();

            foreach ($ids as $id) {
                // Se obtiene el grupo completo por su ID
                $grupo = Grupo::obtenerGrupo($id);
                if ($grupo) $grupos[] = $grupo;
            }
        } catch (PDOException $e) {
            die("Error al buscar los grupos: " . $e->getMessage());
        }
        return $grupos;
    }

    /**
     * Lista los grupos para el modal de "Unirse a Grupo".
     * @return array|false Array de grupos o false en caso de error
     */
    public static function listarGruposParaModal()
    {
        $salida = false;
        // Array para almacenar los grupos con la información necesaria para el modal
        $gruposParaModal = [];
        try {
            $conexion = new Conexion("libellus", "db", "miriam", "libreria123");
            $con = $conexion->getConexion()->prepare("SELECT id_grupo, nom_grupo, img_grupo, descripcion, (clave_grupo IS NOT NULL AND clave_grupo != '') AS necesitaClave FROM GRUPO ORDER BY nom_grupo");
            $con->execute();
            $datosGrupos = $con->fetchAll(PDO::FETCH_ASSOC);
            $conexion->cerrarConexion();

            foreach ($datosGrupos as $datoGrupo) {
                // Si no hay imagen, se pone una por defecto
                $gruposParaModal[] = [
                    'idGrupo' => (int)$datoGrupo['id_grupo'],
                    'nombreGrupo' => validarCadena($datoGrupo['nom_grupo']),
                    'imgGrupo' => validarCadena($datoGrupo['img_grupo'] ?: '../img/grupo.png'),
                    'descripcion' => validarCadena($datoGrupo['descripcion']),
                    'necesitaClave' => (bool)$datoGrupo['necesitaClave']
                ];
            }
            $salida = $gruposParaModal;
        } catch (PDOException $e) {
            error_log("Error al listar grupos para modal: " . $e->getMessage());
        }
        return $salida;
    }

    /**
     * Elimina un grupo por su ID.
     * @param int $idGrupo
     * @return bool True si se eliminó correctamente, sino false
     */
    public static function eliminarGrupo($idGrupo)
    {
        $idGrupo = validarEnteroPositivo($idGrupo);
        $salida = false;

        if ($idGrupo && Grupo::obtenerGrupo($idGrupo)) {
            try {
                $conexion = new Conexion("libellus", "db", "miriam", "libreria123");
                $con = $conexion->getConexion();
                $con->beginTransaction();

                // Elimina el grupo de la base de datos
                $borrarGrupo = $con->prepare("DELETE FROM Grupo WHERE id_grupo = :id_grupo");
                $borrarGrupo->bindParam(":id_grupo", $idGrupo);
                $borrarGrupo->execute();

                if ($borrarGrupo->rowCount() > 0) {
                    $con->commit();
                    $salida = true;
                } else {
                    $con->rollBack();
                    die("Error al eliminar: No se encontró el grupo o no se pudo eliminar.");
                }

                $conexion->cerrarConexion();
            } catch (PDOException $e) {
                die("Error al eliminar el grupo: " . $e->getMessage());
            }
        }
        return $salida;
    }

    // Métodos de instancia (CRUD)

    /**
     * Guarda el grupo en la base de datos.
     * @return bool True si se guardó correctamente, sino false
     */
    public function guardarGrupo()
    {
        $salida = false;
        $usu = Usuario::verUsuarioPorNom($this->idLider);
        // Verifica que el líder exista
        if ($usu) {
            try {
                $conexion = new Conexion("libellus", "db", "miriam", "libreria123");
                $con = $conexion->getConexion();

                // Verifica si ya existe un grupo con ese nombre
                $busqueda = $con->prepare("SELECT * FROM GRUPO WHERE nom_grupo = :nom_grupo");
                $busqueda->bindParam(":nom_grupo", $this->nomGrupo);
                $busqueda->execute();
                $grupoExistente = $busqueda->fetch(PDO::FETCH_ASSOC);

                if ($grupoExistente) {
                    throw new Exception("Ya existe un grupo con ese nombre.");
                }

                // Si el grupo no existe, se inserta
                if (!Grupo::obtenerGrupo($this->idGrupo)) {
                    $con = $conexion->getConexion()->prepare("INSERT INTO GRUPO (nom_grupo, img_grupo, clave_grupo, descripcion, id_lider) VALUES (:nom, :img, :clave, :descripcion, :lider)");
                }
                $con->bindParam(':nom', $this->nomGrupo);
                $con->bindParam(':img', $this->imgGrupo);
                $con->bindParam(':clave', $this->claveGrupoHash);
                $con->bindParam(':descripcion', $this->descripcion);
                $con->bindParam(':lider', $this->idLider);
                $salida = $con->execute();
                $conexion->cerrarConexion();
            } catch (PDOException $e) {
                die("Error al guardar el grupo: " . $e->getMessage());
            }
        }

        return $salida;
    }

    /**
     * Actualiza los datos del grupo en la base de datos.
     * @return bool True si se actualizó correctamente, sino false
     */
    public function actualizarGrupo()
    {
        $exito = false;
        try {
            $conexion = new Conexion("libellus", "db", "miriam", "libreria123");
            $con = $conexion->getConexion();

            // Busca si ya existe otro grupo con el mismo nombre
            $busqueda = $con->prepare("SELECT * FROM GRUPO WHERE nom_grupo = :nom_grupo AND id_grupo != :id_grupo");
            $busqueda->bindParam(":nom_grupo", $this->nomGrupo);
            $busqueda->bindParam(":id_grupo", $this->idGrupo);
            $busqueda->execute();
            $grupoExistente = $busqueda->fetch(PDO::FETCH_ASSOC);

            if (!$grupoExistente) {
                // Si no existe, actualiza los datos del grupo
                if (Grupo::obtenerGrupo($this->idGrupo)) {
                    $con = $conexion->getConexion()->prepare("UPDATE GRUPO SET nom_grupo = :nom_grupo, img_grupo = :img_grupo, descripcion = :descripcion WHERE id_grupo = :id_grupo");

                    $con->bindParam(':nom_grupo', $this->nomGrupo);
                    $con->bindParam(':img_grupo', $this->imgGrupo);
                    $con->bindParam(':descripcion', $this->descripcion);
                    $con->bindParam(':id_grupo', $this->idGrupo);
                    $exito = $con->execute();
                }
            }

            $conexion->cerrarConexion();
        } catch (PDOException $e) {
            error_log("Error al actualizar grupo {$this->idGrupo}: " . $e->getMessage());
        }
        return $exito;
    }

    // Métodos de miembros del grupo

    /**
     * Obtiene la lista de miembros del grupo.
     * @return array Lista de miembros
     */
    public function getMiembros()
    {
        $this->actualizarMiembros();
        return $this->miembros;
    }

    /**
     * Actualiza la lista de miembros del grupo desde la base de datos.
     * @return void
     */
    private function actualizarMiembros()
    {
        $this->miembros = [];
        if ($this->idGrupo) {
            try {
                $conexion = new Conexion("libellus", "db", "miriam", "libreria123");
                // Solo se necesita el nombre y la foto
                $con = $conexion->getConexion()->prepare("SELECT u.nom_usu, u.foto_perfil FROM USUARIO u JOIN PERTENECE p ON u.nom_usu = p.nom_usu WHERE p.id_grupo = :id_grupo ORDER BY u.nom_usu");
                $con->bindParam(':id_grupo', $this->idGrupo);
                $con->execute();
                $this->miembros = $con->fetchAll(PDO::FETCH_ASSOC);
                $conexion->cerrarConexion();
            } catch (PDOException $e) {
                die("Error al cargar los miembros del grupo: " . $e->getMessage());
            }
        }
    }

    /**
     * Agrega un miembro al grupo.
     *
     * @param string $nomUsu Nombre de usuario
     * @return bool True si se agregó correctamente, sino false
     */
    public function agregarMiembro($nomUsu)
    {
        $exito = false;
        $nomUsuVal = validarUsu($nomUsu);
        $usuNuevo = Usuario::verUsuarioPorNom($nomUsuVal);

        if ($this->idGrupo && $this->idGrupo > 0) {
            if ($usuNuevo && !$usuNuevo->esAdministrador()) {
                // Verificar si el usuario ya está en el grupo
                $miembrosGrupo = $this->getMiembros();

                $yaMiembro = false;
                foreach ($miembrosGrupo as $miembro) {
                    if (strtolower($miembro['nom_usu']) === strtolower($nomUsuVal)) {
                        $yaMiembro = true;
                    }
                }
                if (!$yaMiembro) {
                    try {
                        $conexion = new Conexion("libellus", "db", "miriam", "libreria123");
                        $fechaUnion = date('Y-m-d');
                        // Insertar el nuevo miembro en la tabla PERTENECE
                        $con = $conexion->getConexion()->prepare("INSERT INTO PERTENECE (nom_usu, id_grupo, fec_union)VALUES (:nom_usu, :id_grupo, :fec_union)");
                        $con->bindParam(':nom_usu', $nomUsuVal);
                        $con->bindParam(':id_grupo', $this->idGrupo);
                        $con->bindParam(':fec_union', $fechaUnion);
                        $con->execute();
                        $exito = true;
                        $conexion->cerrarConexion();
                        // Actualizar lista de miembros si se insertó correctamente
                        if ($exito && $con->rowCount() > 0) {
                            $this->actualizarMiembros();
                        }
                    } catch (PDOException $e) {
                        error_log("Error al agregar al nuevo miembro: " . $e->getMessage());
                    }
                } else {
                    error_log("El usuario {$nomUsuVal} ya pertenece al grupo.");
                }
            } else {
                error_log("El usuario {$nomUsuVal} no está registrado en esta web o no introdujo el nombre de usuario correctamente.");
            }
        }
        return $exito;
    }

    /**
     * Elimina un miembro del grupo.
     *
     * @param string $nomUsu Nombre de usuario
     * @return bool True si se eliminó correctamente, sino false
     */
    public function eliminarMiembro($nomUsu)
    {
        $exito = false;
        $nomUsuVal = validarUsu($nomUsu);

        if ($this->idGrupo && $this->idGrupo > 0) {
            // Verificar si el usuario existe
            if (Usuario::verUsuarioPorNom($nomUsuVal)) {
                try {
                    $conexion = new Conexion("libellus", "db", "miriam", "libreria123");
                    $con = $conexion->getConexion();

                    // Verificar si el usuario pertenece al grupo
                    $consulta = $con->prepare("SELECT * FROM PERTENECE WHERE nom_usu = :nom_usu AND id_grupo = :id_grupo");
                    $consulta->bindParam(':nom_usu', $nomUsuVal);
                    $consulta->bindParam(':id_grupo', $this->idGrupo);
                    $consulta->execute();

                    if ($consulta->fetch(PDO::FETCH_ASSOC)) {
                        // Si el usuario pertenece al grupo se elimina
                        $eliminar = $con->prepare("DELETE FROM PERTENECE WHERE nom_usu = :nom_usu AND id_grupo = :id_grupo");
                        $eliminar->bindParam(':nom_usu', $nomUsuVal);
                        $eliminar->bindParam(':id_grupo', $this->idGrupo);
                        $eliminar->execute();

                        if ($eliminar->rowCount() > 0) {
                            $exito = true;
                            $this->actualizarMiembros(); // Actualizar lista de miembros tras eliminar correctamente al miembro
                        } else {
                            error_log("Error al eliminar el usuario {$nomUsuVal} del grupo.");
                        }
                    }

                    $conexion->cerrarConexion();
                } catch (PDOException $e) {
                    error_log("Error al eliminar miembro {$nomUsuVal}: " . $e->getMessage());
                }
            } else {
                error_log("El nombre de usuario no es válido.");
            }
        } else {
            error_log("El grupo no es válido.");
        }

        return $exito;
    }

    // Métodos de libros guardados en el grupo

    /**
     * Obtiene la lista de libros del grupo.
     * @return array Lista de libros
     */
    public function getLibros()
    {
        $this->actualizarLibros();
        return $this->librosGrupo;
    }

    /**
     * Agrega un libro al grupo.
     *
     * @param int $idLibro ID del libro
     * @return bool True si se agregó correctamente, sino false
     */
    public function agregarLibro($idLibro)
    {
        $exito = false;
        $idLibroVal = validarEnteroPositivo($idLibro);
        if ($this->idGrupo && $this->idGrupo > 0) {
            if (Libro::verLibro($idLibroVal)) {
                try {
                    $conexion = new Conexion("libellus", "db", "miriam", "libreria123");
                    // Usar DATETIME actual con hora y minutos
                    $fecha = date('Y-m-d H:i:s');
                    $con = $conexion->getConexion()->prepare("INSERT INTO CONTIENE (id_grupo, id_libro, fecha) VALUES (:id_grupo, :id_libro, :fecha)");
                    $con->bindParam(':id_grupo', $this->idGrupo);
                    $con->bindParam(':id_libro', $idLibroVal);
                    $con->bindParam(':fecha', $fecha);
                    $exito = $con->execute();
                    $conexion->cerrarConexion();
                    // Actualizar la lista de libros si se insertó correctamente
                    if ($exito && $con->rowCount() > 0) {
                        $this->actualizarLibros(); // Recargar los libros del grupo
                    }
                } catch (PDOException $e) {
                    die("Error al agregar el libro al grupo: " . $e->getMessage());
                }
            } else {
                error_log("El libro {$idLibroVal} introducido no se ha encontrado.");
            }
        }
        return $exito;
    }

    /**
     * Actualiza la lista de libros del grupo desde la base de datos.
     * @return void
     */
    private function actualizarLibros()
    {
        $this->librosGrupo = [];

        if ($this->idGrupo) {
            try {
                $conexion = new Conexion("libellus", "db", "miriam", "libreria123");
                // Consulta para obtener los libros del grupo, ahora la columna fecha es DATETIME
                $con = $conexion->getConexion()->prepare("
                SELECT L.id_libro, L.titulo, L.portada, C.fecha 
                FROM CONTIENE C INNER JOIN LIBRO L ON C.id_libro = L.id_libro
                WHERE C.id_grupo = :id_grupo ORDER BY C.fecha DESC, L.titulo ASC ");
                $con->bindParam(':id_grupo', $this->idGrupo);
                $con->execute();
                $libros = $con->fetchAll(PDO::FETCH_ASSOC);

                // Obtener autores y géneros de cada libro con consultas individuales
                foreach ($libros as &$libro) {
                    // Consulta para obtener los autores
                    $conAutores = $conexion->getConexion()->prepare("
                    SELECT nom_autor FROM AUTOR WHERE id_autor IN ( SELECT id_autor FROM ESCRIBE WHERE id_libro = :id_libro)");
                    $conAutores->bindParam(':id_libro', $libro['id_libro']);
                    $conAutores->execute();
                    $autores = $conAutores->fetchAll(PDO::FETCH_COLUMN);
                    $libro['autores'] = implode(', ', $autores);

                    // Consulta para obtener los géneros
                    $conGeneros = $conexion->getConexion()->prepare("
                    SELECT nom_genero FROM GENERO WHERE id_genero IN ( SELECT id_genero FROM POSEE WHERE id_libro = :id_libro) ");
                    $conGeneros->bindParam(':id_libro', $libro['id_libro']);
                    $conGeneros->execute();
                    $generos = $conGeneros->fetchAll(PDO::FETCH_COLUMN);
                    $libro['generos'] = implode(', ', $generos);
                }

                $this->librosGrupo = $libros;
                $conexion->cerrarConexion();
            } catch (PDOException $e) {
                die("Error al cargar los libros del grupo: " . $e->getMessage());
            }
        }
    }

    /**
     * Elimina un libro del grupo.
     *
     * @param int $idLibro ID del libro
     * @return bool True si se eliminó correctamente, sino false
     */
    public function eliminarLibro($idLibro)
    {
        $exito = false;
        $idLibroVal = validarEnteroPositivo($idLibro);

        if ($this->idGrupo && $this->idGrupo > 0) {
            if (Libro::verLibro($idLibroVal)) {
                try {
                    $conexion = new Conexion("libellus", "db", "miriam", "libreria123");
                    // Eliminar el libro del grupo
                    $con = $conexion->getConexion()->prepare("DELETE FROM CONTIENE WHERE id_grupo = :id_grupo AND id_libro = :id_libro");
                    $con->bindParam(':id_grupo', $this->idGrupo);
                    $con->bindParam(':id_libro', $idLibroVal);
                    $con->execute();
                    $exito = true;
                    $conexion->cerrarConexion();
                    // Actualizar la lista de libros si se eliminó correctamente
                    if ($exito && $con->rowCount() > 0) {
                        $this->actualizarLibros(); //Hay que actualizar la lista de libros del grupo cada que se elimine uno
                    }
                } catch (PDOException $e) {
                    die("Error al eliminar libro del grupo: " . $e->getMessage());
                }
            } else {
                error_log("El libro {$idLibroVal} no está registrado en esta web.");
            }
        }
        return $exito;
    }
}
