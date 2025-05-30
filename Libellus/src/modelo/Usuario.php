<?php

require_once 'Conexion.php';
require_once '../controlador/validaciones.php';
require_once 'Grupo.php';
require_once 'Libro.php';

/**
 * Clase Usuario
 * 
 * Representa un usuario de la aplicación.
 * Permite crear, consultar, actualizar y eliminar usuarios, así como gestionar sus grupos y libros guardados.
 * 
 * @package Modelo
 * @author Miriam Rodríguez Antequera
 */
class Usuario
{
    /**
     * @var string Nombre de usuario
     */
    private $nomUsu;

    /**
     * @var string Email del usuario
     */
    private $email;

    /**
     * @var string|null Hash de la contraseña
     */
    private $claveUsu;

    /**
     * @var string|null URL de la foto de perfil
     */
    private $fotoPerfil;

    /**
     * @var int 1 si es administrador, 0 si no lo es
     */
    private $administrador;

    /**
     * @var array Lista de grupos a los que pertenece el usuario
     */
    private $grupos = [];

    /**
     * @var array Lista de libros guardados por el usuario
     */
    private $librosGuardados = [];

    /**
     * Constructor de la clase Usuario.
     * 
     * @param string $nomUsu Nombre de usuario
     * @param string $email Email del usuario
     * @param string|null $claveSinHash Contraseña sin hash (opcional)
     * @param string|null $fotoPerfil URL de la foto de perfil (opcional)
     * @param int $administrador 1 si es admin, 0 si no lo es
     * @throws Exception Si algún dato no es válido
     */
    public function __construct($nomUsu, $email, $claveSinHash = null, $fotoPerfil = null, $administrador = 0)
    {
        $errores = [];

        // Validar nombre de usuario
        if (!validarUsu($nomUsu)) {
            $errores[] = "El nombre de usuario no es válido.";
        }
        // Validar email
        if (!validarEmail($email)) {
            $errores[] = "El email no es válido.";
        }
        // Validar contraseña si se proporciona
        if (!is_null($claveSinHash)) {
            if (!validarContr($claveSinHash)) {
                $errores[] = "La contraseña no es válida.";
            } else {
                // Guardar el hash de la contraseña
                $this->claveUsu = password_hash($claveSinHash, PASSWORD_DEFAULT);
            }
        } else {
            $this->claveUsu = null;
        }
        // Validar URL de la foto si se proporciona
        if (!is_null($fotoPerfil) && !validarUrl($fotoPerfil)) {
            $errores[] = "La ruta de la foto no es válida.";
        }

        if (!empty($errores)) {
            throw new Exception(implode(" | ", $errores));
        }

        $this->nomUsu = $nomUsu;
        $this->email = $email;
        $this->fotoPerfil = $fotoPerfil;
        $this->administrador = $administrador;

        $this->grupos = [];
        $this->librosGuardados = [];
    }


    // Getters


    /**
     * Obtiene el nombre de usuario.
     * @return string
     */
    public function getNomUsu()
    {
        return $this->nomUsu;
    }

    /**
     * Obtiene el email del usuario.
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Obtiene la URL de la foto de perfil.
     * @return string|null
     */
    public function getFotoPerfil()
    {
        return $this->fotoPerfil;
    }

    /**
     * Indica si el usuario es administrador.
     * @return int 1 si es admin, 0 si no lo es
     */
    public function esAdministrador()
    {
        return $this->administrador;
    }

    /**
     * Obtiene el hash de la contraseña.
     * @return string|null
     */
    public function getClaveHash()
    {
        return $this->claveUsu;
    }


    // Setters


    /**
     * Establece el email del usuario.
     * @param string $email
     * @return bool True si se estableció correctamente, false en caso contrario
     */
    public function setEmail($email)
    {
        $salida = false;
        $emailValidado = validarEmail($email);
        if ($emailValidado) {
            $this->email = $emailValidado;
            $salida = true;
        }
        return $salida;
    }

