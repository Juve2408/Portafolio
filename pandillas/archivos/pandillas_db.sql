-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 26-10-2026 a las 06:13:54
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
-- Base de datos: `control_pandillas`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `delitos`
--

CREATE TABLE `delitos` (
  `id_delito` int(11) NOT NULL,
  `nombre_delito` varchar(100) DEFAULT NULL,
  `descripcion` varchar(100) DEFAULT NULL,
  `categoria` varchar(100) DEFAULT NULL,
  `gravedad` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `delitos`
--

INSERT INTO `delitos` (`id_delito`, `nombre_delito`, `descripcion`, `categoria`, `gravedad`) VALUES
(1, 'Robo a persona', 'Robo cometido contra una persona', 'Robo', 'Alta'),
(2, 'Robo a vehículo', 'Robo de vehículos', 'Robo', 'Alta'),
(3, 'Robo a casa habitación', 'Robo en domicilio particular', 'Robo', 'Alta'),
(4, 'Robo a comercio', 'Robo en establecimiento comercial', 'Robo', 'Media'),
(5, 'Narcomenudeo', 'Venta de drogas en pequeña escala', 'Narcóticos', 'Alta'),
(6, 'Homicidio', 'Privación de la vida', 'Violencia', 'Extrema'),
(7, 'Extorsiones', 'Amenazas para obtener dinero', 'Extorsión', 'Alta'),
(8, 'Robo a comercio', 'Robo a comercios', 'Robo', 'Media'),
(9, 'Daños', 'Daño a propiedad ajena', 'Daños', 'Media'),
(10, 'Otros Delitos', 'Otros', 'Otros', 'Media');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `faltas`
--

CREATE TABLE `faltas` (
  `id_falta` int(11) NOT NULL,
  `nombre_falta` varchar(100) DEFAULT NULL,
  `descripcion` varchar(100) DEFAULT NULL,
  `categoria` varchar(100) DEFAULT NULL,
  `gravedad` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `faltas`
--

INSERT INTO `faltas` (`id_falta`, `nombre_falta`, `descripcion`, `categoria`, `gravedad`) VALUES
(2, 'Tomar en la vía pública', 'Consumo de alcohol en lugares públicos', 'Alcohólicas', 'Baja'),
(3, 'Riñas', 'Peleas callejeras', 'Violencia', 'Media'),
(4, 'Violencia familiar', 'Violencia dentro del núcleo familiar', 'Violencia', 'Alta'),
(5, 'Consumo de drogas', 'Consumo de sustancias prohibidas', 'Drogas', 'Media'),
(6, 'Daños', 'Daños menores a propiedad', 'Daños', 'Baja'),
(7, 'Escandalizar', 'Alterar el orden público', 'Orden público', 'Baja'),
(8, 'Otras Faltas', 'Otras', 'Orden público', 'Media');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `integrantes`
--

CREATE TABLE `integrantes` (
  `id_integrante` int(11) NOT NULL,
  `id_pandilla` int(11) DEFAULT NULL,
  `nombres` varchar(100) DEFAULT NULL,
  `apellido_paterno` varchar(100) DEFAULT NULL,
  `apellido_materno` varchar(100) DEFAULT NULL,
  `alias` varchar(100) DEFAULT NULL,
  `informacion_adicional` varchar(255) DEFAULT NULL,
  `delitos_asociados` text DEFAULT NULL,
  `faltas_cometidas` text DEFAULT NULL,
  `fecha_nacimiento` date DEFAULT NULL,
  `redes_sociales` varchar(100) DEFAULT NULL,
  `direccion_vivienda` varchar(100) DEFAULT NULL,
  `latitud_vivienda` varchar(100) DEFAULT NULL,
  `longitud_vivienda` varchar(100) DEFAULT NULL,
  `fotografia` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `integrantes`
--

INSERT INTO `integrantes` (`id_integrante`, `id_pandilla`, `nombres`, `apellido_paterno`, `apellido_materno`, `alias`, `informacion_adicional`, `delitos_asociados`, `faltas_cometidas`, `fecha_nacimiento`, `redes_sociales`, `direccion_vivienda`, `latitud_vivienda`, `longitud_vivienda`, `fotografia`) VALUES
(16, 1, 'Luis osbaldo', 'Salasz', 'Gomez', 'Luis', 'Pelo chino', 'Robo a vehículo, Narcomenudeo', 'Riflas, Daños leves', '2004-06-21', 'Nada', 'Sta. Clara 134, Centro Historico, 78000 San Luis Potosí, S.L.P.', '22.1560', '-100.9787', ''),
(17, 7, 'colosio', 'santana', 'garcia', 'el papoi', 'bajito', 'robo a persona', 'riflas', '1999-07-13', 'nada', 'Av. Vasco de Quiroga 745-757, Industrial Aviacion 1ra Secc, 78140 San Luis Potosí, S.L.P.', '22.1732', ' -100.9994', NULL),
(18, 19, 'esdwin', 'garcia', 'Sánchez', 'papoi', 'peligroso', 'Robo a persona, Narcomenudeo, Homicidio, Daños', 'Violencia familiar, Daños leves, Alteración del orden público', '2000-04-20', 'facebook', 'Real del Monte 90, Obispado, 78396 San Luis Potosí, S.L.P.', '22.1388', '-100.9319', NULL),
(19, 1, 'eduaardo', 'sanchez', 'davila', 'edu', '', 'Robo a vehículo, Robo a comercio', 'Tomar en la vía pública, Consumo de drogas', '2005-06-20', 'instagram', 'venusataino carranza', '22.142239', '-100.940221', NULL),
(22, 30, 'jose', 'sanchez', 'Castillo', 'joseph', 'robusto', 'Robo a persona, Narcomenudeo, Otros Delitos', 'Tomar en la vía pública, Riñas, Consumo de drogas', '1987-02-22', 'instagram', 'Calle ignacio lopez', '22.169404', '-100.976114', '693751686e71a.jpg'),
(23, 31, 'Juan Carlos', 'Mendoza', 'M', 'Chacho', 'tatuado', 'Robo a persona, Robo a comercio', 'Tomar en la vía pública, Consumo de drogas', '2000-02-01', 'NA', 'Santos Degollado', '22.144213', '-100.993814', '69375d6878515.jpg');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `pandillas`
--

CREATE TABLE `pandillas` (
  `id_pandilla` int(11) NOT NULL,
  `nombre` varchar(100) DEFAULT NULL,
  `alias` varchar(100) DEFAULT NULL,
  `descripcion` varchar(255) DEFAULT NULL,
  `id_lider_actual` varchar(50) DEFAULT NULL,
  `num_integrantes_aproximado` int(11) DEFAULT NULL,
  `rango_edades` varchar(100) DEFAULT NULL,
  `horario_reunion` varchar(100) DEFAULT NULL,
  `peligrosidad` varchar(100) DEFAULT NULL,
  `redes_sociales` varchar(100) DEFAULT NULL,
  `lugar_reunion` varchar(100) DEFAULT NULL,
  `latitud_reunion` varchar(100) DEFAULT NULL,
  `longitud_reunion` varchar(100) DEFAULT NULL,
  `fecha_registro` datetime DEFAULT current_timestamp(),
  `pandillas_rivales_texto` varchar(100) DEFAULT NULL,
  `zona` varchar(30) NOT NULL,
  `delitos_pandillas` text NOT NULL,
  `faltas_pandillas` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `pandillas`
--

INSERT INTO `pandillas` (`id_pandilla`, `nombre`, `alias`, `descripcion`, `id_lider_actual`, `num_integrantes_aproximado`, `rango_edades`, `horario_reunion`, `peligrosidad`, `redes_sociales`, `lugar_reunion`, `latitud_reunion`, `longitud_reunion`, `fecha_registro`, `pandillas_rivales_texto`, `zona`, `delitos_pandillas`, `faltas_pandillas`) VALUES
(1, 'Los pruebas', 'Tests', 'Pandilla generada por sistema', NULL, 40, NULL, NULL, 'Media', NULL, 'mercado republica', '22.164683310363337', '-100.97749772198226', '2025-12-03 09:34:44', 'pruebas 2', 'centro', 'Robo a vehículo, Homicidio', 'Tomar en la vía pública'),
(7, 'pandilla 2', 'AMLOVERS', '', '0', 30, '20-45', '18:50', 'Extrema', 'Nada', 'Av. Vasco de Quiroga 745-757, Industrial Aviacion 1ra Secc, 78140 San Luis Potosí, S.L.P.', '22.1280', '-100.9907', '2025-12-04 10:57:35', 'los pruebas', 'sur', '', ''),
(8, 'pandilla 3', 'rapidos', '', '0', 20, '30-40', '7:40', 'Alta', 'Nada', 'Calle Circuito Ote 1064_3, Hermenegildo J. Aldana, 78390 San Luis Potosí, S.L.P.', '22.1465', '-100.9393', '2025-12-05 09:28:46', 'los pruebas', 'centro', '', ''),
(18, 'pandilla 3', 'Luis', '', '0', 24, '30-40', '18:50', 'Baja', 'Nada', 'Av. Vasco de Quiroga 745-757, Industrial Aviacion 1ra Secc, 78140 San Luis Potosí, S.L.P.', '22.1560', ' -101.00271575083457', '2025-12-05 10:53:02', 'los pruebas', 'oeste', '', ''),
(19, 'los papois', 'pepe', 'malos', '0', 50, '16-30', '17:20', 'Extrema', 'instagram', 'Bagdad, Obispado, 78396 San Luis Potosí, S.L.P.', '22.1406', '-100.9310', '2025-12-05 11:24:24', 'los pruebas', 'este', 'daños', 'drogas'),
(22, 'los fuertes', 'Fuertes', '', '0', 30, '20-45', '18:50', 'Alta', 'facebook', 'calle prol.ecuador', '22.167225', '-100.941836', '2025-12-08 08:56:15', 'los pruebas', 'sur', 'Robo a persona, Narcomenudeo', 'Tomar en vía pública, Drogas'),
(26, 'pruebas', 'agiles', 'nada', '0', 40, '30-40', '17:20', 'Alta', 'facebook', 'plata', '22.158764', '-101.018814', '2025-12-08 11:06:35', 'los pruebas', 'oeste', 'Robo a vehículo, Homicidio, Daños', 'Tomar en vía pública'),
(29, 'pepes', 'pep', 'Prietos', '0', 123, '20-45', '17:20', 'Media', 'facebook', 'Parque Tangamanga', '22.129732', '-100.995196', '2025-12-08 11:11:13', 'los pruebas', 'oeste', 'Robo a persona, Daños', 'Tomar en vía pública'),
(30, 'pandilla ', 'raperos', 'peligrosos', '0', 55, '15-43', '20:40', 'Extrema', 'instagram', 'Benjamin argumedo 180A', '22.191622', '-101.000245', '2025-12-08 16:26:29', 'los pruebas', 'Norte', 'Robo a persona, Robo a vehículo, Robo a comercio', 'Tomar en vía pública'),
(31, 'Los leones', 'Leones', 'altamente conflictiva', '0', 18, '30-40', '18:50', 'Baja', 'Nada', 'Urbano Villalon 500', '22.122339', '-100.984268', '2025-12-08 17:16:46', 'No aplica', 'Norte', 'Robo a persona, Robo a comercio', 'Tomar en vía pública, Consumo de Drogas');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `rinas_pandilleriles`
--

CREATE TABLE `rinas_pandilleriles` (
  `id_rina` int(11) NOT NULL,
  `pandillas_involucradas` varchar(100) DEFAULT NULL,
  `fecha_rina` date DEFAULT NULL,
  `hora_rina` varchar(100) DEFAULT NULL,
  `ubicacion_rina` varchar(100) DEFAULT NULL,
  `descripcion_hechos` varchar(100) DEFAULT NULL,
  `latitud_rina` varchar(100) DEFAULT NULL,
  `longitud_rina` varchar(100) DEFAULT NULL,
  `id_usuario_registro` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `rinas_pandilleriles`
--

INSERT INTO `rinas_pandilleriles` (`id_rina`, `pandillas_involucradas`, `fecha_rina`, `hora_rina`, `ubicacion_rina`, `descripcion_hechos`, `latitud_rina`, `longitud_rina`, `id_usuario_registro`) VALUES
(1, 'los_mechos, los pruebas', '2025-12-01', '16:25', 'estadio alfonso lastras', 'pelea de pandillas', '22.147243280924155', '-100.94541310599428', 1),
(29, NULL, '1978-12-03', '12:24', 'parque tangamanga', 'pelea', '22.132276', '-101.002062', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `telegram_links`
--

CREATE TABLE `telegram_links` (
  `id` int(11) NOT NULL,
  `telegram_id` bigint(20) DEFAULT NULL,
  `codigo` varchar(10) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `verified` tinyint(1) DEFAULT 0,
  `creado` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `telegram_links`
--

INSERT INTO `telegram_links` (`id`, `telegram_id`, `codigo`, `user_id`, `verified`, `creado`) VALUES
(1, 1215910571, '315656', NULL, 0, '2025-12-03 09:34:14');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--

CREATE TABLE `usuarios` (
  `id_usuario` int(11) NOT NULL,
  `nombre` varchar(100) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `password` varchar(100) DEFAULT NULL,
  `rol` varchar(50) DEFAULT 'admin',
  `telegram_id` bigint(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `usuarios`
--

INSERT INTO `usuarios` (`id_usuario`, `nombre`, `email`, `password`, `rol`, `telegram_id`) VALUES
(1, 'Administrador', 'admin@test.com', '1234', 'admin', 1215910571),
(2, 'Eduardo Juventino ', 'consultor@test.com', '1234', 'consultor', NULL),
(12, 'Edwin Antonio', 'edwin@test.com', '12345', 'admin', NULL),
(13, 'Rafael Llamas', 'rllamas@correo.com', '12345', 'admin', NULL);

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `delitos`
--
ALTER TABLE `delitos`
  ADD PRIMARY KEY (`id_delito`);

--
-- Indices de la tabla `faltas`
--
ALTER TABLE `faltas`
  ADD PRIMARY KEY (`id_falta`);

--
-- Indices de la tabla `integrantes`
--
ALTER TABLE `integrantes`
  ADD PRIMARY KEY (`id_integrante`),
  ADD KEY `id_pandilla` (`id_pandilla`);

--
-- Indices de la tabla `pandillas`
--
ALTER TABLE `pandillas`
  ADD PRIMARY KEY (`id_pandilla`);

--
-- Indices de la tabla `rinas_pandilleriles`
--
ALTER TABLE `rinas_pandilleriles`
  ADD PRIMARY KEY (`id_rina`),
  ADD KEY `id_usuario_registro` (`id_usuario_registro`);

--
-- Indices de la tabla `telegram_links`
--
ALTER TABLE `telegram_links`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_telegram` (`telegram_id`),
  ADD KEY `idx_codigo` (`codigo`);

--
-- Indices de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id_usuario`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `delitos`
--
ALTER TABLE `delitos`
  MODIFY `id_delito` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT de la tabla `faltas`
--
ALTER TABLE `faltas`
  MODIFY `id_falta` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT de la tabla `integrantes`
--
ALTER TABLE `integrantes`
  MODIFY `id_integrante` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT de la tabla `pandillas`
--
ALTER TABLE `pandillas`
  MODIFY `id_pandilla` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=32;

--
-- AUTO_INCREMENT de la tabla `rinas_pandilleriles`
--
ALTER TABLE `rinas_pandilleriles`
  MODIFY `id_rina` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=30;

--
-- AUTO_INCREMENT de la tabla `telegram_links`
--
ALTER TABLE `telegram_links`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id_usuario` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `integrantes`
--
ALTER TABLE `integrantes`
  ADD CONSTRAINT `integrantes_ibfk_1` FOREIGN KEY (`id_pandilla`) REFERENCES `pandillas` (`id_pandilla`);

--
-- Filtros para la tabla `rinas_pandilleriles`
--
ALTER TABLE `rinas_pandilleriles`
  ADD CONSTRAINT `rinas_pandilleriles_ibfk_1` FOREIGN KEY (`id_usuario_registro`) REFERENCES `usuarios` (`id_usuario`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
