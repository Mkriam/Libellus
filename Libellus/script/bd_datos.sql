USE libellus;
set names 'utf8mb4';

-- Usuarios
INSERT INTO USUARIO (nom_usu, email, clave_usu, foto_perfil, administrador) VALUES
-- clave: Pruebaadmin1 Pruebamiembro1 Mirimiembro1
('PruebaAdmin', 'prueba@admin.es', '$2y$12$66B6TEbza5pQx2WS63AZBuFaiA7NI5z1wKSuObUdtFAO2IXtzROIq', 'https://cdn.unotv.com/images/2023/11/gato-173700-1024x576.jpeg', 1), 
('PruebaMiembro', 'prueba@miembro.es', '$2y$12$j9.o8Ejw9Hk1Sl7aUAuwx.CJEUbYmFjfRqIzUouoORwZTwmrFpOf.', 'https://1.bp.blogspot.com/-maMEBPUnl7w/Uy5H6FMSGSI/AAAAAAAAQms/sIvm1nlcwgg/s1600/1920114_640936095962177_825784182_n.jpg', 0),
('Miri', 'miri@miembro.es', '$2y$12$mNEZkZBF0AcJckaz1BKft.8K3P.3g8boAaIFPJqKdUmMcRbGibUem', 'https://es.mypet.com/wp-content/uploads/sites/23/2021/03/GettyImages-623368750-e1582816063521-1.jpg', 0);

-- Géneros
INSERT INTO GENERO(nom_genero) VALUES
('Fantasía'), ('Ciencia Ficción'), ('Misterio'), ('Romance'), ('Drama'), ('Aventura'), ('Comedia'), ('Terror'), ('Thriller'), ('Histórico'), ('Poesía'), ('Ensayo'), ('Biografía'), ('Autobiografía');

-- Autores
INSERT INTO AUTOR(nom_autor) VALUES
('J.K. Rowling'), ('Agatha Christie'), ('Harper Lee'), ('Suzanne Collins'), ('Paulo Coelho'), ('Gabriel García Márquez'), ('George Orwell'), ('F. Scott Fitzgerald'), ('Fiódor Dostoievski'), ('Frank Herbert'), ('Miguel de Cervantes'), ('Jane Austen');

