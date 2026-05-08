-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 08-05-2026 a las 05:09:10
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
-- Base de datos: `sisgeclin`
--

DELIMITER $$
--
-- Procedimientos
--
CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_registrar_entrada` (IN `p_medicamento_id` INT, IN `p_cantidad` INT, IN `p_motivo` VARCHAR(255), IN `p_usuario_id` INT)   BEGIN
    DECLARE v_cantidad_anterior INT;
    DECLARE v_cantidad_nueva INT;
    
    -- Obtener cantidad actual
    SELECT cantidad INTO v_cantidad_anterior 
    FROM inventario 
    WHERE id = p_medicamento_id;
    
    -- Calcular nueva cantidad
    SET v_cantidad_nueva = v_cantidad_anterior + p_cantidad;
    
    -- Actualizar inventario
    UPDATE inventario 
    SET cantidad = v_cantidad_nueva 
    WHERE id = p_medicamento_id;
    
    -- Registrar movimiento
    INSERT INTO inventario_movimientos 
    (medicamento_id, tipo_movimiento, cantidad, cantidad_anterior, cantidad_nueva, motivo, usuario_id)
    VALUES 
    (p_medicamento_id, 'entrada', p_cantidad, v_cantidad_anterior, v_cantidad_nueva, p_motivo, p_usuario_id);
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_registrar_salida` (IN `p_medicamento_id` INT, IN `p_cantidad` INT, IN `p_motivo` VARCHAR(255), IN `p_usuario_id` INT, IN `p_consulta_id` INT)   BEGIN
    DECLARE v_cantidad_anterior INT;
    DECLARE v_cantidad_nueva INT;
    
    -- Obtener cantidad actual
    SELECT cantidad INTO v_cantidad_anterior 
    FROM inventario 
    WHERE id = p_medicamento_id;
    
    -- Validar stock suficiente
    IF v_cantidad_anterior < p_cantidad THEN
        SIGNAL SQLSTATE '45000' 
        SET MESSAGE_TEXT = 'Stock insuficiente';
    END IF;
    
    -- Calcular nueva cantidad
    SET v_cantidad_nueva = v_cantidad_anterior - p_cantidad;
    
    -- Actualizar inventario
    UPDATE inventario 
    SET cantidad = v_cantidad_nueva 
    WHERE id = p_medicamento_id;
    
    -- Registrar movimiento
    INSERT INTO inventario_movimientos 
    (medicamento_id, tipo_movimiento, cantidad, cantidad_anterior, cantidad_nueva, motivo, consulta_id, usuario_id)
    VALUES 
    (p_medicamento_id, 'salida', p_cantidad, v_cantidad_anterior, v_cantidad_nueva, p_motivo, p_consulta_id, p_usuario_id);
END$$

DELIMITER ;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `bitacora`
--

