-- Añadir la columna `predeterminado` a la tabla `almacenes`
ALTER TABLE `almacenes`
ADD COLUMN `predeterminado` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'Indica si el almacén es el predeterminado';

-- Actualizar procedimiento para leer almacenes
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
        predeterminado,
        fecha_creacion,
        fecha_eliminacion
    FROM `almacenes`
    WHERE
        (p_nombre IS NULL OR p_nombre = '' OR nombre LIKE CONCAT('%', p_nombre, '%') COLLATE utf8mb4_unicode_ci) AND
        (p_estado IS NULL OR estado = p_estado)
    ORDER BY id DESC;
END$$
DELIMITER ;

-- Actualizar procedimiento para leer un almacén por ID
DROP PROCEDURE IF EXISTS `sp_leer_almacen_por_id`;
DELIMITER $$
CREATE PROCEDURE `sp_leer_almacen_por_id`(IN p_id INT)
BEGIN
    SELECT
        id,
        nombre,
        estado,
        predeterminado
    FROM `almacenes`
    WHERE id = p_id;
END$$
DELIMITER ;

-- Actualizar procedimiento para crear un almacén
DROP PROCEDURE IF EXISTS `sp_crear_almacen`;
DELIMITER $$
CREATE PROCEDURE `sp_crear_almacen`(
    IN p_nombre VARCHAR(255),
    IN p_predeterminado BOOLEAN
)
BEGIN
    -- Si se va a establecer como predeterminado, desmarcar cualquier otro
    IF p_predeterminado = 1 THEN
        UPDATE `almacenes` SET `predeterminado` = 0;
    END IF;

    INSERT INTO `almacenes` (nombre, predeterminado, estado)
    VALUES (p_nombre, p_predeterminado, 1);

    SELECT LAST_INSERT_ID() AS id;
END$$
DELIMITER ;

-- Actualizar procedimiento para actualizar un almacén
DROP PROCEDURE IF EXISTS `sp_actualizar_almacen`;
DELIMITER $$
CREATE PROCEDURE `sp_actualizar_almacen`(
    IN p_id INT,
    IN p_nombre VARCHAR(255),
    IN p_estado BOOLEAN,
    IN p_predeterminado BOOLEAN
)
BEGIN
    -- Si se va a establecer como predeterminado, desmarcar cualquier otro
    IF p_predeterminado = 1 THEN
        UPDATE `almacenes` SET `predeterminado` = 0 WHERE id != p_id;
    END IF;

    UPDATE `almacenes`
    SET
        nombre = p_nombre,
        estado = p_estado,
        predeterminado = p_predeterminado
    WHERE id = p_id;
END$$
DELIMITER ;

-- Contar almacenes
DROP PROCEDURE IF EXISTS `sp_contar_almacenes`;
DELIMITER $$
CREATE PROCEDURE `sp_contar_almacenes`(
    IN p_nombre VARCHAR(255),
    IN p_estado BOOLEAN
)
BEGIN
    SELECT
        COUNT(id) AS total
    FROM `almacenes`
    WHERE
        (p_nombre IS NULL OR p_nombre = '' OR nombre LIKE CONCAT('%', p_nombre, '%') COLLATE utf8mb4_unicode_ci) AND
        (p_estado IS NULL OR estado = p_estado);
END$$
DELIMITER ;