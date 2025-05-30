CREATE DATABASE IF NOT EXISTS libellus;

-- Asignar permisos a mi usuario
CREATE USER IF NOT EXISTS 'miriam'@'%' IDENTIFIED BY 'libreria123';
GRANT ALL PRIVILEGES ON libellus.* TO 'miriam'@'%';
FLUSH PRIVILEGES;

USE libellus;
set names 'utf8mb4';

CREATE TABLE USUARIO (
    nom_usu VARCHAR(20) PRIMARY KEY,
    email VARCHAR(100) NOT NULL UNIQUE,
    clave_usu VARCHAR(300) NOT NULL,
    foto_perfil TEXT,
    administrador BOOLEAN DEFAULT FALSE
);

CREATE TABLE GENERO (
    id_genero INT PRIMARY KEY AUTO_INCREMENT,
    nom_genero VARCHAR(50) UNIQUE
);

CREATE TABLE AUTOR (
    id_autor INT PRIMARY KEY AUTO_INCREMENT,
    nom_autor VARCHAR(100) UNIQUE
);

CREATE TABLE LIBRO (
    id_libro INT PRIMARY KEY AUTO_INCREMENT,
    titulo VARCHAR(200),
    portada TEXT,
    sinopsis TEXT,
    fec_publicacion DATE,
    url_compra TEXT
);

CREATE TABLE POSEE (
    id_libro INT,
    id_genero INT,
    PRIMARY KEY (id_libro, id_genero),
    CONSTRAINT fk_posee_libro FOREIGN KEY (id_libro) REFERENCES LIBRO(id_libro) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_posee_genero FOREIGN KEY (id_genero) REFERENCES GENERO(id_genero) ON DELETE CASCADE ON UPDATE CASCADE 
);

CREATE TABLE ESCRIBE (
    id_libro INT,
    id_autor INT,
    PRIMARY KEY (id_libro, id_autor),
    CONSTRAINT fk_escribe_libro FOREIGN KEY (id_libro) REFERENCES LIBRO(id_libro) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_escribe_autor FOREIGN KEY (id_autor) REFERENCES AUTOR(id_autor) ON DELETE CASCADE ON UPDATE CASCADE
);

CREATE TABLE GRUPO (
    id_grupo INT PRIMARY KEY AUTO_INCREMENT,
    nom_grupo VARCHAR(100) UNIQUE,
    img_grupo TEXT,
    clave_grupo VARCHAR(300), 
    descripcion VARCHAR(200),
    id_lider VARCHAR(20) NULL,
    CONSTRAINT fk_grupo_lider FOREIGN KEY (id_lider) REFERENCES USUARIO(nom_usu) ON DELETE CASCADE ON UPDATE CASCADE 
);

CREATE TABLE PERTENECE (
    nom_usu VARCHAR(20),
    id_grupo INT,
    fec_union DATE,
    PRIMARY KEY (nom_usu, id_grupo),
    CONSTRAINT fk_pertenece_usuario FOREIGN KEY (nom_usu) REFERENCES USUARIO(nom_usu) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_pertenece_grupo FOREIGN KEY (id_grupo) REFERENCES GRUPO(id_grupo) ON DELETE CASCADE ON UPDATE CASCADE
);

CREATE TABLE CONTIENE (
    id_grupo INT,
    id_libro INT,
    fecha datetime DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id_grupo, id_libro),
    CONSTRAINT fk_contiene_grupo FOREIGN KEY (id_grupo) REFERENCES GRUPO(id_grupo) ON DELETE CASCADE ON UPDATE CASCADE, 
    CONSTRAINT fk_contiene_libro FOREIGN KEY (id_libro) REFERENCES LIBRO(id_libro) ON DELETE CASCADE ON UPDATE CASCADE
);

CREATE TABLE GUARDA (
    nom_usu VARCHAR(20),
    id_libro INT,
    comentario TEXT,
    estado ENUM('Completado', 'Leyendo', 'Pendiente'),
    PRIMARY KEY (nom_usu, id_libro),
    CONSTRAINT fk_guarda_usuario FOREIGN KEY (nom_usu) REFERENCES USUARIO(nom_usu) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_guarda_libro FOREIGN KEY (id_libro) REFERENCES LIBRO(id_libro) ON DELETE CASCADE ON UPDATE CASCADE
);