    /**
     * Establece la foto de perfil del usuario.
     * @param string|null $fotoPerfil
     * @return bool True si se estableció correctamente, false en caso contrario
     */
    public function setFotoPerfil($fotoPerfil)
    {
        $salida = false;
        // Si es null o vacío, se borra la foto
        if (is_null($fotoPerfil) || trim($fotoPerfil ?? '') === '') {
            $this->fotoPerfil = null;
            $salida = true;
        } else {
            $fotoValidada = validarUrl($fotoPerfil);
            if ($fotoValidada) {
                $this->fotoPerfil = $fotoValidada;
                $salida = true;
            }
        }
        return $salida;
    }

    /**
     * Establece la contraseña del usuario (hash).
     * @param string $claveSinHash Contraseña sin hash
     * @return bool True si se estableció correctamente, false en caso contrario
     */
    public function setClave($claveSinHash)
    {
        $salida = false;
        $contrValidada = validarContr($claveSinHash);
        if ($contrValidada) {
            $this->claveUsu = password_hash($contrValidada, PASSWORD_DEFAULT);
            $salida = true;
        }
        return $salida;
    }


    // Métodos de instancia


    /**
     * Actualiza los datos del usuario (email y foto) en la base de datos.
     * @return bool True si se actualizó correctamente, false en caso contrario
     */
    public function actualizarDatos()
    {
        $exito = false;
        if (!is_null($this->nomUsu)) {
            try {
                $conexion = new Conexion("libellus", "db", "miriam", "libreria123");
                $con = $conexion->getConexion();
                // Prepara la consulta para actualizar email y foto
                $con = $con->prepare("UPDATE USUARIO SET email = :email, foto_perfil = :fotoPerfil WHERE nom_usu = :nomUsu");
                $con->bindParam(':email', $this->email);
                $con->bindParam(':fotoPerfil', $this->fotoPerfil);
                $con->bindParam(':nomUsu', $this->nomUsu);
                $exito = $con->execute();
                $conexion->cerrarConexion();
            } catch (PDOException $ex) {
                error_log("Error al actualizar datos del usuario {$this->nomUsu}: " . $ex->getMessage());
            }
        } else {
            error_log("No se puede actualizar los datos del usuario sin el nombre.");
        }
        return $exito;
    }

    /**
     * Cambia la contraseña del usuario en la base de datos.
     * @return bool True si se actualizó correctamente, false en caso contrario
     */
    public function cambiarClave()
    {
        $exito = false;
        // Verifica que el nombre de usuario y la clave existan
        if (!is_null($this->nomUsu) && !is_null($this->claveUsu)) {
            try {
                $conexion = new Conexion("libellus", "db", "miriam", "libreria123");
                // Actualiza la clave en la base de datos
                $con = $conexion->getConexion()->prepare("UPDATE USUARIO SET clave_usu = :claveUsu WHERE nom_usu = :nomUsu");
                $con->bindParam(':clave_usu', $this->claveUsu);
                $con->bindParam(':nom_usu', $this->nomUsu);
                $exito = $con->execute();
                $conexion->cerrarConexion();
            } catch (PDOException $ex) {
                error_log("Error al actualizar clave del usuario {$this->nomUsu}: " . $ex->getMessage());
            }
        } else {
            error_log("No se puede actualizar clave sin el nombre del usuario o la nueva clave.");
        }
        return $exito;
    }


    // Métodos Estáticos


    /**
     * Busca un usuario por su nombre de usuario.
     * @param string $nomUsuBuscado
     * @return Usuario|null El usuario encontrado o null si no existe
     */
    public static function verUsuarioPorNom($nomUsuBuscado)
    {
        $usuario = null;
        $nomUsuVal = validarUsu($nomUsuBuscado);

        if ($nomUsuVal) {
            try {
                $conexion = new Conexion("libellus", "db", "miriam", "libreria123");
                // Consulta el usuario por nombre
                $con = $conexion->getConexion()->prepare("SELECT nom_usu, email, clave_usu, foto_perfil, administrador FROM USUARIO WHERE nom_usu = :nomUsu");
                $con->bindParam(":nomUsu", $nomUsuVal);
                $con->execute();
                $datos = $con->fetch(PDO::FETCH_ASSOC);

                if ($datos) {
                    // Se crea el objeto Usuario con los datos de la BD
                    $usuario = new Usuario(
                        $datos['nom_usu'],
                        $datos['email'],
                        null, // No se pasa la clave sin hash
                        $datos['foto_perfil'],
                        $datos['administrador']
                    );
                    // Se asigna el hash de la clave directamente
                    $usuario->claveUsu = $datos['clave_usu'];
                }
                $conexion->cerrarConexion();
            } catch (PDOException $ex) {
                error_log("Error al obtener usuario: " . $ex->getMessage());
            }
        } else {
            error_log("Nombre de usuario no válido");
        }

        return $usuario;
    }

