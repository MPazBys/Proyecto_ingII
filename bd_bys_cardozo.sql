-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 21-05-2026 a las 01:34:30
-- Versión del servidor: 10.4.32-MariaDB
-- Versión de PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `bd_bys_cardozo`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `autores`
--

CREATE TABLE `autores` (
  `idAutor` int(11) NOT NULL,
  `nombreAutor` varchar(200) NOT NULL,
  `apellidoAutor` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `autores`
--

INSERT INTO `autores` (`idAutor`, `nombreAutor`, `apellidoAutor`) VALUES
(1, 'Rick', 'Riordan'),
(2, 'Stephen', 'King'),
(3, 'Sarah J.', 'Maas'),
(4, 'Alejandro G.', 'Roemmers'),
(5, 'Hiromi', 'Kawakami'),
(6, 'Kira Breed-Wrisley', 'Scott Cawthon'),
(7, 'Robert', 'Jordan'),
(8, 'Fernando', 'Pessoa'),
(9, 'Paula B.', 'Giménez'),
(10, 'Carissa', 'Broadbent'),
(11, 'Robin', 'Sharma'),
(12, 'Nora', 'Sakavic'),
(13, 'C.S.', 'Pacat'),
(14, 'George R.R.', 'Martin'),
(15, 'J.K.', 'Rowling'),
(16, 'Brandon', 'Sanderson'),
(17, 'John', 'Katzenbach'),
(18, 'Agatha', 'Christie'),
(19, 'Gillian', 'Flynn'),
(20, 'Joël', 'Dicker'),
(21, 'Colleen', 'Hoover'),
(22, 'Ali', 'Hazelwood'),
(23, 'Rebecca', 'Yarros'),
(24, 'Holly', 'Black'),
(25, 'Leigh', 'Bardugo'),
(26, 'Taylor', 'Jenkins Reid'),
(27, 'V.E.', 'Schwab'),
(28, 'Alice', 'Kellen'),
(29, 'Joanne', 'Fluke'),
(30, 'John', 'Green'),
(31, 'Neil', 'Gaiman'),
(32, 'Freida', 'McFadden'),
(33, 'Alex', 'Michaelides'),
(34, 'Lucy', 'Foley'),
(35, 'Shari', 'Lapena'),
(36, 'Mariana', 'Enriquez'),
(37, 'Samanta', 'Schweblin'),
(38, 'Eduardo', 'Sacheri'),
(39, 'Claudia', 'Piñeiro'),
(40, 'Florencia', 'Bonelli'),
(41, 'Jorge Luis', 'Borges'),
(42, 'Julio', 'Cortázar'),
(43, 'Ernesto', 'Sábato'),
(44, 'Adolfo', 'Bioy Casares'),
(45, 'Liliana', 'Bodoc'),
(46, 'Gabriel', 'García Márquez'),
(47, 'Isabel', 'Allende'),
(48, 'Mario', 'Vargas Llosa'),
(49, 'Laura', 'Restrepo'),
(50, 'Juan Rulfo', 'Vizcaíno'),
(51, 'Roberto', 'Bolaño'),
(52, 'Silvia', 'Moreno-Garcia'),
(53, 'Carlos', 'Ruiz Zafón'),
(54, 'Javier', 'Castillo'),
(55, 'Juan', 'Gómez-Jurado'),
(56, 'Eva García', 'Sáenz de Urturi'),
(57, 'Arturo', 'Pérez-Reverte'),
(58, 'Dolores', 'Redondo'),
(59, 'Albert', 'Espinosa'),
(60, 'Elisabet', 'Benavent'),
(61, 'Joana', 'Marcús'),
(62, 'Iria G.', 'Parente');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `categorias`
--

CREATE TABLE `categorias` (
  `idCategoria` int(11) NOT NULL,
  `nombreCategoria` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `categorias`
--

INSERT INTO `categorias` (`idCategoria`, `nombreCategoria`) VALUES
(18, 'Autoayuda'),
(6, 'Ciencia Ficción'),
(12, 'Cocina y Gastronomía'),
(14, 'Educación'),
(1, 'Fantasia'),
(5, 'Ficción'),
(8, 'Infantil'),
(9, 'Juvenil'),
(7, 'Misterio y Suspenso'),
(13, 'Negocios y Economía'),
(17, 'No ficción'),
(16, 'Novela'),
(10, 'Poesía'),
(15, 'Programación'),
(4, 'Romance'),
(11, 'Salud y Bienestar'),
(3, 'Tecnologia'),
(2, 'Terror');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `consultas`
--

CREATE TABLE `consultas` (
  `idConsulta` int(11) NOT NULL,
  `asunto` varchar(100) NOT NULL,
  `mensaje` text NOT NULL,
  `respondido` tinyint(4) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `idPersona` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `detalleventa`
--

CREATE TABLE `detalleventa` (
  `idDetalle` int(11) NOT NULL,
  `idVenta` int(11) NOT NULL,
  `idLibro` int(11) NOT NULL,
  `cantidad` int(11) NOT NULL,
  `precioUnitario` decimal(10,0) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `detalleventa`
--

INSERT INTO `detalleventa` (`idDetalle`, `idVenta`, `idLibro`, `cantidad`, `precioUnitario`) VALUES
(2, 5, 3, 1, 33199),
(3, 6, 4, 1, 33500),
(4, 6, 5, 1, 28000),
(5, 6, 9, 1, 42000),
(6, 7, 4, 1, 33500),
(7, 7, 3, 1, 33199),
(8, 7, 6, 1, 25899),
(9, 8, 5, 1, 28000),
(10, 8, 12, 1, 38100);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `direccion`
--

CREATE TABLE `direccion` (
  `idDireccion` int(11) NOT NULL,
  `calle` varchar(100) NOT NULL,
  `altura` int(11) NOT NULL,
  `pisoDepto` varchar(100) DEFAULT NULL,
  `consideraciones` varchar(500) DEFAULT NULL,
  `idLocalidad` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `estadousuario`
--

CREATE TABLE `estadousuario` (
  `idEstado` int(11) NOT NULL,
  `nombreEstado` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `estadousuario`
--

INSERT INTO `estadousuario` (`idEstado`, `nombreEstado`) VALUES
(0, 'inactivo'),
(1, 'activo');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `etiqueta`
--

CREATE TABLE `etiqueta` (
  `idEtiqueta` int(11) NOT NULL,
  `nombre` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `etiqueta`
--

INSERT INTO `etiqueta` (`idEtiqueta`, `nombre`) VALUES
(1, 'Ninguna'),
(2, 'Destacados'),
(3, 'Novedades');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `formapago`
--

CREATE TABLE `formapago` (
  `idPago` int(11) NOT NULL,
  `nombrePago` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `formapago`
--

INSERT INTO `formapago` (`idPago`, `nombrePago`) VALUES
(1, 'Efectivo'),
(2, 'Tarjeta');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `libros`
--

CREATE TABLE `libros` (
  `idLibro` int(11) NOT NULL,
  `nombreLibro` varchar(100) NOT NULL,
  `idCategoria` int(11) NOT NULL,
  `precioLibro` decimal(10,0) NOT NULL,
  `stockLibro` int(11) NOT NULL,
  `estado` int(11) NOT NULL,
  `descripcionLibro` varchar(1000) NOT NULL,
  `imagenLibro` varchar(100) NOT NULL,
  `idEtiqueta` int(11) NOT NULL,
  `idAutor` int(11) NOT NULL,
  `fechaEdicion` year(4) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `libros`
--

INSERT INTO `libros` (`idLibro`, `nombreLibro`, `idCategoria`, `precioLibro`, `stockLibro`, `estado`, `descripcionLibro`, `imagenLibro`, `idEtiqueta`, `idAutor`, `fechaEdicion`) VALUES
(2, 'CARRIE (50 aniversario)', 2, 38299, 30, 0, 'Carrie, una joven de apariencia insignificante, acosada por sus compañeras de instituto, vive con su madre, una fanática religiosa. Un día en las duchas, la primera menstruación de Carrie provoca las burlas de las demás chicas y desencadena una sucesión de hechos sobrenaturales y terroríficos.', '1749314510_240c40867348ddf7a44f.jpg', 1, 2, '2024'),
(3, 'MR. MERCEDES', 16, 33199, 57, 1, 'Justo antes del amanecer, cientos de parados esperan la apertura de la oficina de empleo.  De pronto, un Mercedes surge de la fría niebla de la madrugada. Su conductor atropella y aplasta a todos los que encuentra a su alcance. El asesino huye dejando atrás ocho muertos y quince heridos.  Meses después, Bill Hodges, un policía jubilado, recibe una carta anónima de alguien que se declara culpable de la masacre.  Brady Hartsfield vive con su madre alcohólica en la casa donde nació. Disfrutó tanto de aquella sensación de muerte debajo de los neumáticos del Mercedes que ahora quiere recuperarla.  ¿Quién es el cazador y quién la presa?', '1749332832_9bc398b3ee848a0e437a.webp', 2, 2, '2014'),
(4, 'LA DIOSA DE TRES CABEZAS', 1, 33500, 2, 1, 'Percy Jackson, Annabeth Chase y Grover Underwood emprenden una nueva aventura con el objetivo de conseguir que Percy entre a la universidad. La diosa Hécate, para dar a Percy su segunda carta de recomendación, le pide que cuide su mansión y sus mascotas, Hécuba (un perro del infierno) y Gale (un turón), durante una semana antes de Halloween. Percy se enfrenta a esta tarea, que promete ser divertida y peligrosa.', '1749353673_59809dfcbd21949a067c.jpg', 2, 1, '2024'),
(5, 'TRONO DE CRISTAL', 1, 28000, 98, 1, 'En las tenebrosas minas de sal de Endovier, una muchacha de dieciocho años cumple cadena perpetua. Es una asesina profesional, la mejor en lo suyo, pero ha cometido un error fatal. La han capturado. El joven capitán Westfall le ofrece un trato: la libertad a cambio de un enorme sacrificio. Celaena debe representar al príncipe en un torneo a muerte, en el que deberá luchar con los asesinos y ladrones más peligrosos del reino. Viva o muerta, Celaena será libre. Tanto si gana como si pierde, está a punto de descubrir su verdadero destino. Pero ¿qué pasará entretanto con su corazón de asesina?', '1749353993_f510f82cacdcacf5d0e4.jpg', 2, 3, '2024'),
(6, 'EL MISTERIO DEL ÚLTIMO STRADIVARIUS', 16, 25899, 89, 1, '\"El misterio del último Stradivarius\" cuenta dos historias paralelas: una histórica que sigue el viaje del último violín construido por Antonio Stradivari y otra contemporánea que investiga un doble asesinato en Paraguay. La historia del violín, que se presenta como un objeto con propiedades mágicas o, al menos, capaz de producir una música sublime, se desarrolla a través de diferentes épocas y personajes. La investigación policial, por otro lado, se centra en un crimen que parece estar relacionado con el violín y sus dueños a lo largo de la historia. ', '1749354223_2cd9f500789d3eca7ad7.webp', 2, 4, '2025'),
(7, 'EL CIELO ES AZUL, LA TIERRA BLANCA', 5, 23999, 150, 1, 'Tsukiko tiene 38 años y lleva una vida solitaria. Considera que no está dotada para el amor. Hasta que un día encuentra en una taberna a su viejo maestro de japonés. Entre ambos se establece un pacto tácito para compartir la soledad. Escogen la misma comida, buscan la compañía del otro y les cuesta separarse, aunque a veces intenten escapar el uno del otro: el maestro, en el recuerdo de la mujer que un día lo abandonó; Tsukiko, en un antiguo compañero de clase.', '1749404843_31d72373e62e22933e64.webp', 1, 5, '2018'),
(8, 'FIVE NIGHTS AT FREDDY\'S: LOS OJOS DE PLATA', 2, 37499, 125, 1, 'Han pasado diez años desde los asesinatos en Freddy Fazbear\'s Pizza, y Charlie ha pasado esos diez años tratando de olvidar. Su padre fue el dueño de Freddy Fazbear\'s Pizza y el creador de estos cuatro animales animatrónicos, y ahora Charlie esta regresando a su ciudad natal para reunirse con sus amigos de infancia. La curiosidad lleva a Charlie y sus amigos a la vieja pizzería que se encuentra oculta y sellada. Descubrieron una entrada, pero las cosas no eran como solían ser: las cuatro mascotas que entretenían y encantaban a los niños habían cambiado. Los animatrónicos tenían un oscuro secreto y una agenda asesina.', '1749405093_0177cf15d0ee054dc367.webp', 3, 6, '2017'),
(9, 'RUEDA DEL TIEMPO 3: EL DRAGÓN RENACIDO', 1, 42000, 74, 1, 'Rand, acosado por inquietantes sueños sobre una espada de cristal, decide abandonar a sus compañeros tras un ataque de Engendros de la Sombra y se encamina hacia Tear para descubrir quién es realmente. Mientras tanto, las tres jóvenes aspirantes a Aes Sedai viajan con Mat hacia Tar Valon para ingresar como novicias en la Torre Blanca, donde esperan que las hermanas sanen a Mat de la extraña enfermedad que padece. Poco tiempo después, la Amyrlin les encomienda una peligrosa misión. . .', '1749431708_9080787a2edd8dab3867.webp', 3, 7, '2019'),
(10, 'LIBRO DE DESASOSIEGO', 16, 36000, 300, 1, 'Ésta es una obra inacabada e inacabable: un universo entero en expansión cuya pluralidad—literaria y vital—es infinita. Bernardo Soares, ayudante de tenedor de libros de contabilidad en la ciudad de Lisboa, autor ficticio de este libro, es, según Pessoa, «un semi-heterónimo, porque, no siendo mía la personalidad, es, no diferente de la mía, sino una simple mutilación de ella».', '1749432155_64bc963d6da5ddf1462b.webp', 3, 8, '2002'),
(11, 'ARDOR', 10, 20600, 78, 1, 'Ardor se pregunta qué es el deseo y dónde anida, cómo azuzarlo y por qué se apaga.  Este libro propone una exploración del deseo, desasida de prejuicios y preconceptos, al tiempo que recopila anécdotas y datos curiosos de la atracción en el reino animal. A través de pequeñas escenas cotidianas, Paula B. Giménez sigue el hilo de la sexualidad multiforme de lo existente, sin inhibiciones ni vergüenza, ampliando el menú de posibilidades cuando dos (o más) cuerpos se encuentran.', '1749432333_81b11f43830e79608de2.webp', 3, 9, '2017'),
(12, 'LA SERPIENTE Y LAS ALAS DE LA NOCHE', 1, 38100, 59, 1, 'Una sola vez cada cien años, se celebra el Kejari, el legendario torneo en honor a la diosa de la muerte, Nyaxia, que reúne a los vampiros de todos los rincones. En esta ocasión, sin embargo, hay una participante de lo más particular: una humana, Oraya, que además es la hija adoptiva del rey de los Nacidos de la Noche.  Aunque lleva entrenándose toda la vida, Oraya está en clara desventaja. Este mundo está diseñado para matarla y este torneo mortal es la peor prueba: deberá competir contra los vampiros más feroces y sanguinarios de todos los pueblos. No obstante, es su única oportunidad para ser algo más que una presa y poder cumplir un sueño oculto.', '1749432737_c62c7721c129dca057cb.jpg', 1, 10, '2024'),
(13, 'EL MONJE QUE VENDIÓ SU FERRARI (bolsillo)', 18, 25999, 27, 1, 'El monje que vendió su Ferrari es una fábula espiritual que, desde hace más de quince años, ha marcado la vida de millones de personas en todo el mundo.  A traves de sus páginas, conocemos la extraordinaria historia de Julian Mantle, un abogado de exito que, tras sufrir un ataque al corazón, debe afrontar el gran vacío de su existencia. Inmerso en esta crisis existencial, Julian toma la radical decisión de vender todas sus pertenencias y viajar a la India. Es en un monasterio del Himalaya donde aprende las sabias y profundas lecciones de los monjes sobre la felicidad, el coraje, el equilibrio y la paz interior.', '1749433031_836017175356298d2340.webp', 3, 11, '2010');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `localidades`
--

CREATE TABLE `localidades` (
  `idLocalidad` int(11) NOT NULL,
  `nombreLocalidad` varchar(200) NOT NULL,
  `idProvincia` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `localidades`
--

INSERT INTO `localidades` (`idLocalidad`, `nombreLocalidad`, `idProvincia`) VALUES
(1, 'La Plata', 1),
(2, 'Mar del Plata', 1),
(3, 'Bahía Blanca', 1),
(4, 'Tandil', 1),
(5, 'Pilar', 1),
(6, 'San Isidro', 1),
(7, 'CABA', 2),
(8, 'San Fernando del Valle de Catamarca', 3),
(9, 'Andalgalá', 3),
(10, 'Belén', 3),
(11, 'Resistencia', 4),
(12, 'Presidencia Roque Sáenz Peña', 4),
(13, 'Villa Ángela', 4),
(14, 'Charata', 4),
(15, 'Rawson', 5),
(16, 'Comodoro Rivadavia', 5),
(17, 'Trelew', 5),
(18, 'Puerto Madryn', 5),
(19, 'Esquel', 5),
(20, 'Córdoba Capital', 6),
(21, 'Villa Carlos Paz', 6),
(22, 'Río Cuarto', 6),
(23, 'San Francisco', 6),
(24, 'Villa María', 6),
(25, 'Corrientes Capital', 7),
(26, 'Goya', 7),
(27, 'Paso de los Libres', 7),
(28, 'Curuzú Cuatiá', 7),
(29, 'Mercedes', 7),
(30, 'Bella Vista', 7),
(31, 'Santo Tomé', 7),
(32, 'Ituzaingó', 7),
(33, 'Esquina', 7),
(34, 'Monte Caseros', 7),
(35, 'Saladas', 7),
(36, 'Paraná', 8),
(37, 'Concordia', 8),
(38, 'Gualeguaychú', 8),
(39, 'Concepción del Uruguay', 8),
(40, 'Formosa Capital', 9),
(41, 'Clorinda', 9),
(42, 'Pirané', 9),
(43, 'San Salvador de Jujuy', 10),
(44, 'San Pedro de Jujuy', 10),
(45, 'Palpalá', 10),
(46, 'Santa Rosa', 11),
(47, 'General Pico', 11),
(48, 'La Rioja Capital', 12),
(49, 'Chilecito', 12),
(50, 'Mendoza Capital', 13),
(51, 'San Rafael', 13),
(52, 'Godoy Cruz', 13),
(53, 'Maipú', 13),
(54, 'Posadas', 14),
(55, 'Puerto Iguazú', 14),
(56, 'Oberá', 14),
(57, 'Eldorado', 14),
(58, 'Neuquén Capital', 15),
(59, 'San Martín de los Andes', 15),
(60, 'Cutral Có', 15),
(61, 'Viedma', 16),
(62, 'San Carlos de Bariloche', 16),
(63, 'General Roca', 16),
(64, 'Cipolletti', 16),
(65, 'Salta Capital', 17),
(66, 'San Ramón de la Nueva Orán', 17),
(67, 'Tartagal', 17),
(68, 'San Juan Capital', 18),
(69, 'Caucete', 18),
(70, 'San Luis Capital', 19),
(71, 'Villa Mercedes', 19),
(72, 'Río Gallegos', 20),
(73, 'Caleta Olivia', 20),
(74, 'El Calafate', 20),
(75, 'Santa Fe Capital', 21),
(76, 'Rosario', 21),
(77, 'Rafaela', 21),
(78, 'Venado Tuerto', 21),
(79, 'Reconquista', 21),
(80, 'Santiago del Estero Capital', 22),
(81, 'La Banda', 22),
(82, 'Termas de Río Hondo', 22),
(83, 'Ushuaia', 23),
(84, 'Río Grande', 23),
(85, 'Tolhuin', 23),
(86, 'San Miguel de Tucumán', 24),
(87, 'Yerba Buena', 24),
(88, 'Concepción', 24);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `perfil`
--

CREATE TABLE `perfil` (
  `idPerfil` int(11) NOT NULL,
  `perfilDescripcion` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `perfil`
--

INSERT INTO `perfil` (`idPerfil`, `perfilDescripcion`) VALUES
(1, 'Administrador'),
(2, 'Cliente');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `persona`
--

CREATE TABLE `persona` (
  `idPersona` int(11) NOT NULL,
  `nombrePersona` varchar(100) NOT NULL,
  `apellidoPersona` varchar(100) NOT NULL,
  `correoPersona` varchar(100) NOT NULL,
  `contrasenia` varchar(500) NOT NULL,
  `idEstado` int(11) NOT NULL,
  `idPerfil` int(11) NOT NULL,
  `dni` int(11) NOT NULL,
  `idDireccion` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `persona`
--

INSERT INTO `persona` (`idPersona`, `nombrePersona`, `apellidoPersona`, `correoPersona`, `contrasenia`, `idEstado`, `idPerfil`, `dni`, `idDireccion`) VALUES
(1, 'Carlos', 'Gómez', 'carlosgomez123@gmail.com', '123Carlos456*', 1, 2, 123, NULL),
(2, 'Paz', 'Bys', 'paz250804@gmail.com', '$2y$10$muOtEmClkWLGa1VjINuEU.wR2TjAV46h.hcIweaCYEHjvXnLlkQjW', 1, 1, 46242480, NULL),
(3, 'Micaela', 'Cardozo', 'micaelacardozo3794@gmail.com', '$2y$10$IHpkC7DNWX3k6lyzfBUeLOWb4SlBN1II9euIzwECgzotDvTI0pMIG', 1, 1, 124, NULL),
(4, 'Victoria', 'Lopez', 'victorialopez12345@gmail.com', '$2y$10$DtY91YzGs4Lo1iip15.OvusoqXfVFAGXkPOhD5bkBNn1h6ioNma7.', 1, 2, 126, NULL),
(6, 'Alex', 'Martin', 'alexM@gmail.com', '$2y$10$KG8UKVi/LPXcYfiQ6me8seRY2c7zz0ONeLYlhUUoWzwOI7ZERiVly', 1, 2, 125, NULL),
(7, 'Alejandro', 'Acosta', 'aleacosta@gmail.com', '$2y$10$YSe/n4uBS9t3vcRQms37xe3Mz5OvE4y9iigEo8EDfdalbhAgXUE5.', 1, 2, 128, NULL),
(8, 'Sofia', 'Fernandez', 'Soffer1@gmail.com', '$2y$10$tZ1P6VjUBITRXIqSoWNYEu32qiZgV193EIS9ldR86XLGkOZU18PAm', 1, 2, 256, NULL),
(9, 'Lorena', 'Galarza', 'lorenaGal@gmail.com', '$2y$10$TQfZj0UV9gjuCxUGQdYKLOL8RB.nuQVPg4DDrno6E9Sc7bRg5VGw6', 1, 2, 782, NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `provincias`
--

CREATE TABLE `provincias` (
  `idProvincia` int(11) NOT NULL,
  `nombreProvincia` varchar(200) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `provincias`
--

INSERT INTO `provincias` (`idProvincia`, `nombreProvincia`) VALUES
(1, 'Buenos Aires'),
(2, 'Ciudad Autónoma de Buenos Aires'),
(3, 'Catamarca'),
(4, 'Chaco'),
(5, 'Chubut'),
(6, 'Córdoba'),
(7, 'Corrientes'),
(8, 'Entre Ríos'),
(9, 'Formosa'),
(10, 'Jujuy'),
(11, 'La Pampa'),
(12, 'La Rioja'),
(13, 'Mendoza'),
(14, 'Misiones'),
(15, 'Neuquén'),
(16, 'Río Negro'),
(17, 'Salta'),
(18, 'San Juan'),
(19, 'San Luis'),
(20, 'Santa Cruz'),
(21, 'Santa Fe'),
(22, 'Santiago del Estero'),
(23, 'Tierra del Fuego, Antártida e Islas del Atlántico Sur'),
(24, 'Tucumán');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `venta`
--

CREATE TABLE `venta` (
  `idVenta` int(11) NOT NULL,
  `fecha` date NOT NULL DEFAULT current_timestamp(),
  `idCliente` int(11) NOT NULL,
  `total` decimal(10,0) NOT NULL,
  `idPago` int(11) NOT NULL,
  `estado` varchar(30) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `venta`
--

INSERT INTO `venta` (`idVenta`, `fecha`, `idCliente`, `total`, `idPago`, `estado`) VALUES
(5, '2025-06-19', 8, 33199, 1, 'Finalizado'),
(6, '2025-06-19', 8, 103500, 2, 'Pendiente'),
(7, '2025-06-19', 9, 92598, 1, 'Pendiente'),
(8, '2025-06-20', 9, 66100, 2, 'Pendiente');

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `autores`
--
ALTER TABLE `autores`
  ADD PRIMARY KEY (`idAutor`);

--
-- Indices de la tabla `categorias`
--
ALTER TABLE `categorias`
  ADD PRIMARY KEY (`idCategoria`),
  ADD UNIQUE KEY `nombreCategoria` (`nombreCategoria`);

--
-- Indices de la tabla `consultas`
--
ALTER TABLE `consultas`
  ADD PRIMARY KEY (`idConsulta`),
  ADD KEY `idPersona` (`idPersona`);

--
-- Indices de la tabla `detalleventa`
--
ALTER TABLE `detalleventa`
  ADD PRIMARY KEY (`idDetalle`),
  ADD KEY `idVenta` (`idVenta`),
  ADD KEY `idLibro` (`idLibro`);

--
-- Indices de la tabla `direccion`
--
ALTER TABLE `direccion`
  ADD PRIMARY KEY (`idDireccion`),
  ADD KEY `idLocalidad` (`idLocalidad`);

--
-- Indices de la tabla `estadousuario`
--
ALTER TABLE `estadousuario`
  ADD PRIMARY KEY (`idEstado`);

--
-- Indices de la tabla `etiqueta`
--
ALTER TABLE `etiqueta`
  ADD PRIMARY KEY (`idEtiqueta`);

--
-- Indices de la tabla `formapago`
--
ALTER TABLE `formapago`
  ADD PRIMARY KEY (`idPago`);

--
-- Indices de la tabla `libros`
--
ALTER TABLE `libros`
  ADD PRIMARY KEY (`idLibro`),
  ADD KEY `idCategoria` (`idCategoria`),
  ADD KEY `etiquetaLibro` (`idEtiqueta`),
  ADD KEY `idAutor` (`idAutor`);

--
-- Indices de la tabla `localidades`
--
ALTER TABLE `localidades`
  ADD PRIMARY KEY (`idLocalidad`),
  ADD KEY `idProvincia` (`idProvincia`);

--
-- Indices de la tabla `perfil`
--
ALTER TABLE `perfil`
  ADD PRIMARY KEY (`idPerfil`);

--
-- Indices de la tabla `persona`
--
ALTER TABLE `persona`
  ADD PRIMARY KEY (`idPersona`),
  ADD UNIQUE KEY `correoPersona` (`correoPersona`),
  ADD UNIQUE KEY `dni` (`dni`),
  ADD KEY `idPerfil` (`idPerfil`),
  ADD KEY `estadoUsuario` (`idEstado`),
  ADD KEY `idDireccion` (`idDireccion`);

--
-- Indices de la tabla `provincias`
--
ALTER TABLE `provincias`
  ADD PRIMARY KEY (`idProvincia`);

--
-- Indices de la tabla `venta`
--
ALTER TABLE `venta`
  ADD PRIMARY KEY (`idVenta`),
  ADD KEY `idCliente` (`idCliente`),
  ADD KEY `formaPago` (`idPago`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `autores`
--
ALTER TABLE `autores`
  MODIFY `idAutor` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=63;

--
-- AUTO_INCREMENT de la tabla `categorias`
--
ALTER TABLE `categorias`
  MODIFY `idCategoria` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT de la tabla `consultas`
--
ALTER TABLE `consultas`
  MODIFY `idConsulta` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de la tabla `detalleventa`
--
ALTER TABLE `detalleventa`
  MODIFY `idDetalle` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT de la tabla `direccion`
--
ALTER TABLE `direccion`
  MODIFY `idDireccion` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `etiqueta`
--
ALTER TABLE `etiqueta`
  MODIFY `idEtiqueta` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `formapago`
--
ALTER TABLE `formapago`
  MODIFY `idPago` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `libros`
--
ALTER TABLE `libros`
  MODIFY `idLibro` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT de la tabla `localidades`
--
ALTER TABLE `localidades`
  MODIFY `idLocalidad` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=89;

--
-- AUTO_INCREMENT de la tabla `persona`
--
ALTER TABLE `persona`
  MODIFY `idPersona` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT de la tabla `provincias`
--
ALTER TABLE `provincias`
  MODIFY `idProvincia` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT de la tabla `venta`
--
ALTER TABLE `venta`
  MODIFY `idVenta` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `consultas`
--
ALTER TABLE `consultas`
  ADD CONSTRAINT `consultas_ibfk_1` FOREIGN KEY (`idPersona`) REFERENCES `persona` (`idPersona`);

--
-- Filtros para la tabla `detalleventa`
--
ALTER TABLE `detalleventa`
  ADD CONSTRAINT `detalleventa_ibfk_1` FOREIGN KEY (`idVenta`) REFERENCES `venta` (`idVenta`),
  ADD CONSTRAINT `detalleventa_ibfk_2` FOREIGN KEY (`idLibro`) REFERENCES `libros` (`idLibro`);

--
-- Filtros para la tabla `direccion`
--
ALTER TABLE `direccion`
  ADD CONSTRAINT `direccion_ibfk_1` FOREIGN KEY (`idLocalidad`) REFERENCES `localidades` (`idLocalidad`);

--
-- Filtros para la tabla `libros`
--
ALTER TABLE `libros`
  ADD CONSTRAINT `libros_ibfk_1` FOREIGN KEY (`idCategoria`) REFERENCES `categorias` (`idCategoria`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `libros_ibfk_2` FOREIGN KEY (`idEtiqueta`) REFERENCES `etiqueta` (`idEtiqueta`),
  ADD CONSTRAINT `libros_ibfk_3` FOREIGN KEY (`idAutor`) REFERENCES `autores` (`idAutor`);

--
-- Filtros para la tabla `localidades`
--
ALTER TABLE `localidades`
  ADD CONSTRAINT `localidades_ibfk_1` FOREIGN KEY (`idProvincia`) REFERENCES `provincias` (`idProvincia`);

--
-- Filtros para la tabla `persona`
--
ALTER TABLE `persona`
  ADD CONSTRAINT `persona_ibfk_1` FOREIGN KEY (`idPerfil`) REFERENCES `perfil` (`idPerfil`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `persona_ibfk_2` FOREIGN KEY (`idEstado`) REFERENCES `estadousuario` (`idEstado`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `persona_ibfk_3` FOREIGN KEY (`idDireccion`) REFERENCES `direccion` (`idDireccion`);

--
-- Filtros para la tabla `venta`
--
ALTER TABLE `venta`
  ADD CONSTRAINT `venta_ibfk_1` FOREIGN KEY (`idCliente`) REFERENCES `persona` (`idPersona`),
  ADD CONSTRAINT `venta_ibfk_2` FOREIGN KEY (`idPago`) REFERENCES `formapago` (`idPago`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
