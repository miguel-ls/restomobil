-- =================================================================
-- Archivo: almacenes.sql
-- Descripción: Creación de la tabla de almacenes y procedimientos
-- almacenados para el CRUD.
-- =================================================================

--
-- 1. Creación de la tabla `almacenes`
--
CREATE TABLE IF NOT EXISTS `almacenes` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `nombre` VARCHAR(255) NOT NULL,
  `estado` BOOLEAN NOT NULL DEFAULT TRUE, -- 1 para Activo, 0 para Inactivo
  `fecha_creacion` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `fecha_actualizacion` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `fecha_eliminacion` DATETIME NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- 2. Procedimientos Almacenados
--

-- Procedimiento para CREAR un nuevo almacen
DROP PROCEDURE IF EXISTS `sp_crear_almacen`;
DELIMITER $$
CREATE PROCEDURE `sp_crear_almacen`(
    IN p_nombre VARCHAR(255)
)
BEGIN
    INSERT INTO `almacenes` (nombre)
    VALUES (p_nombre);
    SELECT LAST_INSERT_ID() as id;
END$$
DELIMITER ;

-- Procedimiento para ACTUALIZAR un almacen existente
DROP PROCEDURE IF EXISTS `sp_actualizar_almacen`;
DELIMITER $$
CREATE PROCEDURE `sp_actualizar_almacen`(
    IN p_id INT,
    IN p_nombre VARCHAR(255),
    IN p_estado BOOLEAN
)
BEGIN
    UPDATE `almacenes`
    SET
        nombre = p_nombre,
        estado = p_estado
    WHERE id = p_id;
END$$
DELIMITER ;

-- Procedimiento para DESACTIVAR un almacen (eliminación lógica)
DROP PROCEDURE IF EXISTS `sp_desactivar_almacen`;
DELIMITER $$
CREATE PROCEDURE `sp_desactivar_almacen`(
    IN p_id INT
)
BEGIN
    UPDATE `almacenes`
    SET
        estado = FALSE,
        fecha_eliminacion = NOW()
    WHERE id = p_id;
END$$
DELIMITER ;

-- Procedimiento para LEER almacenes con filtros y paginación
DROP PROCEDURE IF EXISTS `sp_leer_almacenes`;
DELIMITER $$
CREATE PROCEDURE `sp_leer_almacenes`(
    IN p_nombre VARCHAR(255),
    IN p_estado BOOLEAN,
    IN p_offset INT,
    IN p_limit INT
)
BEGIN
    SELECT
        id,
        nombre,
        estado,
        fecha_creacion,
        fecha_eliminacion
    FROM `almacenes`
    WHERE
        (p_nombre IS NULL OR p_nombre = '' OR nombre LIKE CONCAT('%', p_nombre, '%')) AND
        (p_estado IS NULL OR estado = p_estado)
    ORDER BY id DESC
    LIMIT p_offset, p_limit;
END$$
DELIMITER ;

-- Procedimiento para CONTAR el total de almacenes con filtros
DROP PROCEDURE IF EXISTS `sp_contar_almacenes`;
DELIMITER $$
CREATE PROCEDURE `sp_contar_almacenes`(
    IN p_nombre VARCHAR(255),
    IN p_estado BOOLEAN
)
BEGIN
    SELECT COUNT(id) AS total
    FROM `almacenes`
    WHERE
        (p_nombre IS NULL OR p_nombre = '' OR nombre LIKE CONCAT('%', p_nombre, '%')) AND
        (p_estado IS NULL OR estado = p_estado);
END$$
DELIMITER ;


-- Procedimiento para LEER un almacen por su ID
DROP PROCEDURE IF EXISTS `sp_leer_almacen_por_id`;
DELIMITER $$
CREATE PROCEDURE `sp_leer_almacen_por_id`(
    IN p_id INT
)
BEGIN
    SELECT
        id,
        nombre,
        estado
    FROM `almacenes`
    WHERE id = p_id;
END$$
DELIMITER ;