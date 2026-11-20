-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 13-11-2026 a las 00:59:15
-- Versión del servidor: 10.4.32-MariaDB
-- Versión de PHP: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `habla_sin_voz`
--



-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `cuestionarios`
--

CREATE TABLE `cuestionarios` (
  `id_cuestionario` int(11) NOT NULL,
  `id_leccion` int(11) NOT NULL,
  `titulo` varchar(150) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `cuestionarios`
--

INSERT INTO `cuestionarios` (`id_cuestionario`, `id_leccion`, `titulo`) VALUES
(1, 4, 'Evaluación - Alfabeto dactilológico'),
(2, 7, 'Evaluación - Saludos y presentación');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `cursos`
--

CREATE TABLE `cursos` (
  `id_curso` int(11) NOT NULL,
  `titulo` varchar(150) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `nivel` varchar(50) NOT NULL,
  `id_profesor` int(11) NOT NULL,
  `estado` enum('Borrador','Revisión','Publicado') DEFAULT 'Borrador'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `cursos`
--

INSERT INTO `cursos` (`id_curso`, `titulo`, `descripcion`, `nivel`, `id_profesor`, `estado`) VALUES
(1, 'Alfabeto dactilológico', 'Aprende las 27 letras del alfabeto dactilológico en Lengua de Señas Boliviana (LSB), reconociendo la forma y posición de la mano.', 'Básico 1', 2, 'Publicado'),
(2, 'Saludos y presentación', 'Aprende a saludar, despedirte y realizar presentaciones personales cordiales en Lengua de Señas Boliviana.', 'Básico 1', 2, 'Publicado'),
(3, 'Números y colores', 'Aprende la numeración del 1 al 100 y los colores principales en Lengua de Señas Boliviana para situaciones de la vida diaria.', 'Básico 1', 2, 'Publicado');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `intentos`
--

CREATE TABLE `intentos` (
  `id_intento` int(11) NOT NULL,
  `id_usuario` int(11) NOT NULL,
  `id_cuestionario` int(11) NOT NULL,
  `calificacion` decimal(5,2) DEFAULT NULL,
  `fecha` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `intentos`
--

INSERT INTO `intentos` (`id_intento`, `id_usuario`, `id_cuestionario`, `calificacion`, `fecha`) VALUES
(1, 3, 1, 80.00, '2026-10-28 21:00:00');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `lecciones`
--

CREATE TABLE `lecciones` (
  `id_leccion` int(11) NOT NULL,
  `id_curso` int(11) NOT NULL,
  `titulo` varchar(150) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `orden` int(11) NOT NULL,
  `estado` enum('Pendiente','Publicada','Observada') DEFAULT 'Pendiente'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `lecciones`
--

INSERT INTO `lecciones` (`id_leccion`, `id_curso`, `titulo`, `descripcion`, `orden`, `estado`) VALUES
(1, 1, 'Letras A, B y C', 'Configuración básica de la mano y pulgar para las letras iniciales A, B y C del alfabeto dactilológico.', 1, 'Publicada'),
(2, 1, 'Letras D, E y F', 'Aprende a posicionar el índice y dedos curvos para las letras D, E y F.', 2, 'Publicada'),
(3, 1, 'Letras G, H e I', 'Orientación de muñeca y dedos para las letras G, H e I en LSB.', 3, 'Publicada'),
(4, 1, 'Evaluación práctica de dactilología', 'Cuestionario interactivo de repaso para identificar las letras del alfabeto dactilológico.', 4, 'Publicada'),
(5, 2, 'Saludos de bienvenida (Hola, Buenos días)', 'Señas de cortesía para dar la bienvenida y saludar en diferentes momentos del día.', 1, 'Publicada'),
(6, 2, 'Presentación personal (Mi nombre es)', 'Cómo deletrear tu nombre y expresar presentación formal en LSB.', 2, 'Publicada'),
(7, 2, 'Evaluación - Saludos y presentación', 'Prueba tus conocimientos sobre saludos y presentaciones cotidianas.', 3, 'Publicada'),
(8, 3, 'Números del 1 al 10', 'Aprende a contar del 1 al 10 con la posición correcta de la palma.', 1, 'Publicada'),
(9, 3, 'Colores primarios y secundarios', 'Señas para rojo, azul, amarillo, verde, blanco y negro.', 2, 'Publicada');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `multimedia`
--

CREATE TABLE `multimedia` (
  `id_multimedia` int(11) NOT NULL,
  `ruta_enlace` varchar(255) NOT NULL,
  `tipo` enum('Video','Imagen','Audio','Enlace') NOT NULL,
  `tamano` int(11) DEFAULT NULL,
  `id_leccion` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `multimedia`
--

INSERT INTO `multimedia` (`id_multimedia`, `ruta_enlace`, `tipo`, `tamano`, `id_leccion`) VALUES
(1, 'assets/img/senas/letra_a.svg', 'Imagen', 4500, 1),
(2, 'https://www.youtube.com/watch?v=dQw4w9WgXcQ', 'Enlace', 0, 1),
(3, 'assets/img/senas/letra_b.svg', 'Imagen', 4200, 2),
(4, 'assets/img/senas/saludo.svg', 'Imagen', 4800, 5),
(5, 'assets/img/senas/numeros.svg', 'Imagen', 5100, 8);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `opciones`
--

CREATE TABLE `opciones` (
  `id_opcion` int(11) NOT NULL,
  `id_pregunta` int(11) NOT NULL,
  `texto_opcion` varchar(255) NOT NULL,
  `es_correcta` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `opciones`
--

INSERT INTO `opciones` (`id_opcion`, `id_pregunta`, `texto_opcion`, `es_correcta`) VALUES
(1, 1, 'Opción A: Puño cerrado con pulgar apoyado vertical al lateral', 1),
(2, 1, 'Opción B: Palma abierta con dedos separados', 0),
(3, 1, 'Opción C: Mano en forma de garra curvada', 0),
(4, 1, 'Opción D: Dos dedos apuntando hacia el suelo', 0),
(5, 2, 'Opción A', 0),
(6, 2, 'Opción B', 1),
(7, 2, 'Opción C', 0),
(8, 2, 'Opción D', 0),
(9, 3, 'Opción A: El pulgar extendido', 0),
(10, 3, 'Opción B: El dedo índice', 1),
(11, 3, 'Opción C: El dedo meñique', 0),
(12, 3, 'Opción D: El dedo anular', 0),
(13, 4, 'Opción A: Totalmente cerrados en puño', 0),
(14, 4, 'Opción B: Cuatro dedos juntos extendidos hacia arriba', 1),
(15, 4, 'Opción C: Curvados hacia adentro tocando la muñeca', 0),
(16, 4, 'Opción D: Solo dos dedos levantados en V', 0),
(17, 5, 'Opción A: Letra A', 0),
(18, 5, 'Opción B: Letra O', 0),
(19, 5, 'Opción C: Letra C', 1),
(20, 5, 'Opción D: Letra E', 0),
(21, 6, 'Opción A: Movimiento oscilante con la palma hacia el frente', 1),
(22, 6, 'Opción B: Golpeando el pecho dos veces', 0),
(23, 6, 'Opción C: Puño cerrado estático en el hombro', 0),
(24, 6, 'Opción D: Señalando con el índice al suelo', 0),
(25, 7, 'Opción A: A la altura del pecho proyectando hacia adelante', 1),
(26, 7, 'Opción B: Detrás de la cabeza', 0),
(27, 7, 'Opción C: Solo en la frente', 0),
(28, 7, 'Opción D: En la cintura con ambas manos', 0);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `preguntas`
--

CREATE TABLE `preguntas` (
  `id_pregunta` int(11) NOT NULL,
  `id_cuestionario` int(11) NOT NULL,
  `texto_pregunta` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `preguntas`
--

INSERT INTO `preguntas` (`id_pregunta`, `id_cuestionario`, `texto_pregunta`) VALUES
(1, 1, '¿Cuál es la configuración básica del pulgar para representar la letra A?'),
(2, 1, '¿Qué letra representa esta seña?'),
(3, 1, '¿Qué dedo se extiende verticalmente hacia arriba para representar la letra D?'),
(4, 1, 'Para la letra B, ¿cómo se posicionan los cuatro dedos?'),
(5, 1, '¿Qué letra se forma curvando los dedos en forma de media luna?'),
(6, 2, '¿Cuál es el movimiento de la mano para la seña de \"Hola\"?'),
(7, 2, '¿A qué altura se realiza la seña de \"Buenos días\"?');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `progreso`
--

CREATE TABLE `progreso` (
  `id_progreso` int(11) NOT NULL,
  `id_usuario` int(11) NOT NULL,
  `id_leccion` int(11) NOT NULL,
  `completada` tinyint(1) DEFAULT 1,
  `fecha_completado` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `progreso`
--

INSERT INTO `progreso` (`id_progreso`, `id_usuario`, `id_leccion`, `completada`, `fecha_completado`) VALUES
(1, 3, 1, 1, '2026-10-25 14:30:00'),
(2, 3, 2, 1, '2026-10-26 18:15:00'),
(3, 3, 3, 1, '2026-10-27 20:45:00'),
(4, 3, 5, 1, '2026-10-28 15:20:00');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `reportes`
--

CREATE TABLE `reportes` (
  `id_reporte` int(11) NOT NULL,
  `id_usuario` int(11) NOT NULL,
  `id_leccion` int(11) NOT NULL,
  `comentario` text NOT NULL,
  `respuesta` text DEFAULT NULL,
  `estado` enum('Pendiente','Resuelto') DEFAULT 'Pendiente',
  `fecha_reporte` timestamp NOT NULL DEFAULT current_timestamp(),
  `fecha_respuesta` timestamp NULL DEFAULT NULL,
  `id_profesor_respuesta` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Configuración del límite máximo de carga multimedia (MB)
--

CREATE TABLE `configuracion_subidas` (
  `id_config` int(11) NOT NULL,
  `limite_mb` int(11) NOT NULL DEFAULT 50,
  `actualizado_en` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT IGNORE INTO `configuracion_subidas` (`id_config`, `limite_mb`) VALUES (1, 50);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `roles`
--

CREATE TABLE `roles` (
  `id_rol` int(11) NOT NULL,
  `nombre_rol` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `roles`
--

INSERT INTO `roles` (`id_rol`, `nombre_rol`) VALUES
(1, 'Estudiante'),
(2, 'Profesor'),
(3, 'Administrador');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `solicitudes_profesor`
--

CREATE TABLE `solicitudes_profesor` (
  `id_solicitud` int(11) NOT NULL,
  `id_usuario` int(11) NOT NULL,
  `experiencia` text DEFAULT NULL,
  `estado` enum('Pendiente','Aprobada','Rechazada') DEFAULT 'Pendiente',
  `documento` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--

CREATE TABLE `usuarios` (
  `id_usuario` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `correo` varchar(100) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `id_rol` int(11) NOT NULL,
  `estado` enum('Activo','Suspendido') NOT NULL DEFAULT 'Activo'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Tabla para asignar uno o varios profesores a cada curso
--

CREATE TABLE `curso_profesores` (
  `id_curso` int(11) NOT NULL,
  `id_profesor` int(11) NOT NULL,
  `fecha_asignacion` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id_curso`,`id_profesor`),
  KEY `idx_curso_profesores_profesor` (`id_profesor`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `curso_estudiantes` (
  `id_curso` int(11) NOT NULL,
  `id_usuario` int(11) NOT NULL,
  `fecha_inscripcion` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id_curso`,`id_usuario`),
  KEY `idx_curso_estudiantes_usuario` (`id_usuario`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `usuarios`
--

INSERT INTO `usuarios` (`id_usuario`, `nombre`, `correo`, `password_hash`, `id_rol`) VALUES
(1, 'Administrador del Sistema', 'admin@hablasinvoz.bo', '$2y$10$ngpYu1nmNP2tPGZV.HTUROK3o6mmBBdJ2sw.re4hM3PwPLuerSHlu', 3),
(2, 'Prof. Carlos Mendoza', 'profesor@hablasinvoz.bo', '$2y$10$s3pjU.HybMB9mynxWOWb1uqY/PIYLMahbFtD4R6ST3/jl5LajRA66', 2),
(3, 'Juan Pérez', 'correo@ejemplo.com', '$2y$10$NiFb3aSOPBTKkuFE2rfPeOrC7GssIBWjkHeYZpCymTdchnWkUGzP2', 1),
(5, 'Test Nombre', 'test1794527402@test.com', 'hash123', 1),
(6, 'Jose Veimar Choque Morales', 'josechoque855@gmail.com', '$2y$10$LvLvHczF305WGcKIAtjSRejEdvQaW00Ua9EQnWIAwMkyjiewQYFHe', 1);

--
-- Preservar el profesor principal de cada curso existente
--
INSERT IGNORE INTO `curso_profesores` (`id_curso`, `id_profesor`)
SELECT `id_curso`, `id_profesor` FROM `cursos`;

--
-- Registrar como participantes a estudiantes con progreso o intentos existentes
--
INSERT IGNORE INTO `curso_estudiantes` (`id_curso`, `id_usuario`)
SELECT DISTINCT l.`id_curso`, p.`id_usuario`
FROM `progreso` p
JOIN `lecciones` l ON l.`id_leccion` = p.`id_leccion`
JOIN `usuarios` u ON u.`id_usuario` = p.`id_usuario`
WHERE p.`completada` = 1 AND u.`id_rol` = 1
UNION
SELECT DISTINCT l.`id_curso`, i.`id_usuario`
FROM `intentos` i
JOIN `cuestionarios` q ON q.`id_cuestionario` = i.`id_cuestionario`
JOIN `lecciones` l ON l.`id_leccion` = q.`id_leccion`
JOIN `usuarios` u ON u.`id_usuario` = i.`id_usuario`
WHERE u.`id_rol` = 1;

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `cuestionarios`
--
ALTER TABLE `cuestionarios`
  ADD PRIMARY KEY (`id_cuestionario`),
  ADD KEY `id_leccion` (`id_leccion`);

--
-- Indices de la tabla `cursos`
--
ALTER TABLE `cursos`
  ADD PRIMARY KEY (`id_curso`),
  ADD KEY `id_profesor` (`id_profesor`);

--
-- Indices de la tabla `intentos`
--
ALTER TABLE `intentos`
  ADD PRIMARY KEY (`id_intento`),
  ADD KEY `id_usuario` (`id_usuario`),
  ADD KEY `id_cuestionario` (`id_cuestionario`);

--
-- Indices de la tabla `lecciones`
--
ALTER TABLE `lecciones`
  ADD PRIMARY KEY (`id_leccion`),
  ADD KEY `id_curso` (`id_curso`);

--
-- Indices de la tabla `multimedia`
--
ALTER TABLE `multimedia`
  ADD PRIMARY KEY (`id_multimedia`),
  ADD KEY `id_leccion` (`id_leccion`);

--
-- Indices de la tabla `opciones`
--
ALTER TABLE `opciones`
  ADD PRIMARY KEY (`id_opcion`),
  ADD KEY `id_pregunta` (`id_pregunta`);

--
-- Indices de la tabla `preguntas`
--
ALTER TABLE `preguntas`
  ADD PRIMARY KEY (`id_pregunta`),
  ADD KEY `id_cuestionario` (`id_cuestionario`);

--
-- Indices de la tabla `progreso`
--
ALTER TABLE `progreso`
  ADD PRIMARY KEY (`id_progreso`),
  ADD KEY `id_usuario` (`id_usuario`),
  ADD KEY `id_leccion` (`id_leccion`);

--
-- Indices de la tabla `reportes`
--
ALTER TABLE `reportes`
  ADD PRIMARY KEY (`id_reporte`),
  ADD KEY `id_usuario` (`id_usuario`),
  ADD KEY `id_leccion` (`id_leccion`),
  ADD KEY `id_profesor_respuesta` (`id_profesor_respuesta`);

--
-- Índice de configuración de cargas
--
ALTER TABLE `configuracion_subidas`
  ADD PRIMARY KEY (`id_config`);

--
-- Indices de la tabla `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id_rol`);

--
-- Indices de la tabla `solicitudes_profesor`
--
ALTER TABLE `solicitudes_profesor`
  ADD PRIMARY KEY (`id_solicitud`),
  ADD KEY `id_usuario` (`id_usuario`);

--
-- Indices de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id_usuario`),
  ADD UNIQUE KEY `correo` (`correo`),
  ADD KEY `id_rol` (`id_rol`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `cuestionarios`
--
ALTER TABLE `cuestionarios`
  MODIFY `id_cuestionario` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `cursos`
--
ALTER TABLE `cursos`
  MODIFY `id_curso` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `intentos`
--
ALTER TABLE `intentos`
  MODIFY `id_intento` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `lecciones`
--
ALTER TABLE `lecciones`
  MODIFY `id_leccion` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT de la tabla `multimedia`
--
ALTER TABLE `multimedia`
  MODIFY `id_multimedia` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de la tabla `opciones`
--
ALTER TABLE `opciones`
  MODIFY `id_opcion` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=29;

--
-- AUTO_INCREMENT de la tabla `preguntas`
--
ALTER TABLE `preguntas`
  MODIFY `id_pregunta` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT de la tabla `progreso`
--
ALTER TABLE `progreso`
  MODIFY `id_progreso` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `reportes`
--
ALTER TABLE `reportes`
  MODIFY `id_reporte` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `roles`
--
ALTER TABLE `roles`
  MODIFY `id_rol` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `solicitudes_profesor`
--
ALTER TABLE `solicitudes_profesor`
  MODIFY `id_solicitud` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id_usuario` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `cuestionarios`
--
ALTER TABLE `cuestionarios`
  ADD CONSTRAINT `cuestionarios_ibfk_1` FOREIGN KEY (`id_leccion`) REFERENCES `lecciones` (`id_leccion`);

--
-- Filtros para la tabla `cursos`
--
ALTER TABLE `cursos`
  ADD CONSTRAINT `cursos_ibfk_1` FOREIGN KEY (`id_profesor`) REFERENCES `usuarios` (`id_usuario`);

--
-- Filtros para la tabla `curso_profesores`
--
ALTER TABLE `curso_profesores`
  ADD CONSTRAINT `fk_curso_profesores_curso` FOREIGN KEY (`id_curso`) REFERENCES `cursos` (`id_curso`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_curso_profesores_usuario` FOREIGN KEY (`id_profesor`) REFERENCES `usuarios` (`id_usuario`) ON DELETE RESTRICT;

ALTER TABLE `curso_estudiantes`
  ADD CONSTRAINT `fk_curso_estudiantes_curso` FOREIGN KEY (`id_curso`) REFERENCES `cursos` (`id_curso`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_curso_estudiantes_usuario` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id_usuario`) ON DELETE CASCADE;

--
-- Filtros para la tabla `intentos`
--
ALTER TABLE `intentos`
  ADD CONSTRAINT `intentos_ibfk_1` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id_usuario`),
  ADD CONSTRAINT `intentos_ibfk_2` FOREIGN KEY (`id_cuestionario`) REFERENCES `cuestionarios` (`id_cuestionario`);

--
-- Filtros para la tabla `lecciones`
--
ALTER TABLE `lecciones`
  ADD CONSTRAINT `lecciones_ibfk_1` FOREIGN KEY (`id_curso`) REFERENCES `cursos` (`id_curso`);

--
-- Filtros para la tabla `multimedia`
--
ALTER TABLE `multimedia`
  ADD CONSTRAINT `multimedia_ibfk_1` FOREIGN KEY (`id_leccion`) REFERENCES `lecciones` (`id_leccion`);

--
-- Filtros para la tabla `opciones`
--
ALTER TABLE `opciones`
  ADD CONSTRAINT `opciones_ibfk_1` FOREIGN KEY (`id_pregunta`) REFERENCES `preguntas` (`id_pregunta`);

--
-- Filtros para la tabla `preguntas`
--
ALTER TABLE `preguntas`
  ADD CONSTRAINT `preguntas_ibfk_1` FOREIGN KEY (`id_cuestionario`) REFERENCES `cuestionarios` (`id_cuestionario`);

--
-- Filtros para la tabla `progreso`
--
ALTER TABLE `progreso`
  ADD CONSTRAINT `progreso_ibfk_1` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id_usuario`),
  ADD CONSTRAINT `progreso_ibfk_2` FOREIGN KEY (`id_leccion`) REFERENCES `lecciones` (`id_leccion`);

--
-- Filtros para la tabla `reportes`
--
ALTER TABLE `reportes`
  ADD CONSTRAINT `reportes_ibfk_1` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id_usuario`),
  ADD CONSTRAINT `reportes_ibfk_2` FOREIGN KEY (`id_leccion`) REFERENCES `lecciones` (`id_leccion`),
  ADD CONSTRAINT `reportes_ibfk_3` FOREIGN KEY (`id_profesor_respuesta`) REFERENCES `usuarios` (`id_usuario`) ON DELETE SET NULL;

--
-- Filtros para la tabla `solicitudes_profesor`
--
ALTER TABLE `solicitudes_profesor`
  ADD CONSTRAINT `solicitudes_profesor_ibfk_1` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id_usuario`);

--
-- Filtros para la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD CONSTRAINT `usuarios_ibfk_1` FOREIGN KEY (`id_rol`) REFERENCES `roles` (`id_rol`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
