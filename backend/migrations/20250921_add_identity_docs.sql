-- Migration to add identity document types table and procedures

-- -----------------------------------------------------
-- Table `tipo_documento_identidad`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `tipo_documento_identidad` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `codigo` VARCHAR(2) NOT NULL UNIQUE,
  `nombre` VARCHAR(100) NOT NULL,
  `descripcion` TEXT,
  `estado` BOOLEAN NOT NULL DEFAULT TRUE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- -----------------------------------------------------
-- Initial Data for `tipo_documento_identidad`
-- -----------------------------------------------------
INSERT INTO `tipo_documento_identidad` (`codigo`, `nombre`, `descripcion`) VALUES
('1', 'Documento Nacional de Identidad', 'DNI'),
('4', 'Carnet de Extranjería', 'Carnet de Extranjería'),
('6', 'Registro Único de Contribuyentes', 'RUC'),
('7', 'Pasaporte', 'Pasaporte'),
('A', 'Cédula Diplomática de Identidad', 'Cédula Diplomática de Identidad'),
('0', 'Otros', 'Otros tipos de documentos');

-- -----------------------------------------------------
-- Stored Procedures for `tipo_documento_identidad`
-- -----------------------------------------------------
DELIMITER $$

DROP PROCEDURE IF EXISTS `sp_getAllIdentityDocumentTypes`$$
CREATE PROCEDURE `sp_getAllIdentityDocumentTypes`()
BEGIN
    SELECT id, codigo, nombre, descripcion, estado
    FROM `tipo_documento_identidad`
    ORDER BY nombre;
END$$

DROP PROCEDURE IF EXISTS `sp_getOneIdentityDocumentType`$$
CREATE PROCEDURE `sp_getOneIdentityDocumentType`(IN p_id INT)
BEGIN
    SELECT id, codigo, nombre, descripcion, estado
    FROM `tipo_documento_identidad`
    WHERE id = p_id;
END$$

DROP PROCEDURE IF EXISTS `sp_createIdentityDocumentType`$$
CREATE PROCEDURE `sp_createIdentityDocumentType`(
    IN p_codigo VARCHAR(2),
    IN p_nombre VARCHAR(100),
    IN p_descripcion TEXT
)
BEGIN
    INSERT INTO `tipo_documento_identidad` (codigo, nombre, descripcion)
    VALUES (p_codigo, p_nombre, p_descripcion);
    SELECT LAST_INSERT_ID() as id;
END$$

DROP PROCEDURE IF EXISTS `sp_updateIdentityDocumentType`$$
CREATE PROCEDURE `sp_updateIdentityDocumentType`(
    IN p_id INT,
    IN p_codigo VARCHAR(2),
    IN p_nombre VARCHAR(100),
    IN p_descripcion TEXT,
    IN p_estado BOOLEAN
)
BEGIN
    UPDATE `tipo_documento_identidad`
    SET
        codigo = p_codigo,
        nombre = p_nombre,
        descripcion = p_descripcion,
        estado = p_estado
    WHERE id = p_id;
END$$

DROP PROCEDURE IF EXISTS `sp_deleteIdentityDocumentType`$$
CREATE PROCEDURE `sp_deleteIdentityDocumentType`(IN p_id INT)
BEGIN
    DELETE FROM `tipo_documento_identidad` WHERE id = p_id;
END$$

DELIMITER ;