CREATE TABLE `bitacora` (
  `id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `accion` varchar(100) NOT NULL,
  `tabla` varchar(50) DEFAULT NULL,
  `registro_id` int(11) DEFAULT NULL,
  `detalles` text DEFAULT NULL,
  `ip` varchar(45) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `bitacora`
--

INSERT INTO `bitacora` (`id`, `usuario_id`, `accion`, `tabla`, `registro_id`, `detalles`, `ip`, `created_at`) VALUES
(1, 1, 'INICIO SESIÓN', 'usuarios', 1, NULL, '127.0.0.1', '2026-05-08 03:04:52'),
(2, 1, 'CREAR USUARIO', 'usuarios', 4, 'Creó el usuario \'Blacke2f\' con rol admin', '127.0.0.1', '2026-05-08 03:05:52'),
(3, 1, 'DESACTIVAR USUARIO', 'usuarios', 2, 'desactivó el usuario \'medico\'', '127.0.0.1', '2026-05-08 03:06:03'),
(4, 1, 'ACTIVAR USUARIO', 'usuarios', 2, 'activó el usuario \'medico\'', '127.0.0.1', '2026-05-08 03:06:06'),
(5, 1, 'ENTRADA INVENTARIO', 'inventario_movimientos', 16, 'Entrada de 25 unidades de Atorvastatina 20mg', '127.0.0.1', '2026-05-08 03:07:04'),
(6, 1, 'ENTRADA INVENTARIO', 'inventario_movimientos', 17, 'Entrada de 30 unidades de Ibuprofeno 400mg', '127.0.0.1', '2026-05-08 03:07:23');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `consultas`
--

CREATE TABLE `consultas` (
  `id` int(11) NOT NULL,
  `paciente_id` int(11) NOT NULL,
  `medico_id` int(11) DEFAULT NULL,
  `empleado_id` int(10) UNSIGNED DEFAULT NULL,
  `fecha_hora` datetime NOT NULL,
  `motivo` text NOT NULL,
  `diagnostico` text DEFAULT NULL,
  `tratamiento` text DEFAULT NULL,
  `estado` enum('pendiente','en_proceso','completada','cancelada') NOT NULL DEFAULT 'pendiente',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `consultas`
--

INSERT INTO `consultas` (`id`, `paciente_id`, `medico_id`, `empleado_id`, `fecha_hora`, `motivo`, `diagnostico`, `tratamiento`, `estado`, `created_at`, `updated_at`) VALUES
(1, 1, NULL, 1, '2026-04-30 23:01:22', 'Control de presión arterial', 'Hipertensión controlada', NULL, 'completada', '2026-05-01 03:01:22', '2026-05-08 03:01:22'),
(2, 2, NULL, 1, '2026-05-02 23:01:22', 'Dolor abdominal', 'Gastritis aguda', NULL, 'completada', '2026-05-03 03:01:22', '2026-05-08 03:01:22'),
(3, 3, NULL, 5, '2026-05-04 23:01:22', 'Fiebre y malestar general', 'Infección viral', NULL, 'completada', '2026-05-05 03:01:22', '2026-05-08 03:01:22'),
(4, 1, NULL, 6, '2026-05-06 23:01:22', 'Seguimiento de tratamiento cardiovascular', 'Control de arritmia', NULL, 'en_proceso', '2026-05-07 03:01:22', '2026-05-08 03:01:22'),
(5, 2, NULL, 1, '2026-05-07 23:01:22', 'Consulta de rutina', NULL, NULL, 'pendiente', '2026-05-08 03:01:22', '2026-05-08 03:01:22'),
(6, 4, NULL, 5, '2026-05-07 23:01:22', 'Dolor de cabeza persistente', NULL, NULL, 'pendiente', '2026-05-08 03:01:22', '2026-05-08 03:01:22'),
(7, 5, NULL, 1, '2026-05-08 23:01:22', 'Chequeo general', NULL, NULL, 'pendiente', '2026-05-08 03:01:22', '2026-05-08 03:01:22');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `consultas_medicamentos`
--

CREATE TABLE `consultas_medicamentos` (
  `id` int(11) NOT NULL,
  `consulta_id` int(11) NOT NULL,
  `medicamento_id` int(11) NOT NULL,
  `cantidad` int(11) NOT NULL,
  `indicaciones` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `inventario`
--

CREATE TABLE `inventario` (
  `id` int(11) NOT NULL,
  `nombre_medicamento` varchar(150) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `cantidad` int(11) NOT NULL DEFAULT 0,
  `unidad` varchar(30) NOT NULL DEFAULT 'unidades',
  `precio` decimal(10,2) NOT NULL DEFAULT 0.00,
  `fecha_vencimiento` date DEFAULT NULL,
  `proveedor` varchar(150) DEFAULT NULL,
  `stock_minimo` int(11) NOT NULL DEFAULT 10,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `inventario`
--

INSERT INTO `inventario` (`id`, `nombre_medicamento`, `descripcion`, `cantidad`, `unidad`, `precio`, `fecha_vencimiento`, `proveedor`, `stock_minimo`, `created_at`, `updated_at`) VALUES
(1, 'Paracetamol 500mg', 'Analgésico y antipirético', 200, 'tabletas', 0.50, '2026-12-31', 'Farmacéutica Nacional', 50, '2026-05-08 03:01:23', '2026-05-08 03:01:23'),
(2, 'Amoxicilina 500mg', 'Antibiótico de amplio espectro', 80, 'cápsulas', 1.20, '2026-06-30', 'MedSupply', 20, '2026-05-08 03:01:23', '2026-05-08 03:01:23'),
(3, 'Ibuprofeno 400mg', 'Antiinflamatorio no esteroideo', 45, 'tabletas', 0.75, '2027-10-15', 'Farmacéutica Nacional', 30, '2026-05-08 03:01:23', '2026-05-08 03:08:15'),
(4, 'Omeprazol 20mg', 'Inhibidor de la bomba de protones', 120, 'cápsulas', 0.80, '2026-08-20', 'MedSupply', 40, '2026-05-08 03:01:23', '2026-05-08 03:01:23'),
(5, 'Losartán 50mg', 'Antihipertensivo', 90, 'tabletas', 1.50, '2026-10-15', 'Farmacéutica Nacional', 30, '2026-05-08 03:01:23', '2026-05-08 03:01:23'),
(6, 'Metformina 850mg', 'Antidiabético oral', 150, 'tabletas', 0.60, '2026-11-30', 'MedSupply', 50, '2026-05-08 03:01:23', '2026-05-08 03:01:23'),
(7, 'Atorvastatina 20mg', 'Hipolipemiante', 33, 'tabletas', 2.00, '2029-12-07', 'Farmacéutica Nacional', 25, '2026-05-08 03:01:23', '2026-05-08 03:08:04'),
(8, 'Salbutamol 100mcg', 'Broncodilatador', 45, 'inhaladores', 3.50, '2026-07-15', 'MedSupply', 15, '2026-05-08 03:01:23', '2026-05-08 03:01:23');

--
-- Disparadores `inventario`
--
DELIMITER $$
CREATE TRIGGER `trg_validar_stock_minimo` BEFORE UPDATE ON `inventario` FOR EACH ROW BEGIN
    IF NEW.cantidad < 0 THEN
        SIGNAL SQLSTATE '45000' 
        SET MESSAGE_TEXT = 'La cantidad no puede ser negativa';
    END IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `inventario_movimientos`
--

CREATE TABLE `inventario_movimientos` (
  `id` int(11) NOT NULL,
  `medicamento_id` int(11) NOT NULL,
  `tipo_movimiento` enum('entrada','salida') NOT NULL,
  `cantidad` int(11) NOT NULL,
  `cantidad_anterior` int(11) NOT NULL,
  `cantidad_nueva` int(11) NOT NULL,
  `motivo` varchar(255) DEFAULT NULL,
  `consulta_id` int(11) DEFAULT NULL,
  `usuario_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `inventario_movimientos`
--

INSERT INTO `inventario_movimientos` (`id`, `medicamento_id`, `tipo_movimiento`, `cantidad`, `cantidad_anterior`, `cantidad_nueva`, `motivo`, `consulta_id`, `usuario_id`, `created_at`) VALUES
(1, 1, 'entrada', 200, 0, 200, 'Compra inicial de inventario', NULL, 1, '2026-04-08 03:01:23'),
(2, 2, 'entrada', 100, 0, 100, 'Compra inicial de inventario', NULL, 1, '2026-04-08 03:01:23'),
(3, 3, 'entrada', 50, 0, 50, 'Compra inicial de inventario', NULL, 1, '2026-04-08 03:01:23'),
(4, 4, 'entrada', 150, 0, 150, 'Compra inicial de inventario', NULL, 1, '2026-04-08 03:01:23'),
(5, 5, 'entrada', 100, 0, 100, 'Compra inicial de inventario', NULL, 1, '2026-04-08 03:01:23'),
(6, 6, 'entrada', 200, 0, 200, 'Compra inicial de inventario', NULL, 1, '2026-04-08 03:01:23'),
(7, 7, 'entrada', 50, 0, 50, 'Compra inicial de inventario', NULL, 1, '2026-04-08 03:01:23'),
(8, 8, 'entrada', 60, 0, 60, 'Compra inicial de inventario', NULL, 1, '2026-04-08 03:01:23'),
(9, 2, 'salida', 20, 100, 80, 'Dispensado en consulta', 1, 2, '2026-05-01 03:01:23'),
(10, 3, 'salida', 35, 50, 15, 'Dispensado en consulta', 2, 2, '2026-05-03 03:01:23'),
(11, 4, 'salida', 30, 150, 120, 'Dispensado en consulta', 3, 2, '2026-05-05 03:01:23'),
(12, 5, 'salida', 10, 100, 90, 'Dispensado en consulta', 4, 2, '2026-05-07 03:01:23'),
(13, 6, 'salida', 50, 200, 150, 'Dispensado en consulta', 1, 2, '2026-05-02 03:01:23'),
(14, 7, 'salida', 42, 50, 8, 'Dispensado en consulta', 2, 2, '2026-05-04 03:01:23'),
(15, 8, 'salida', 15, 60, 45, 'Dispensado en consulta', 3, 2, '2026-05-06 03:01:23'),
(16, 7, 'entrada', 25, 8, 33, 'Entrada de inventario', NULL, 1, '2026-05-08 03:07:04'),
(17, 3, 'entrada', 30, 15, 45, 'Entrada de inventario', NULL, 1, '2026-05-08 03:07:23');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `mensajes`
--

CREATE TABLE `mensajes` (
  `id` int(11) NOT NULL,
  `remitente_id` int(11) NOT NULL,
  `destinatario_id` int(11) DEFAULT NULL,
  `asunto` varchar(200) NOT NULL,
  `contenido` text NOT NULL,
  `leido` tinyint(1) NOT NULL DEFAULT 0,
  `es_masivo` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `mensajes`
--

INSERT INTO `mensajes` (`id`, `remitente_id`, `destinatario_id`, `asunto`, `contenido`, `leido`, `es_masivo`, `created_at`) VALUES
(1, 1, 2, 'Bienvenido al sistema', 'Bienvenido al sistema de gestión clínica SISGECLIN. Por favor revisa la documentación.', 1, 0, '2026-05-08 03:01:24'),
(2, 1, 3, 'Recordatorio de reunión', 'Recuerde la reunión de personal mañana a las 9:00 AM.', 0, 0, '2026-05-08 03:01:24'),
(3, 2, 1, 'Solicitud de medicamentos', 'Necesitamos reabastecer el inventario de Ibuprofeno y Atorvastatina.', 1, 0, '2026-05-08 03:01:24');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `nomina`
--

CREATE TABLE `nomina` (
  `id` int(10) UNSIGNED NOT NULL,
  `nombre` varchar(80) NOT NULL,
  `apellido` varchar(80) NOT NULL,
  `cedula` varchar(20) NOT NULL,
  `cargo` varchar(100) NOT NULL,
  `departamento` varchar(100) NOT NULL,
  `salario` decimal(10,2) NOT NULL DEFAULT 0.00,
  `fecha_ingreso` date NOT NULL,
  `telefono` varchar(20) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `estado` enum('activo','inactivo') NOT NULL DEFAULT 'activo',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `nomina`
--

INSERT INTO `nomina` (`id`, `nombre`, `apellido`, `cedula`, `cargo`, `departamento`, `salario`, `fecha_ingreso`, `telefono`, `email`, `estado`, `created_at`, `updated_at`) VALUES
(1, 'Roberto', 'Méndez', '101-0001234-5', 'Médico General', 'Consultas', 85000.00, '2020-01-15', '809-555-1001', 'rmendez@sisgeclin.com', 'activo', '2026-05-08 03:01:22', '2026-05-08 03:01:22'),
(2, 'Ana', 'Torres', '102-0005678-9', 'Enfermera Jefe', 'Enfermería', 45000.00, '2019-06-01', '809-555-1002', 'atorres@sisgeclin.com', 'activo', '2026-05-08 03:01:22', '2026-05-08 03:01:22'),
(3, 'Luis', 'Castillo', '103-0009012-3', 'Recepcionista', 'Administración', 25000.00, '2021-03-10', '809-555-1003', 'lcastillo@sisgeclin.com', 'activo', '2026-05-08 03:01:22', '2026-05-08 03:01:22'),
(4, 'Carmen', 'Díaz', '104-0003456-7', 'Farmacéutica', 'Farmacia', 38000.00, '2020-08-20', '809-555-1004', 'cdiaz@sisgeclin.com', 'activo', '2026-05-08 03:01:22', '2026-05-08 03:01:22'),
(5, 'José', 'Ramírez', '105-0007890-1', 'Pediatra', 'Consultas', 90000.00, '2021-02-01', '809-555-1005', 'jramirez@sisgeclin.com', 'activo', '2026-05-08 03:01:22', '2026-05-08 03:01:22'),
(6, 'Laura', 'Fernández', '106-0004567-8', 'Cardiólogo', 'Consultas', 95000.00, '2020-05-15', '809-555-1006', 'lfernandez@sisgeclin.com', 'activo', '2026-05-08 03:01:22', '2026-05-08 03:01:22');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `pacientes`
--

CREATE TABLE `pacientes` (
  `id` int(11) NOT NULL,
  `nombre` varchar(80) NOT NULL,
  `apellido` varchar(80) NOT NULL,
  `cedula` varchar(20) NOT NULL,
  `telefono` varchar(20) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `direccion` text DEFAULT NULL,
  `fecha_nacimiento` date DEFAULT NULL,
  `genero` enum('M','F','Otro') NOT NULL DEFAULT 'M',
  `tipo_sangre` varchar(5) DEFAULT NULL,
  `alergias` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `pacientes`
--

INSERT INTO `pacientes` (`id`, `nombre`, `apellido`, `cedula`, `telefono`, `email`, `direccion`, `fecha_nacimiento`, `genero`, `tipo_sangre`, `alergias`, `created_at`, `updated_at`) VALUES
(1, 'Juan', 'Pérez', '001-1234567-8', '809-555-0101', 'juan@email.com', NULL, '1985-03-15', 'M', 'O+', NULL, '2026-05-08 03:01:21', '2026-05-08 03:01:21'),
(2, 'María', 'González', '002-9876543-1', '809-555-0202', 'maria@email.com', NULL, '1990-07-22', 'F', 'A+', NULL, '2026-05-08 03:01:21', '2026-05-08 03:01:21'),
(3, 'Carlos', 'Rodríguez', '003-4567890-2', '809-555-0303', NULL, NULL, '1978-11-08', 'M', 'B-', NULL, '2026-05-08 03:01:21', '2026-05-08 03:01:21'),
(4, 'Ana', 'Martínez', '004-1111111-1', '809-555-0404', 'ana@email.com', NULL, '1995-05-20', 'F', 'O+', NULL, '2026-05-08 03:01:21', '2026-05-08 03:01:21'),
(5, 'Pedro', 'López', '005-2222222-2', '809-555-0505', NULL, NULL, '1982-09-10', 'M', 'A-', NULL, '2026-05-08 03:01:21', '2026-05-08 03:01:21');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--

CREATE TABLE `usuarios` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `nombre_completo` varchar(150) NOT NULL,
  `rol` enum('admin','medico','recepcionista') NOT NULL DEFAULT 'recepcionista',
  `activo` tinyint(1) NOT NULL DEFAULT 1,
  `ultimo_acceso` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `usuarios`
--

INSERT INTO `usuarios` (`id`, `username`, `email`, `password`, `nombre_completo`, `rol`, `activo`, `ultimo_acceso`, `created_at`, `updated_at`) VALUES
(1, 'admin', 'admin@sisgeclin.com', '$2y$10$ivKlhRep3mu5Qk9VVdm5OeOgGfpm4i87G0Bo.305CaBZm1Lx1p8DS', 'Administrador Sistema', 'admin', 1, '2026-05-07 23:04:52', '2026-05-08 03:01:21', '2026-05-08 03:04:52'),
(2, 'medico', 'medico@sisgeclin.com', '$2y$10$ivKlhRep3mu5Qk9VVdm5OeOgGfpm4i87G0Bo.305CaBZm1Lx1p8DS', 'Dr. Roberto Méndez', 'medico', 1, NULL, '2026-05-08 03:01:21', '2026-05-08 03:06:06'),
(3, 'recepcion', 'recepcion@sisgeclin.com', '$2y$10$ivKlhRep3mu5Qk9VVdm5OeOgGfpm4i87G0Bo.305CaBZm1Lx1p8DS', 'María Torres', 'recepcionista', 1, NULL, '2026-05-08 03:01:21', '2026-05-08 03:04:39'),
(4, 'Blacke2f', 'enzoeliud1010@gmail.com', '$2y$10$2kZT1ckfaye/rry2JLCoGOT6N8UcvIxCUWZCXNF07rsoikNx4O8ey', 'Enzo Colmenarez', 'admin', 1, NULL, '2026-05-08 03:05:52', '2026-05-08 03:05:52');

-- --------------------------------------------------------

--
-- Estructura Stand-in para la vista `v_consultas_completas`
-- (Véase abajo para la vista actual)
--
CREATE TABLE `v_consultas_completas` (
`id` int(11)
,`paciente_id` int(11)
,`paciente_nombre` varchar(161)
,`paciente_cedula` varchar(20)
,`empleado_id` int(10) unsigned
,`medico_id` int(11)
,`medico_nombre` varchar(161)
,`medico_cargo` varchar(100)
,`fecha_hora` datetime
,`motivo` text
,`diagnostico` text
,`tratamiento` text
,`estado` enum('pendiente','en_proceso','completada','cancelada')
,`created_at` timestamp
,`updated_at` timestamp
);

-- --------------------------------------------------------

--
-- Estructura Stand-in para la vista `v_medicamentos_stock_bajo`
-- (Véase abajo para la vista actual)
--
CREATE TABLE `v_medicamentos_stock_bajo` (
`id` int(11)
,`nombre_medicamento` varchar(150)
,`cantidad` int(11)
,`stock_minimo` int(11)
,`unidad` varchar(30)
,`proveedor` varchar(150)
,`cantidad_faltante` bigint(12)
,`fecha_vencimiento` date
);

-- --------------------------------------------------------

--
-- Estructura Stand-in para la vista `v_medicos_activos`
-- (Véase abajo para la vista actual)
--
CREATE TABLE `v_medicos_activos` (
`id` int(10) unsigned
,`nombre_completo` varchar(161)
,`cedula` varchar(20)
,`cargo` varchar(100)
,`departamento` varchar(100)
,`telefono` varchar(20)
,`email` varchar(100)
,`fecha_ingreso` date
);

-- --------------------------------------------------------

--
-- Estructura Stand-in para la vista `v_movimientos_inventario`
-- (Véase abajo para la vista actual)
--
CREATE TABLE `v_movimientos_inventario` (
`id` int(11)
,`medicamento_id` int(11)
,`nombre_medicamento` varchar(150)
,`tipo_movimiento` enum('entrada','salida')
,`cantidad` int(11)
,`cantidad_anterior` int(11)
,`cantidad_nueva` int(11)
,`motivo` varchar(255)
,`consulta_id` int(11)
,`usuario_id` int(11)
,`usuario_nombre` varchar(150)
,`created_at` timestamp
);

-- --------------------------------------------------------

--
-- Estructura para la vista `v_consultas_completas`
--
DROP TABLE IF EXISTS `v_consultas_completas`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `v_consultas_completas`  AS SELECT `c`.`id` AS `id`, `c`.`paciente_id` AS `paciente_id`, concat(`p`.`nombre`,' ',`p`.`apellido`) AS `paciente_nombre`, `p`.`cedula` AS `paciente_cedula`, `c`.`empleado_id` AS `empleado_id`, `c`.`medico_id` AS `medico_id`, coalesce(concat(`e`.`nombre`,' ',`e`.`apellido`),`u`.`nombre_completo`) AS `medico_nombre`, coalesce(`e`.`cargo`,'Médico') AS `medico_cargo`, `c`.`fecha_hora` AS `fecha_hora`, `c`.`motivo` AS `motivo`, `c`.`diagnostico` AS `diagnostico`, `c`.`tratamiento` AS `tratamiento`, `c`.`estado` AS `estado`, `c`.`created_at` AS `created_at`, `c`.`updated_at` AS `updated_at` FROM (((`consultas` `c` join `pacientes` `p` on(`c`.`paciente_id` = `p`.`id`)) left join `nomina` `e` on(`c`.`empleado_id` = `e`.`id`)) left join `usuarios` `u` on(`c`.`medico_id` = `u`.`id`)) ORDER BY `c`.`fecha_hora` DESC ;

-- --------------------------------------------------------

--
-- Estructura para la vista `v_medicamentos_stock_bajo`
--
DROP TABLE IF EXISTS `v_medicamentos_stock_bajo`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `v_medicamentos_stock_bajo`  AS SELECT `inventario`.`id` AS `id`, `inventario`.`nombre_medicamento` AS `nombre_medicamento`, `inventario`.`cantidad` AS `cantidad`, `inventario`.`stock_minimo` AS `stock_minimo`, `inventario`.`unidad` AS `unidad`, `inventario`.`proveedor` AS `proveedor`, `inventario`.`stock_minimo`- `inventario`.`cantidad` AS `cantidad_faltante`, `inventario`.`fecha_vencimiento` AS `fecha_vencimiento` FROM `inventario` WHERE `inventario`.`cantidad` <= `inventario`.`stock_minimo` ORDER BY `inventario`.`cantidad` ASC ;

-- --------------------------------------------------------

--
-- Estructura para la vista `v_medicos_activos`
--
DROP TABLE IF EXISTS `v_medicos_activos`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `v_medicos_activos`  AS SELECT `nomina`.`id` AS `id`, concat(`nomina`.`nombre`,' ',`nomina`.`apellido`) AS `nombre_completo`, `nomina`.`cedula` AS `cedula`, `nomina`.`cargo` AS `cargo`, `nomina`.`departamento` AS `departamento`, `nomina`.`telefono` AS `telefono`, `nomina`.`email` AS `email`, `nomina`.`fecha_ingreso` AS `fecha_ingreso` FROM `nomina` WHERE `nomina`.`estado` = 'activo' AND `nomina`.`cargo` in ('Médico General','Pediatra','Cardiólogo','Cirujano','Ginecólogo','Obstetra','Neurólogo','Traumatólogo','Oftalmólogo','Otorrinolaringólogo','Dermatólogo','Urólogo','Psiquiatra','Anestesiólogo','Radiólogo','Patólogo','Oncólogo','Endocrinólogo','Gastroenterólogo','Nefrólogo','Neumólogo','Reumatólogo','Hematólogo','Infectólogo','Internista') ORDER BY `nomina`.`nombre` ASC, `nomina`.`apellido` ASC ;

-- --------------------------------------------------------

--
-- Estructura para la vista `v_movimientos_inventario`
--
DROP TABLE IF EXISTS `v_movimientos_inventario`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `v_movimientos_inventario`  AS SELECT `m`.`id` AS `id`, `m`.`medicamento_id` AS `medicamento_id`, `i`.`nombre_medicamento` AS `nombre_medicamento`, `m`.`tipo_movimiento` AS `tipo_movimiento`, `m`.`cantidad` AS `cantidad`, `m`.`cantidad_anterior` AS `cantidad_anterior`, `m`.`cantidad_nueva` AS `cantidad_nueva`, `m`.`motivo` AS `motivo`, `m`.`consulta_id` AS `consulta_id`, `m`.`usuario_id` AS `usuario_id`, `u`.`nombre_completo` AS `usuario_nombre`, `m`.`created_at` AS `created_at` FROM ((`inventario_movimientos` `m` join `inventario` `i` on(`m`.`medicamento_id` = `i`.`id`)) join `usuarios` `u` on(`m`.`usuario_id` = `u`.`id`)) ORDER BY `m`.`created_at` DESC ;

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `bitacora`
--
ALTER TABLE `bitacora`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_usuario` (`usuario_id`),
  ADD KEY `idx_accion` (`accion`),
  ADD KEY `idx_tabla` (`tabla`),
  ADD KEY `idx_fecha` (`created_at`);

--
-- Indices de la tabla `consultas`
--
ALTER TABLE `consultas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_paciente` (`paciente_id`),
  ADD KEY `idx_medico` (`medico_id`),
  ADD KEY `idx_empleado` (`empleado_id`),
  ADD KEY `idx_fecha` (`fecha_hora`),
  ADD KEY `idx_estado` (`estado`),
  ADD KEY `idx_created` (`created_at`);

--
-- Indices de la tabla `consultas_medicamentos`
--
ALTER TABLE `consultas_medicamentos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_consulta` (`consulta_id`),
  ADD KEY `idx_medicamento` (`medicamento_id`);

--
-- Indices de la tabla `inventario`
--
ALTER TABLE `inventario`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_nombre` (`nombre_medicamento`),
  ADD KEY `idx_cantidad` (`cantidad`),
  ADD KEY `idx_stock_minimo` (`stock_minimo`),
  ADD KEY `idx_vencimiento` (`fecha_vencimiento`);

--
-- Indices de la tabla `inventario_movimientos`
--
ALTER TABLE `inventario_movimientos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_medicamento` (`medicamento_id`),
  ADD KEY `idx_tipo` (`tipo_movimiento`),
  ADD KEY `idx_fecha` (`created_at`),
  ADD KEY `idx_consulta` (`consulta_id`),
  ADD KEY `idx_usuario` (`usuario_id`);

--
-- Indices de la tabla `mensajes`
--
ALTER TABLE `mensajes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_remitente` (`remitente_id`),
  ADD KEY `idx_destinatario` (`destinatario_id`),
  ADD KEY `idx_leido` (`leido`),
  ADD KEY `idx_masivo` (`es_masivo`),
  ADD KEY `idx_fecha` (`created_at`);

--
-- Indices de la tabla `nomina`
--
ALTER TABLE `nomina`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `cedula` (`cedula`),
  ADD KEY `idx_cedula` (`cedula`),
  ADD KEY `idx_estado` (`estado`),
  ADD KEY `idx_cargo` (`cargo`),
  ADD KEY `idx_departamento` (`departamento`);

--
-- Indices de la tabla `pacientes`
--
ALTER TABLE `pacientes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `cedula` (`cedula`),
  ADD KEY `idx_cedula` (`cedula`),
  ADD KEY `idx_nombre` (`nombre`,`apellido`),
  ADD KEY `idx_created` (`created_at`);

--
-- Indices de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `idx_username` (`username`),
  ADD KEY `idx_email` (`email`),
  ADD KEY `idx_rol` (`rol`),
  ADD KEY `idx_activo` (`activo`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `bitacora`
--
ALTER TABLE `bitacora`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de la tabla `consultas`
--
ALTER TABLE `consultas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT de la tabla `consultas_medicamentos`
--
ALTER TABLE `consultas_medicamentos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `inventario`
--
ALTER TABLE `inventario`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT de la tabla `inventario_movimientos`
--
ALTER TABLE `inventario_movimientos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT de la tabla `mensajes`
--
ALTER TABLE `mensajes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `nomina`
--
ALTER TABLE `nomina`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de la tabla `pacientes`
--
ALTER TABLE `pacientes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `bitacora`
--
ALTER TABLE `bitacora`
  ADD CONSTRAINT `bitacora_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `consultas`
--
ALTER TABLE `consultas`
  ADD CONSTRAINT `consultas_ibfk_1` FOREIGN KEY (`paciente_id`) REFERENCES `pacientes` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `consultas_ibfk_2` FOREIGN KEY (`medico_id`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `consultas_ibfk_3` FOREIGN KEY (`empleado_id`) REFERENCES `nomina` (`id`);

--
-- Filtros para la tabla `consultas_medicamentos`
--
ALTER TABLE `consultas_medicamentos`
  ADD CONSTRAINT `consultas_medicamentos_ibfk_1` FOREIGN KEY (`consulta_id`) REFERENCES `consultas` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `consultas_medicamentos_ibfk_2` FOREIGN KEY (`medicamento_id`) REFERENCES `inventario` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `inventario_movimientos`
--
ALTER TABLE `inventario_movimientos`
  ADD CONSTRAINT `inventario_movimientos_ibfk_1` FOREIGN KEY (`medicamento_id`) REFERENCES `inventario` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `inventario_movimientos_ibfk_2` FOREIGN KEY (`consulta_id`) REFERENCES `consultas` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `inventario_movimientos_ibfk_3` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `mensajes`
--
ALTER TABLE `mensajes`
  ADD CONSTRAINT `mensajes_ibfk_1` FOREIGN KEY (`remitente_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `mensajes_ibfk_2` FOREIGN KEY (`destinatario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
