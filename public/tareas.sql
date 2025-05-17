-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 18-05-2025 a las 00:31:22
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
-- Base de datos: `tareas`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `colaboradores`
--

CREATE TABLE `colaboradores` (
  `id_tarea` int(11) NOT NULL,
  `id_user` int(11) NOT NULL,
  `estado_colaborador` enum('aceptada','rechazada','pendiente') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `colaboradores`
--

INSERT INTO `colaboradores` (`id_tarea`, `id_user`, `estado_colaborador`) VALUES
(1, 1, 'pendiente'),
(1, 2, 'aceptada');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `colaboradores_subtareas`
--

CREATE TABLE `colaboradores_subtareas` (
  `id_subtarea` int(11) NOT NULL,
  `id_user` int(11) NOT NULL,
  `estado_colaborador` enum('aceptada','rechazada','pendiente') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `notificaciones`
--

CREATE TABLE `notificaciones` (
  `id_notificacion` int(11) NOT NULL,
  `id_usuario_destino` int(11) NOT NULL COMMENT 'ID del usuario que recibe la notificación',
  `tipo_notificacion` enum('invitacion_tarea','invitacion_subtarea','recordatorio_vencimiento','info_general','invitacion_tarea_aceptada','invitacion_tarea_rechazada','invitacion_subtarea_aceptada','invitacion_subtarea_rechazada') NOT NULL,
  `mensaje` varchar(255) NOT NULL,
  `id_entidad_principal` int(11) DEFAULT NULL COMMENT 'ID de la tarea o subtarea a la que se refiere',
  `tipo_entidad_principal` enum('tarea','subtarea') DEFAULT NULL,
  `id_entidad_relacionada` int(11) DEFAULT NULL COMMENT 'Ej: ID de la tarea padre si es notif. de subtarea, o ID del usuario que invita/actúa',
  `tipo_entidad_relacionada` enum('tarea','subtarea','usuario') DEFAULT NULL,
  `datos_adicionales` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'Para datos extra, ej: nombre_tarea, nombre_invitador' CHECK (json_valid(`datos_adicionales`)),
  `leida` tinyint(1) NOT NULL DEFAULT 0 COMMENT '0 = no leída, 1 = leída',
  `fecha_creacion` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `notificaciones`
--

INSERT INTO `notificaciones` (`id_notificacion`, `id_usuario_destino`, `tipo_notificacion`, `mensaje`, `id_entidad_principal`, `tipo_entidad_principal`, `id_entidad_relacionada`, `tipo_entidad_relacionada`, `datos_adicionales`, `leida`, `fecha_creacion`) VALUES
(2, 1, 'invitacion_tarea_aceptada', 'martinaolmo80@gmail.com ha aceptó tu invitación para colaborar en Completar documentacion del proyecto 4.', 1, 'tarea', 2, 'usuario', '{\"nombre_tarea\":\"Completar documentacion del proyecto 4\",\"nombre_invitador\":\"emiliano.gaido07@gmail.com\"}', 0, '2025-05-17 22:21:28');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `subtarea`
--

CREATE TABLE `subtarea` (
  `id_subtarea` int(11) NOT NULL,
  `id_tarea` int(11) NOT NULL,
  `descripcion` varchar(100) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `prioridad` enum('baja','normal','alta') NOT NULL,
  `estado` enum('definida','en_progreso','completada','borrada') NOT NULL,
  `fecha_vencimiento` date NOT NULL,
  `comentario` varchar(30) NOT NULL,
  `id_responsable` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `subtarea`
--

INSERT INTO `subtarea` (`id_subtarea`, `id_tarea`, `descripcion`, `nombre`, `prioridad`, `estado`, `fecha_vencimiento`, `comentario`, `id_responsable`) VALUES
(1, 1, 'definir la estructura api para comunicacion entre java y php', 'definir estructura api', 'baja', 'completada', '2025-11-21', 'terminenlo rapido', 1),
(8, 1, 'poner mas campos', 'modificar controlador altas', 'normal', 'completada', '2025-12-12', 'no cambiar nada de logica', 1),
(9, 1, 'sacar datos que son irrelevantes', 'dar de baja funcion mostrar', 'baja', '', '2025-05-21', 'no cambiar nada de logica', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tarea`
--

CREATE TABLE `tarea` (
  `asunto` varchar(100) NOT NULL,
  `descripcion` varchar(100) NOT NULL,
  `prioridad` enum('baja','alta','normal') NOT NULL,
  `estado` enum('definida','en_progreso','completada','borrada','archivada') NOT NULL,
  `fecha_vencimiento` date NOT NULL,
  `fecha_recordatorio` date NOT NULL,
  `color` varchar(20) NOT NULL,
  `id_tarea` int(11) NOT NULL,
  `id_responsable` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `tarea`
--

INSERT INTO `tarea` (`asunto`, `descripcion`, `prioridad`, `estado`, `fecha_vencimiento`, `fecha_recordatorio`, `color`, `id_tarea`, `id_responsable`) VALUES
('Completar documentacion del proyecto 4', 'finalizar la documentacion tecnica para el sprint actual', 'normal', 'en_progreso', '2025-05-15', '2025-05-10', '#221fef', 1, 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuario`
--

CREATE TABLE `usuario` (
  `nombre` varchar(20) NOT NULL,
  `apellido` varchar(20) NOT NULL,
  `correo` varchar(40) NOT NULL,
  `id_user` int(11) NOT NULL,
  `contrasenia` varchar(30) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `usuario`
--

INSERT INTO `usuario` (`nombre`, `apellido`, `correo`, `id_user`, `contrasenia`) VALUES
('emiliano', 'gaido', 'emiliano.gaido07@gmail.com', 1, 'egaido'),
('martina', 'olmo', 'martinaolmo80@gmail.com', 2, 'martina'),
('Pablo Gabriel', 'Gaido Riso', 'pablogaidoriso@hotmail.com.ar', 3, 'pablo1976'),
('tomas', 'fonzi', 'tomascapo09@gmail.com', 4, 'e45382003');

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `colaboradores`
--
ALTER TABLE `colaboradores`
  ADD PRIMARY KEY (`id_tarea`,`id_user`),
  ADD KEY `id_tarea` (`id_tarea`,`id_user`),
  ADD KEY `id_user` (`id_user`);

--
-- Indices de la tabla `colaboradores_subtareas`
--
ALTER TABLE `colaboradores_subtareas`
  ADD PRIMARY KEY (`id_subtarea`,`id_user`),
  ADD KEY `id_subtarea` (`id_subtarea`,`id_user`),
  ADD KEY `id_user` (`id_user`);

--
-- Indices de la tabla `notificaciones`
--
ALTER TABLE `notificaciones`
  ADD PRIMARY KEY (`id_notificacion`),
  ADD KEY `idx_usuario_destino_leida` (`id_usuario_destino`,`leida`,`fecha_creacion`);

--
-- Indices de la tabla `subtarea`
--
ALTER TABLE `subtarea`
  ADD PRIMARY KEY (`id_subtarea`,`id_tarea`),
  ADD KEY `id_subtarea` (`id_subtarea`,`id_tarea`,`id_responsable`),
  ADD KEY `id_tarea` (`id_tarea`),
  ADD KEY `id_responsable` (`id_responsable`);

--
-- Indices de la tabla `tarea`
--
ALTER TABLE `tarea`
  ADD PRIMARY KEY (`id_tarea`),
  ADD KEY `id_tarea` (`id_tarea`,`id_responsable`),
  ADD KEY `id_responsable` (`id_responsable`);

--
-- Indices de la tabla `usuario`
--
ALTER TABLE `usuario`
  ADD PRIMARY KEY (`id_user`),
  ADD KEY `id_user` (`id_user`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `notificaciones`
--
ALTER TABLE `notificaciones`
  MODIFY `id_notificacion` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `subtarea`
--
ALTER TABLE `subtarea`
  MODIFY `id_subtarea` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT de la tabla `tarea`
--
ALTER TABLE `tarea`
  MODIFY `id_tarea` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT de la tabla `usuario`
--
ALTER TABLE `usuario`
  MODIFY `id_user` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `colaboradores`
--
ALTER TABLE `colaboradores`
  ADD CONSTRAINT `colaboradores_ibfk_1` FOREIGN KEY (`id_user`) REFERENCES `usuario` (`id_user`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `colaboradores_ibfk_2` FOREIGN KEY (`id_tarea`) REFERENCES `tarea` (`id_tarea`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `colaboradores_subtareas`
--
ALTER TABLE `colaboradores_subtareas`
  ADD CONSTRAINT `colaboradores_subtareas_ibfk_1` FOREIGN KEY (`id_user`) REFERENCES `usuario` (`id_user`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `colaboradores_subtareas_ibfk_2` FOREIGN KEY (`id_subtarea`) REFERENCES `subtarea` (`id_subtarea`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `notificaciones`
--
ALTER TABLE `notificaciones`
  ADD CONSTRAINT `fk_notificaciones_usuario_destino` FOREIGN KEY (`id_usuario_destino`) REFERENCES `usuario` (`id_user`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `subtarea`
--
ALTER TABLE `subtarea`
  ADD CONSTRAINT `subtarea_ibfk_1` FOREIGN KEY (`id_tarea`) REFERENCES `tarea` (`id_tarea`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `subtarea_ibfk_2` FOREIGN KEY (`id_responsable`) REFERENCES `usuario` (`id_user`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `tarea`
--
ALTER TABLE `tarea`
  ADD CONSTRAINT `tarea_ibfk_1` FOREIGN KEY (`id_responsable`) REFERENCES `usuario` (`id_user`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