-- Libros
INSERT INTO LIBRO(titulo, portada, sinopsis, fec_publicacion, url_compra) VALUES
('Harry Potter y la piedra filosofal', 'https://imagessl8.casadellibro.com/a/l/s7/18/9788498386318.webp', 'El día en que cumple once años, Harry Potter descubre que es hijo de dos conocidos hechiceros, de los que ha heredado poderes mágicos. Deberá acudir entonces a una famosa escuela de magia y hechicería: Howards.', '1997-06-26', 'https://www.casadellibro.com/libro-harry-potter-y-la-piedra-filosofal-rustica/9788498386318/2428061'),
('Harry Potter y la cámara secreta', 'https://imagessl5.casadellibro.com/a/l/s7/25/9788498386325.webp', 'Hay una conspiración, Harry Potter. Una conspiración para hacer que este año sucedan las cosas más terribles en el Colegio Hogwarts de Magia y Hechicería..', '2015-03-26', 'https://www.casadellibro.com/libro-harry-potter-y-la-camara-secreta--rustica/9788498386325/2428062'),
('Harry Potter y el prisionero de Azkaban', 'https://imagessl2.casadellibro.com/a/l/s7/32/9788498386332.webp', 'Cuando el autobús noctámbulo irrumpe en una calle oscura y frena con fuertes chirridos delante de Harry, comienza para él un nuevo curso en Hogwarts, lleno de acontecimientos extraordinarios.', '2015-03-26', 'https://www.casadellibro.com/libro-harry-potter-y-el-caliz-de-fuego-harry-potter-4/9788418173110/11459801'),
('Harry Potter y el caliz de fuego', 'https://imagessl0.casadellibro.com/a/l/s7/10/9788418173110.webp', 'Se va a celebrar en Hogwarts el Torneo de los Tres Magos. Sólo los alumnos mayores de diecisiete años pueden participar en esta competición, pero, aun así, Harry sueña con ganarla.', '2015-03-26', 'https://www.casadellibro.com/libro-harry-potter-y-la-camara-secreta--rustica/9788498386325/2428062'),
('Harry Potter y la orden del fenix', 'https://imagessl1.casadellibro.com/a/l/s7/41/9788418173141.webp', 'Son malos tiempos para Hogwarts. Tras el ataque de los dementores a su primo Dudley, Harry Potter comprende que Voldemort no se detendrá ante nada para encontrarlo.', '2015-03-26', 'https://www.casadellibro.com/libro-harry-potter-y-la-orden-del-fenix-harry-potter-5/9788418173141/11459798'),
('Harry Potter y el misterio del príncipe', 'https://imagessl8.casadellibro.com/a/l/s7/58/9788418173158.webp', 'Con dieciseis años cumplidos, Harry inicia el sexto curso en Hogwarts en medio de terribles acontecimientos que asolan Inglaterra.', '2015-03-26', 'https://www.casadellibro.com/libro-harry-potter-y-el-misterio-del-principe-harry-potter-6/9788418173158/11459799'),
('Harry Potter y las reliquias de la muerte', 'https://imagessl0.casadellibro.com/a/l/s7/70/9788498386370.webp', 'Cuando se monta en el sidecar de la moto de Hagrid y se eleva en el cielo, dejando Privet Drive por última vez, Harry Potter sabe que lord Voldemort y sus mortífagos se hallan cerca.', '2015-03-26', 'https://www.casadellibro.com/libro-harry-potter-y-las-reliquias-de-la-muerte-rustica/9788498386370/2532593'),
('El alquimista', 'https://imagessl5.casadellibro.com/a/l/s7/55/9788408304555.webp', 'Cuando quieres algo, todo el Universo conspira para ayudarte a conseguirlo. Una fábula inspiradora sobre la importancia de luchar por tus sueños.', '1988-04-01', 'https://www.casadellibro.com/libro-el-alquimista/9788408304555/16834038'),
('Cien años de soledad', 'https://imagessl7.casadellibro.com/a/l/s7/17/9788466379717.webp', 'Muchos años después, frente al pelotón de fusilamiento, el coronel Aureliano Buendía había de recordar aquella tarde remota en que su padre lo llevó a conocer el hielo.', '1967-05-30', 'https://www.casadellibro.com/libro-cien-anos-de-soledad/9788466379717/16422540'),
('1984', 'https://imagessl4.casadellibro.com/a/l/s7/44/9788499890944.webp', 'En 1984, los ciudadanos de Londres ya no distinguen entre el aspecto privado y público de sus vidas.', '1949-06-08', 'https://www.casadellibro.com/libro-1984/9788445010273/12339998'),
('Orgullo y prejuicio', 'https://imagessl4.casadellibro.com/a/l/s7/34/9788467077834.webp', 'Con la llegada del rico y apuesto señor Darcy a su región, la vida de los Bennet y sus cinco hijas se vuelve del revés.', '1813-01-28', 'https://www.casadellibro.com/libro-orgullo-y-prejuicio/9788467077834/16834296'),
('Crimen y castigo', 'https://imagessl2.casadellibro.com/a/l/s7/22/9788418008122.webp', 'Nadie ha retratado la psicología humana como lo hizo Fiódor Dostoyevski. Su obra, fiel reflejo de una personalidad compleja y atormentada, marca una de las cimas de la narrativa universal.', '1866-01-15', 'https://www.casadellibro.com/libro-crimen-y-castigo/9788418008122/11751229'),
('El gran Gatsby', 'https://imagessl3.casadellibro.com/a/l/s7/63/9788433976963.webp', 'Historia de amor, ambición y tragedia en los años 20 en EE.UU.', '1925-04-10', 'https://www.casadellibro.com/libro-el-gran-gatsby/9788433976963/1997923'),
('Don Quijote de la Mancha', 'https://imagessl6.casadellibro.com/a/l/s7/36/9788491057536.webp', 'La legendaria novela de Miguel de Cervantes sobre un caballero loco y su escudero.', '1605-01-16', 'https://www.casadellibro.com/libro-don-quijote-de-la-mancha--edicion-conmemorativa/9788491057536/16611648'),
('Matar a un ruiseñor', 'https://imagessl8.casadellibro.com/a/l/s7/48/9788417216948.webp', 'Disparad a todos los arrendajos azules que queráis, si podéis acertarles, pero recordad que es un pecado matar a un ruiseñor.', '1960-07-11', 'https://www.casadellibro.com/libro-matar-a-un-ruisenor/9788417216948/12040951'),
('Los juegos del hambre', 'https://imagessl5.casadellibro.com/a/l/s7/65/9788427248465.webp', 'El mundo está observando. Ganar significa fama y riqueza. Perder significa una muerte segura.¡Que empiecen los septuagesimo cuartos juegos del hambre!', '2008-09-14', 'https://www.casadellibro.com/libro-los-juegos-del-hambre-1---los-juegos-del-hambre-edicion-especial-/9788427248465/16444078'),
('Dune', 'https://imagessl2.casadellibro.com/a/l/s7/02/9788466363402.webp', 'Arrakis: un planeta desertico donde el agua es el bien más preciado y, donde llorar a los muertos es el símbolo de máxima prodigalidad.', '1965-08-01', 'https://www.casadellibro.com/libro-dune-las-cronicas-de-dune-1/9788466363402/13577796'),
('Asesinato en el Orient Express', 'https://imagessl3.casadellibro.com/a/l/s7/13/9788467045413.webp', 'Un misterio en un tren.', '1934-01-01', 'https://www.casadellibro.com/libro-asesinato-en-el-orient-express/9788467045413/2575572');

