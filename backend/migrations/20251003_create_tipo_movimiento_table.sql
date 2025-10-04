-- =============================================
-- MIGRATION SCRIPT FOR TIPO DE MOVIMIENTO FEATURE
-- =============================================

-- 1. Create 'tipo_movimiento' table
CREATE TABLE `tipo_movimiento` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `tipo` CHAR(1) NOT NULL COMMENT 'E: Entrada, S: Salida',
  `codigo` CHAR(3) NOT NULL,
  `descripcion` VARCHAR(1000) NOT NULL,
  `estado` ENUM('activado', 'desactivado') NOT NULL DEFAULT 'activado',
  `fecha_creacion` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `fecha_modificacion` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE INDEX `codigo_UNIQUE` (`codigo` ASC)
);

-- 2. Stored Procedures for 'tipo_movimiento'
DELIMITER $$

-- Create a new tipo_movimiento
DROP PROCEDURE IF EXISTS `sp_createTipoMovimiento`$$
CREATE PROCEDURE `sp_createTipoMovimiento`(
    IN p_tipo CHAR(1),
    IN p_codigo CHAR(3),
    IN p_descripcion VARCHAR(1000)
)
BEGIN
    INSERT INTO `tipo_movimiento` (tipo, codigo, descripcion)
    VALUES (p_tipo, p_codigo, p_descripcion);
    SELECT LAST_INSERT_ID() as id;
END$$

-- Get all tipos_movimiento with filters
DROP PROCEDURE IF EXISTS `sp_getAllTiposMovimiento`$$
CREATE PROCEDURE `sp_getAllTiposMovimiento`(
    IN p_descripcion VARCHAR(1000),
    IN p_estado ENUM('activado', 'desactivado', '')
)
BEGIN
    SELECT
        id,
        tipo,
        codigo,
        descripcion,
        estado,
        fecha_creacion,
        fecha_modificacion
    FROM
        `tipo_movimiento`
    WHERE
        (p_descripcion IS NULL OR p_descripcion = '' OR descripcion LIKE CONCAT('%', p_descripcion, '%'))
        AND (p_estado IS NULL OR p_estado = '' OR estado = p_estado)
    ORDER BY
        descripcion ASC;
END$$

-- Get a single tipo_movimiento by ID
DROP PROCEDURE IF EXISTS `sp_getOneTipoMovimiento`$$
CREATE PROCEDURE `sp_getOneTipoMovimiento`(IN p_id INT)
BEGIN
    SELECT
        id,
        tipo,
        codigo,
        descripcion,
        estado
    FROM
        `tipo_movimiento`
    WHERE
        id = p_id;
END$$

-- Update a tipo_movimiento
DROP PROCEDURE IF EXISTS `sp_updateTipoMovimiento`$$
CREATE PROCEDURE `sp_updateTipoMovimiento`(
    IN p_id INT,
    IN p_tipo CHAR(1),
    IN p_codigo CHAR(3),
    IN p_descripcion VARCHAR(1000),
    IN p_estado ENUM('activado', 'desactivado')
)
BEGIN
    UPDATE `tipo_movimiento`
    SET
        tipo = p_tipo,
        codigo = p_codigo,
        descripcion = p_descripcion,
        estado = p_estado,
        fecha_modificacion = CURRENT_TIMESTAMP
    WHERE
        id = p_id;
END$$

-- Delete a tipo_movimiento (logical delete)
DROP PROCEDURE IF EXISTS `sp_deleteTipoMovimiento`$$
CREATE PROCEDURE `sp_deleteTipoMovimiento`(IN p_id INT)
BEGIN
    UPDATE `tipo_movimiento`
    SET estado = 'desactivado'
    WHERE id = p_id;
END$$

DELIMITER ;