    /**
     * Busca un usuario por su email.
     * @param string $emailUsuBuscado
     * @return Usuario|null El usuario encontrado o null si no existe
     */
    public static function verUsuarioPorEmail($emailUsuBuscado)
    {
        $usuario = null;
        $emailUsuVal = validarEmail($emailUsuBuscado);

        if ($emailUsuVal) {
            try {
                $conexion = new Conexion("libellus", "db", "miriam", "libreria123");
                // Consulta el usuario por email
                $con = $conexion->getConexion()->prepare("SELECT nom_usu, email, clave_usu, foto_perfil, administrador FROM USUARIO WHERE email = :email");
                $con->bindParam(":email", $emailUsuVal);
                $con->execute();
                $datos = $con->fetch(PDO::FETCH_ASSOC);

                if ($datos) {
                    $usuario = new Usuario(
                        $datos['nom_usu'],
                        $datos['email'],
                        null,
                        $datos['foto_perfil'],
                        $datos['administrador']
                    );
                    $usuario->claveUsu = $datos['clave_usu'];
                }
                $conexion->cerrarConexion();
            } catch (PDOException $ex) {
                error_log("Error al obtener usuario: " . $ex->getMessage());
            }
        } else {
            error_log("Nombre de usuario no válido");
        }

        return $usuario;
    }

    /**
     * Cambia el nombre de usuario en la base de datos.
     * @param string $nomNuevo Nuevo nombre de usuario
     * @return bool True si se cambió correctamente, false en caso contrario
     */
    public function cambiarNombreUsu($nomNuevo)
    {
        $salida = false;
        $nomViejo = $this->nomUsu;

        $nomNuevo = validarUsu($nomNuevo);
        // Solo cambia si el nombre nuevo es válido y diferente al actual
        if (is_null($nomViejo) || $nomNuevo === false || $nomNuevo === $nomViejo) {
            error_log("cambiarNombreUsu: Intento inválido o sin cambios (Viejo: '{$nomViejo}', Nuevo: '{$nomNuevo}').");
        } else {
            try {
                $conexion = new Conexion("libellus", "db", "miriam", "libreria123");
                $con = $conexion->getConexion();

                $con->beginTransaction();
                // Actualiza el nombre de usuario en la base de datos
                $actualizacionNom = $con->prepare("UPDATE USUARIO SET nom_usu = :nuevoNom WHERE nom_usu = :viejoNom");
                $actualizacionNom->bindParam(':nuevoNom', $nomNuevo);
                $actualizacionNom->bindParam(':viejoNom', $nomViejo);

                $funciona = $actualizacionNom->execute();

                if ($funciona) {
                    if ($actualizacionNom->rowCount() > 0) {
                        $con->commit();
                        $this->nomUsu = $nomNuevo;
                        $salida = true;
                    } else {
                        $con->rollBack();
                        error_log("Actualizacion realizada pero no afectó a la base de datos.");
                    }
                } else {
                    $con->rollBack();
                    error_log("[cambiarNombreUsu] Error al ejecutar UPDATE: " . implode(":", $actualizacionNom->errorInfo()) . ". Rollback.");
                }
            } catch (PDOException $ex) {
                $con->rollBack();
                error_log("Error al cambiar nombre de usuario de '{$nomViejo}' a '{$nomNuevo}': " . $ex->getMessage());
            }
        }

        $conexion->cerrarConexion();
        return $salida;
    }

    /**
     * Verifica el login de un usuario por email y contraseña.
     * @param string $emailUsu Email del usuario
     * @param string $claveIngresada Contraseña ingresada
     * @return Usuario|false El usuario si el login es correcto, false si no
     */
    public static function verificarLogin($emailUsu, $claveIngresada)
    {
        $salida = false;
        $usuario = Usuario::verUsuarioPorEmail($emailUsu);

        // Verifico que el usuario exista y que la contraseña coincida con el hash
        if ($usuario) {
            $claveHash = $usuario->getClaveHash();
            if ($claveHash !== null && password_verify($claveIngresada, $claveHash)) {
                $salida = $usuario;
            }
        }
        return $salida;
    }

