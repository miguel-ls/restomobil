-- Migration to add tipo_categoria and estado to categorias_producto table

-- -----------------------------------------------------
-- Alter `categorias_producto` table
-- -----------------------------------------------------
ALTER TABLE `categorias_producto`
ADD COLUMN `tipo_categoria` ENUM('Bienes', 'Servicios') NOT NULL DEFAULT 'Bienes' AFTER `descripcion`,
ADD COLUMN `estado` BOOLEAN NOT NULL DEFAULT TRUE AFTER `tipo_categoria`;

-- -----------------------------------------------------
-- Update Stored Procedures for `categorias_producto`
-- -----------------------------------------------------
DELIMITER $$

-- Drop existing procedures if they exist
DROP PROCEDURE IF EXISTS `sp_getAllCategories`$$
DROP PROCEDURE IF EXISTS `sp_readOneCategory`$$
DROP PROCEDURE IF EXISTS `sp_createCategory`$$
DROP PROCEDURE IF EXISTS `sp_updateCategory`$$

-- Recreate procedures with new fields
CREATE PROCEDURE `sp_getAllCategories`()
BEGIN
    SELECT id, nombre, descripcion, tipo_categoria, estado
    FROM `categorias_producto`
    ORDER BY nombre;
END$$

CREATE PROCEDURE `sp_readOneCategory`(IN p_id INT)
BEGIN
    SELECT id, nombre, descripcion, tipo_categoria, estado
    FROM `categorias_producto`
    WHERE id = p_id;
END$$

CREATE PROCEDURE `sp_createCategory`(
    IN p_nombre VARCHAR(100),
    IN p_descripcion TEXT,
    IN p_tipo_categoria ENUM('Bienes', 'Servicios')
)
BEGIN
    INSERT INTO `categorias_producto` (nombre, descripcion, tipo_categoria, estado)
    VALUES (p_nombre, p_descripcion, p_tipo_categoria, TRUE);
    SELECT LAST_INSERT_ID() as id;
END$$

CREATE PROCEDURE `sp_updateCategory`(
    IN p_id INT,
    IN p_nombre VARCHAR(100),
    IN p_descripcion TEXT,
    IN p_tipo_categoria ENUM('Bienes', 'Servicios'),
    IN p_estado BOOLEAN
)
BEGIN
    UPDATE `categorias_producto`
    SET
        nombre = p_nombre,
        descripcion = p_descripcion,
        tipo_categoria = p_tipo_categoria,
        estado = p_estado
    WHERE id = p_id;
END$$

DELIMITER ;
