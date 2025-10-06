-- Tabla para Unidades de Medida
CREATE TABLE IF NOT EXISTS `unidades_medida` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `codigo` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL,
  `descripcion` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `estado` tinyint(1) NOT NULL DEFAULT 1,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp(),
  `fecha_actualizacion` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `codigo` (`codigo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Procedimiento para crear una nueva unidad de medida
DROP PROCEDURE IF EXISTS `sp_create_unidad_medida`;
DELIMITER $$
CREATE PROCEDURE `sp_create_unidad_medida`(
    IN p_codigo VARCHAR(10),
    IN p_descripcion VARCHAR(255)
)
BEGIN
    INSERT INTO `unidades_medida` (`codigo`, `descripcion`)
    VALUES (p_codigo, p_descripcion);
    SELECT LAST_INSERT_ID() as id;
END$$
DELIMITER ;

-- Procedimiento para leer todas las unidades de medida con filtros y paginación
DROP PROCEDURE IF EXISTS `sp_read_unidades_medida`;
DELIMITER $$
CREATE PROCEDURE `sp_read_unidades_medida`(
    IN p_filter VARCHAR(255),
    IN p_limit INT,
    IN p_offset INT
)
BEGIN
    SET @sql = CONCAT('
        SELECT id, codigo, descripcion, estado
        FROM unidades_medida
        WHERE (codigo LIKE ''%', p_filter, '%'' COLLATE utf8mb4_unicode_ci OR descripcion LIKE ''%', p_filter, '%'' COLLATE utf8mb4_unicode_ci)
        ORDER BY id DESC
        LIMIT ', p_limit, ' OFFSET ', p_offset, ';
    ');
    PREPARE stmt FROM @sql;
    EXECUTE stmt;
    DEALLOCATE PREPARE stmt;
END$$
DELIMITER ;

-- Procedimiento para contar las unidades de medida con filtros
DROP PROCEDURE IF EXISTS `sp_count_unidades_medida`;
DELIMITER $$
CREATE PROCEDURE `sp_count_unidades_medida`(
    IN p_filter VARCHAR(255)
)
BEGIN
    SELECT COUNT(*) as total
    FROM unidades_medida
    WHERE codigo LIKE CONCAT('%', p_filter, '%') COLLATE utf8mb4_unicode_ci OR descripcion LIKE CONCAT('%', p_filter, '%') COLLATE utf8mb4_unicode_ci;
END$$
DELIMITER ;

-- Procedimiento para obtener una unidad de medida por su ID
DROP PROCEDURE IF EXISTS `sp_get_unidad_medida_by_id`;
DELIMITER $$
CREATE PROCEDURE `sp_get_unidad_medida_by_id`(
    IN p_id INT
)
BEGIN
    SELECT id, codigo, descripcion, estado
    FROM unidades_medida
    WHERE id = p_id;
END$$
DELIMITER ;

-- Procedimiento para actualizar una unidad de medida
DROP PROCEDURE IF EXISTS `sp_update_unidad_medida`;
DELIMITER $$
CREATE PROCEDURE `sp_update_unidad_medida`(
    IN p_id INT,
    IN p_codigo VARCHAR(10),
    IN p_descripcion VARCHAR(255),
    IN p_estado TINYINT
)
BEGIN
    UPDATE `unidades_medida`
    SET
        `codigo` = p_codigo,
        `descripcion` = p_descripcion,
        `estado` = p_estado
    WHERE `id` = p_id;
END$$
DELIMITER ;

-- Procedimiento para eliminar una unidad de medida (cambio de estado)
DROP PROCEDURE IF EXISTS `sp_delete_unidad_medida`;
DELIMITER $$
CREATE PROCEDURE `sp_delete_unidad_medida`(
    IN p_id INT
)
BEGIN
    UPDATE `unidades_medida`
    SET `estado` = 0
    WHERE `id` = p_id;
END$$
DELIMITER ;