    /**
     * Elimina un usuario por su nombre de usuario.
     * @param string $nomUsu
     * @return bool True si se eliminó correctamente, false en caso contrario
     */
    public static function eliminarUsuario($nomUsu)
    {
        $salida = false;
        $nomUsuVal = validarUsu($nomUsu);

        if ($nomUsuVal) {
            try {
                $conexion = new Conexion("libellus", "db", "miriam", "libreria123");
                $con = $conexion->getConexion();
                $con->beginTransaction();

                // Elimina el usuario de la base de datos
                $borrarUsuario = $con->prepare("DELETE FROM USUARIO WHERE nom_usu = :nomUsu");
                $borrarUsuario->bindParam(":nomUsu", $nomUsuVal);
                $borrarUsuario->execute();

                if ($borrarUsuario->rowCount() > 0) {
                    $con->commit();
                    $salida = true;
                    error_log("Usuario eliminado correctamente.");
                } else {
                    $con->rollBack();
                    error_log("Error al eliminar al usuario.");
                }
                $conexion->cerrarConexion();
            } catch (PDOException $ex) {
                if (isset($con) && $con->inTransaction()) {
                    $con->rollBack();
                }
                error_log("Error BD al eliminar usuario: " . $ex->getMessage());
            }
        } else {
            error_log("Intento de eliminar usuario con nombre inválido: " . $nomUsu);
        }
        return $salida;
    }

    /**
     * Guarda el usuario nuevo en la base de datos.
     * @return bool True si se guardó correctamente, false en caso contrario
     */
    public function guardarUsuario()
    {
        $exito = false;
        try {
            $conexion = new Conexion("libellus", "db", "miriam", "libreria123");
            $con = $conexion->getConexion();

            // Verificar si el usuario ya existe
            $consulta = $con->prepare("SELECT nom_usu FROM USUARIO WHERE nom_usu = :nombre OR email = :email");
            $consulta->bindParam(':nombre', $this->nomUsu);
            $consulta->bindParam(':email', $this->email);
            $consulta->execute();

            if ($consulta->rowCount() === 0) {
                // Si no existe, lo inserta
                $insertar = $con->prepare("INSERT INTO USUARIO (nom_usu, email, clave_usu, foto_perfil) VALUES (:nombre, :email, :clave, :foto)");
                $insertar->bindParam(':nombre', $this->nomUsu);
                $insertar->bindParam(':email', $this->email);
                $insertar->bindParam(':clave', $this->claveUsu);
                $insertar->bindParam(':foto', $this->fotoPerfil);

                $exito = $insertar->execute();
            }
            $conexion->cerrarConexion();
        } catch (PDOException $ex) {
            error_log("Error al guardar usuario: " . $ex->getMessage());
        }
        return $exito;
    }


    // Métodos para Grupos


    /**
     * Obtiene los grupos a los que pertenece el usuario.
     * @return array Lista de grupos
     */
    public function getGrupos()
    {
        $this->cargarGrupos();
        return $this->grupos;
    }