-- Posee
INSERT INTO POSEE (id_libro, id_genero) VALUES
(1, 1), -- Harry Potter y la piedra filosofal - Fantasía
(2, 1), -- Harry Potter y la cámara secreta - Fantasía
(3, 1), -- Harry Potter y el prisionero de Azkaban - Fantasía
(4, 1), -- Harry Potter y el caliz de fuego - Fantasía
(5, 1), -- Harry Potter y la orden del fenix - Fantasía
(6, 1), -- Harry Potter y el misterio del príncipe - Fantasía
(7, 1), -- Harry Potter y las reliquias de la muerte - Fantasía
(8, 1), -- El alquimista - Fantasía 
(9, 2), -- Cien años de soledad - Ciencia Ficción
(10, 2), -- 1984 - Ciencia Ficción
(11, 4), -- Orgullo y prejuicio - Romance
(12, 3), -- Crimen y castigo - Misterio
(12, 5), -- Crimen y castigo - Drama
(13, 4), -- El gran Gatsby - Drama
(14, 6), -- Don Quijote de la Mancha - Aventura
(14, 7), -- Don Quijote de la Mancha - Comedia
(15, 5), -- Matar a un ruiseñor - Drama
(16, 2), -- Los juegos del hambre - Ciencia Ficción
(16, 6), -- Los juegos del hambre - Aventura
(17, 2), -- Dune - Ciencia Ficción 
(17, 6), -- Dune - Aventura
(18, 3); -- Asesinato en el Orient Express - Misterio

-- Escribe
INSERT INTO ESCRIBE (id_libro, id_autor) VALUES
(1, 1), -- Harry Potter y la piedra filosofal - J.K. Rowling
(2, 1), -- Harry Potter y la cámara secreta - J.K. Rowling
(3, 1), -- Harry Potter y el prisionero de Azkaban - J.K. Rowling
(4, 1), -- Harry Potter y el caliz de fuego - J.K. Rowling
(5, 1), -- Harry Potter y la orden del fenix - J.K. Rowling
(6, 1), -- Harry Potter y el misterio del príncipe - J.K. Rowling
(7, 1), -- Harry Potter y las reliquias de la muerte - J.K. Rowling
(8, 5), -- El alquimista - Paulo Coelho
(9, 6), -- Cien años de soledad - Gabriel García Márquez
(10, 7), -- 1984 - George Orwell
(11, 12), -- Orgullo y prejuicio - Jane Austen
(12, 9), -- Crimen y castigo - Fiódor Dostoievski
(13, 8), -- El gran Gatsby - F. Scott Fitzgerald
(14, 11), -- Don Quijote de la Mancha - Miguel de Cervantes
(15, 3), -- Matar a un ruiseñor - Harper Lee
(16, 4), -- Los juegos del hambre - Suzanne Collins
(17, 10), -- Dune - Frank Herbert
(18, 2); -- Asesinato en el Orient Express - Agatha Christie

-- Grupos
INSERT INTO GRUPO (nom_grupo, img_grupo, clave_grupo, descripcion, id_lider) VALUES
('Fans de Harry Potter', 'https://i.pinimg.com/736x/75/a9/21/75a9211851d21ea9b7a0efb1ba6a6866.jpg', '$2y$12$a.w9ao0Dz5GtCcjItQf9SOc7mlCGr1/f8mDiCnqkkLl4Cs0hjBgHG', 'Grupo para fans de los libros de Harry Potter.', 'PruebaMiembro');
-- clave: Harrypotter1

-- Pertenece
INSERT INTO PERTENECE (nom_usu, id_grupo, fec_union) VALUES
('PruebaMiembro', 1, '2020-04-02'),
('Miri', 1, '2020-04-02');

-- Contiene
INSERT INTO CONTIENE (id_grupo, id_libro, fecha) VALUES
(1, 1, '2025-03-16 10:15:00'),
(1, 2, '2025-03-23 18:45:00');

-- Guarda
INSERT INTO GUARDA (nom_usu, id_libro, comentario, estado) VALUES
('PruebaMiembro', 9, 'Un clásico imprescindible.', 'Pendiente'),
('PruebaMiembro', 10, 'Me encantó la edición.', 'Pendiente'),
('PruebaMiembro', 1, 'El inicio de una gran saga.', 'Pendiente'),
('PruebaMiembro', 3, 'Mi libro favorito de la serie.', 'Leyendo'),
('PruebaMiembro', 18, 'Un final sorprendente.', 'Pendiente'),
('Miri', 12, 'Intriga desde la primera página.', 'Leyendo'),
('Miri', 18, 'Un final sorprendente.', 'Pendiente'),
('Miri', 1, 'Intriga desde la primera página.', 'Leyendo');

