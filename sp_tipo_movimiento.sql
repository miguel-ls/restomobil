-- =====================================================
-- PROCEDIMIENTOS ALMACENADOS PARA TIPO_MOVIMIENTO (CORREGIDO)
-- =====================================================

-- Procedimiento para obtener todos los tipos de movimiento
DROP PROCEDURE IF EXISTS `sp_getAllTiposMovimiento`;
DELIMITER $$
CREATE PROCEDURE `sp_getAllTiposMovimiento`(
    IN p_descripcion VARCHAR(255),
    IN p_estado VARCHAR(20)
)
BEGIN
    SELECT id, tipo, codigo, descripcion, estado
    FROM tipo_movimiento
    WHERE
        (p_descripcion IS NULL OR p_descripcion = '' OR descripcion LIKE CONCAT('%', p_descripcion, '%')) AND
        (p_estado IS NULL OR p_estado = '' OR estado = p_estado);
END$$
DELIMITER ;

-- (El resto de los procedimientos permanecen igual)

-- Procedimiento para obtener un tipo de movimiento
DROP PROCEDURE IF EXISTS `sp_getOneTipoMovimiento`;
DELIMITER $$
CREATE PROCEDURE `sp_getOneTipoMovimiento`(
    IN p_id INT
)
BEGIN
    SELECT id, tipo, codigo, descripcion, estado
    FROM tipo_movimiento
    WHERE id = p_id;
END$$
DELIMITER ;

-- Procedimiento para crear un tipo de movimiento
DROP PROCEDURE IF EXISTS `sp_createTipoMovimiento`;
DELIMITER $$
CREATE PROCEDURE `sp_createTipoMovimiento`(
    IN p_tipo CHAR(1),
    IN p_codigo CHAR(3),
    IN p_descripcion VARCHAR(1000)
)
BEGIN
    INSERT INTO tipo_movimiento (tipo, codigo, descripcion)
    VALUES (p_tipo, p_codigo, p_descripcion);
    SELECT LAST_INSERT_ID() as id;
END$$
DELIMITER ;

-- Procedimiento para actualizar un tipo de movimiento
DROP PROCEDURE IF EXISTS `sp_updateTipoMovimiento`;
DELIMITER $$
CREATE PROCEDURE `sp_updateTipoMovimiento`(
    IN p_id INT,
    IN p_tipo CHAR(1),
    IN p_codigo CHAR(3),
    IN p_descripcion VARCHAR(1000),
    IN p_estado ENUM('activado', 'desactivado')
)
BEGIN
    UPDATE tipo_movimiento
    SET
        tipo = p_tipo,
        codigo = p_codigo,
        descripcion = p_descripcion,
        estado = p_estado
    WHERE id = p_id;
END$$
DELIMITER ;

-- Procedimiento para eliminar (desactivar) un tipo de movimiento
DROP PROCEDURE IF EXISTS `sp_deleteTipoMovimiento`;
DELIMITER $$
CREATE PROCEDURE `sp_deleteTipoMovimiento`(
    IN p_id INT
)
BEGIN
    UPDATE tipo_movimiento
    SET estado = 'desactivado'
    WHERE id = p_id;
END$$
DELIMITER ;