    /**
     * Carga los grupos del usuario desde la base de datos.
     * @return void
     */
    public function cargarGrupos()
    {
        $this->grupos = [];
        try {
            $conexion = new Conexion("libellus", "db", "miriam", "libreria123");
            // Consulta los grupos a los que pertenece el usuario
            $con = $conexion->getConexion()->prepare("SELECT g.id_grupo, g.nom_grupo, g.img_grupo 
            FROM GRUPO g, PERTENECE p WHERE g.id_grupo = p.id_grupo AND p.nom_usu = :nomUsu ORDER BY g.nom_grupo");
            $con->bindParam(":nomUsu", $this->nomUsu);
            $con->execute();
            $this->grupos = $con->fetchAll(PDO::FETCH_ASSOC);
            $conexion->cerrarConexion();
        } catch (PDOException $ex) {
            error_log("Error BD al cargar grupos para {$this->nomUsu}: " . $ex->getMessage());
        }
    }

    /**
     * Une al usuario actual a un grupo específico.
     * @param int $idGrupo
     * @return bool True si se unió correctamente, false en caso contrario
     */
    public function unirseAGrupo($idGrupo)
    {
        $exito = false;
        // Validar id_grupo
        $idGrupoVal = validarEnteroPositivo($idGrupo);

        if ($idGrupoVal === false) {
            error_log("Intento de unirse a grupo con ID inválido: " . $idGrupo);
            return false;
        }

        if (Grupo::obtenerGrupo($idGrupoVal)) {
            try {
                $fechaUnion = date('Y-m-d');
                $conexion = new Conexion("libellus", "db", "miriam", "libreria123");
                // Inserta la relación en la tabla PERTENECE
                $con = $conexion->getConexion()->prepare("INSERT  INTO PERTENECE (nom_usu, id_grupo, fec_union)VALUES (:nomUsu, :idGrupo, :fecUnion)");
                $con->bindParam(':nomUsu', $this->nomUsu);
                $con->bindParam(':idGrupo', $idGrupoVal);
                $con->bindParam(':fecUnion', $fechaUnion);

                $exito = $con->execute();
                $conexion->cerrarConexion();

                // Recargar la lista de grupos si la inserción tuvo efecto
                if ($con->rowCount() > 0) {
                    $this->cargarGrupos();
                }
            } catch (PDOException $ex) {
                error_log("Error BD al unir usuario {$this->nomUsu} al grupo {$idGrupoVal}: " . $ex->getMessage());
            }
        }

        return $exito;
    }

    /**
     * Saca al usuario actual de un grupo específico.
     * @param int $idGrupo
     * @return bool True si se salió correctamente, false en caso contrario
     */
    public function salirDeGrupo($idGrupo)
    {
        $exito = false;
        $idGrupoVal = filter_var($idGrupo, FILTER_VALIDATE_INT, ["options" => ["min_range" => 1]]);

        if ($idGrupoVal === false) {
            error_log("Intento de salir de grupo con ID inválido: " . $idGrupo);
            return false;
        }

        $grupo = Grupo::obtenerGrupo($idGrupoVal);
        // Solo permite salir si el usuario es el líder del grupo
        if ($grupo && $grupo->getIdLider() === $this->nomUsu) {

            try {
                $conexion = new Conexion("libellus", "db", "miriam", "libreria123");
                // Elimina la relación en la tabla PERTENECE
                $con = $conexion->getConexion()->prepare("DELETE FROM PERTENECE WHERE nom_usu = :nomUsu AND id_grupo = :idGrupo");
                $con->bindParam(":nomUsu", $this->nomUsu);
                $con->bindParam(":idGrupo", $idGrupoVal);
                $con->execute();

                // Éxito si se eliminó al menos una fila
                if ($con->rowCount() > 0) {
                    $exito = true;
                    $this->cargarGrupos(); // Actualizar la lista interna de grupos
                } else {
                    error_log("Usuario {$this->nomUsu} no encontrado en grupo {$idGrupoVal} o grupo inexistente.");
                    $exito = true;
                }
                $conexion->cerrarConexion();
            } catch (PDOException $ex) {
                error_log("Error BD al sacar usuario {$this->nomUsu} del grupo {$idGrupoVal}: " . $ex->getMessage());
            }
        }
        return $exito;
    }


    // Métodos para Libros Guardados


    /**
     * Obtiene la lista de libros guardados por el usuario.
     * @return array Lista de libros guardados
     */
    public function getLibrosGuardados()
    {
        $this->cargarLibrosGuardados();
        return $this->librosGuardados;
    }

    /**
     * Carga los libros guardados del usuario desde la base de datos.
     * @return void
     */
    private function cargarLibrosGuardados()
    {
        $this->librosGuardados = [];
        try {
            $conexion = new Conexion("libellus", "db", "miriam", "libreria123");
            // Consulta los libros guardados por el usuario
            $con = $conexion->getConexion()->prepare(
                "SELECT l.id_libro, l.titulo, l.portada, l.sinopsis, l.fec_publicacion, l.url_compra, g.estado, g.comentario
                 FROM LIBRO l, GUARDA g WHERE l.id_libro = g.id_libro AND g.nom_usu = :nomUsu ORDER BY l.titulo"
            );
            $con->bindParam(":nomUsu", $this->nomUsu);
            $con->execute();
            $this->librosGuardados = $con->fetchAll(PDO::FETCH_ASSOC);
            $conexion->cerrarConexion();
        } catch (PDOException $ex) {
            error_log("Error BD al cargar libros guardados para {$this->nomUsu}: " . $ex->getMessage());
        }
    }



    /**
     * Guarda o actualiza un libro en la lista de guardados del usuario.
     * @param int $idLibro
     * @param string $estado Estado del libro (Completado, Leyendo, Pendiente)
     * @param string|null $comentario Comentario opcional
     * @return bool True si se guardó correctamente, false en caso contrario
     */
    public function guardarLibro($idLibro, $estado, $comentario = null)
    {
        $erroresGuardar = [];
        $estadosValidos = ['Completado', 'Leyendo', 'Pendiente'];
        $idLibroVal = validarEnteroPositivo($idLibro);
        $estadoVal = validarCadena($estado);

        // Validaciones de entrada
        if (!$idLibroVal) {
            $erroresGuardar[] = "Intentó de guardar libro con ID no válido: " . $idLibro;
        }
        if (!$estadoVal || !in_array($estadoVal, $estadosValidos)) {
            $erroresGuardar[] = "Intento de guardar libro con estado inválido: {$estado}";
        }

        if (!empty($erroresGuardar)) {
            error_log(implode(" | ", $erroresGuardar));
        }

        $comentarioVal = is_null($comentario) ? null : validarCadena($comentario, 0, 200);

        if (Libro::verLibro($idLibroVal)) {

            $exito = false;
            try {
                $conexion = new Conexion("libellus", "db", "miriam", "libreria123");
                $con = $conexion->getConexion();

                $con = $con->prepare("INSERT INTO GUARDA (nom_usu, id_libro, comentario, estado)
                    VALUES (:nomUsu, :idLibro, :comentario, :estado) ON DUPLICATE KEY UPDATE comentario = VALUES(comentario), estado = VALUES(estado)");

                $con->bindParam(':nomUsu', $this->nomUsu);
                $con->bindParam(':idLibro', $idLibroVal);
                $con->bindParam(':comentario', $comentarioVal);
                $con->bindParam(':estado', $estadoVal);

                $exito = $con->execute();
                $conexion->cerrarConexion();

                // Recargar la lista de libros guardados si la operación tuvo éxito
                if ($exito) {
                    $this->cargarLibrosGuardados();
                } else {
                    error_log("Error al ejecutar la consulta para guardar libro {$idLibroVal} para {$this->nomUsu}: " . implode(" | ", $con->errorInfo()));
                }
            } catch (PDOException $ex) {
                error_log("Error BD al guardar libro {$idLibroVal} para {$this->nomUsu}: " . $ex->getMessage());
            }
        }
        return $exito;
    }

    /**
     * Elimina un libro de la lista de guardados del usuario.
     * @param int $idLibro
     * @return bool True si se eliminó correctamente, false en caso contrario
     */
    public function eliminarLibroGuardado($idLibro)
    {
        $exito = false;
        $idLibroVal = filter_var($idLibro, FILTER_VALIDATE_INT, ["options" => ["min_range" => 1]]);

        if ($idLibroVal) {
            try {
                $conexion = new Conexion("libellus", "db", "miriam", "libreria123");
                $con = $conexion->getConexion()->prepare("DELETE FROM GUARDA WHERE nom_usu = :nomUsu AND id_libro = :idLibro");
                $con->bindParam(":nomUsu", $this->nomUsu);
                $con->bindParam(":idLibro", $idLibroVal);
                $con->execute();

                // Éxito si se eliminó al menos una fila
                if ($con->rowCount() > 0) {
                    $exito = true;
                    $this->cargarLibrosGuardados();
                } else {
                    error_log("Libro {$idLibroVal} no encontrado en la lista de {$this->nomUsu} o el libro no existe.");
                    $exito = true;
                }
                $conexion->cerrarConexion();
            } catch (PDOException $ex) {
                error_log("Error BD al eliminar libro {$idLibroVal} guardado para {$this->nomUsu}: " . $ex->getMessage());
            }
        } else {
            error_log("Intento de eliminar libro guardado con ID inválido: " . $idLibro);
        }

        return $exito;
    }
}
