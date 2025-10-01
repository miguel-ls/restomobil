-- =================================================================
-- Archivo: impuestos.sql
-- Descripción: Creación de la tabla de impuestos y procedimientos
-- almacenados para el CRUD.
-- =================================================================

--
-- 1. Creación de la tabla `impuestos`
--
CREATE TABLE IF NOT EXISTS `impuestos` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `codigo` CHAR(3) NOT NULL,
  `fecha_inicial` DATE NOT NULL,
  `fecha_final` DATE NULL, -- Modificado para ser opcional
  `valor` DECIMAL(10, 2) NOT NULL,
  `estado` BOOLEAN NOT NULL DEFAULT TRUE, -- 1 para Activo, 0 para Inactivo
  `fecha_creacion` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `fecha_actualizacion` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- 2. Procedimientos Almacenados
--

-- Procedimiento para CREAR un nuevo impuesto
DROP PROCEDURE IF EXISTS `sp_crear_impuesto`;
DELIMITER $$
CREATE PROCEDURE `sp_crear_impuesto`(
    IN p_codigo CHAR(3),
    IN p_fecha_inicial DATE,
    IN p_fecha_final DATE,
    IN p_valor DECIMAL(10, 2),
    IN p_estado BOOLEAN
)
BEGIN
    INSERT INTO `impuestos` (codigo, fecha_inicial, fecha_final, valor, estado)
    VALUES (p_codigo, p_fecha_inicial, p_fecha_final, p_valor, p_estado);
END$$
DELIMITER ;

-- Procedimiento para ACTUALIZAR un impuesto existente
DROP PROCEDURE IF EXISTS `sp_actualizar_impuesto`;
DELIMITER $$
CREATE PROCEDURE `sp_actualizar_impuesto`(
    IN p_id INT,
    IN p_codigo CHAR(3),
    IN p_fecha_inicial DATE,
    IN p_fecha_final DATE,
    IN p_valor DECIMAL(10, 2),
    IN p_estado BOOLEAN
)
BEGIN
    UPDATE `impuestos`
    SET
        codigo = p_codigo,
        fecha_inicial = p_fecha_inicial,
        fecha_final = p_fecha_final,
        valor = p_valor,
        estado = p_estado
    WHERE id = p_id;
END$$
DELIMITER ;

-- Procedimiento para ELIMINAR un impuesto por su ID
DROP PROCEDURE IF EXISTS `sp_eliminar_impuesto`;
DELIMITER $$
CREATE PROCEDURE `sp_eliminar_impuesto`(
    IN p_id INT
)
BEGIN
    DELETE FROM `impuestos` WHERE id = p_id;
END$$
DELIMITER ;

-- Procedimiento para LEER impuestos con filtros y paginación
DROP PROCEDURE IF EXISTS `sp_leer_impuestos`;
DELIMITER $$
CREATE PROCEDURE `sp_leer_impuestos`(
    IN p_codigo CHAR(3),
    IN p_estado BOOLEAN,
    IN p_offset INT,
    IN p_limit INT
)
BEGIN
    SELECT
        id,
        codigo,
        fecha_inicial,
        fecha_final,
        valor,
        estado
    FROM `impuestos`
    WHERE
        (p_codigo IS NULL OR codigo = p_codigo) AND
        (p_estado IS NULL OR estado = p_estado)
    ORDER BY id DESC
    LIMIT p_offset, p_limit;
END$$
DELIMITER ;

-- Procedimiento para CONTAR el total de impuestos con filtros
-- Procedimiento para CONTAR el total de impuestos con filtros (versión corregida)
DROP PROCEDURE IF EXISTS `sp_contar_impuestos`;
DELIMITER $$
CREATE PROCEDURE `sp_contar_impuestos`(
    IN p_codigo CHAR(3),
    IN p_estado BOOLEAN
)
BEGIN
    SELECT COUNT(id) AS total
    FROM `impuestos`
    WHERE
        (p_codigo IS NULL OR p_codigo = '' OR codigo = p_codigo) AND
        (p_estado IS NULL OR estado = p_estado);
END$$
DELIMITER ;


-- Procedimiento para LEER un impuesto por su ID
DROP PROCEDURE IF EXISTS `sp_leer_impuesto_por_id`;
DELIMITER $$
CREATE PROCEDURE `sp_leer_impuesto_por_id`(
    IN p_id INT
)
BEGIN
    SELECT
        id,
        codigo,
        fecha_inicial,
        fecha_final,
        valor,
        estado
    FROM `impuestos`
    WHERE id = p_id;
END$$
DELIMITER ;

-- Procedimiento para obtener los códigos únicos de impuestos para los filtros
DROP PROCEDURE IF EXISTS `sp_leer_codigos_impuesto`;
DELIMITER $$
CREATE PROCEDURE `sp_leer_codigos_impuesto`()
BEGIN
    SELECT DISTINCT codigo FROM `impuestos` ORDER BY codigo ASC;
END$$
DELIMITER ;