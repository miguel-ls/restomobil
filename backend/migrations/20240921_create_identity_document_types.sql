-- Migration script for creating the tipo_documento_identidad table and stored procedures

-- -----------------------------------------------------
-- Table `tipo_documento_identidad`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `tipo_documento_identidad`;

CREATE TABLE `tipo_documento_identidad` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `codigo` VARCHAR(2) NOT NULL,
  `nombre` VARCHAR(100) NOT NULL,
  `descripcion` TEXT NULL,
  `estado` BOOLEAN NOT NULL DEFAULT TRUE,
  PRIMARY KEY (`id`),
  UNIQUE INDEX `codigo_UNIQUE` (`codigo` ASC)
) ENGINE = InnoDB;

-- -----------------------------------------------------
-- Initial data for `tipo_documento_identidad`
-- -----------------------------------------------------
INSERT INTO `tipo_documento_identidad` (`codigo`, `nombre`, `descripcion`) VALUES
('0', 'DOC.TRIB.NO.DOM.SIN.RUC', 'Documento Tributario para no domiciliado sin RUC'),
('1', 'DNI', 'Documento Nacional de Identidad'),
('4', 'CARNET.EXT', 'Carnet de Extranjería'),
('6', 'RUC', 'Registro Único de Contribuyentes'),
('7', 'PASAPORTE', 'Pasaporte'),
('A', 'CED.DIP.IDENT.', 'Cédula Diplomática de Identidad');

-- -----------------------------------------------------
-- Stored Procedures for `tipo_documento_identidad`
-- -----------------------------------------------------
DELIMITER $$

DROP PROCEDURE IF EXISTS `sp_getAllIdentityDocumentTypes`$$
CREATE PROCEDURE `sp_getAllIdentityDocumentTypes`()
BEGIN
    SELECT id, codigo, nombre, descripcion, estado FROM tipo_documento_identidad WHERE estado = 1;
END$$

DROP PROCEDURE IF EXISTS `sp_getOneIdentityDocumentType`$$
CREATE PROCEDURE `sp_getOneIdentityDocumentType`(IN p_id INT)
BEGIN
    SELECT id, codigo, nombre, descripcion, estado FROM tipo_documento_identidad WHERE id = p_id;
END$$

DROP PROCEDURE IF EXISTS `sp_createIdentityDocumentType`$$
CREATE PROCEDURE `sp_createIdentityDocumentType`(IN p_codigo VARCHAR(2), IN p_nombre VARCHAR(100), IN p_descripcion TEXT)
BEGIN
    INSERT INTO tipo_documento_identidad (codigo, nombre, descripcion) VALUES (p_codigo, p_nombre, p_descripcion);
    SELECT LAST_INSERT_ID() as id;
END$$

DROP PROCEDURE IF EXISTS `sp_updateIdentityDocumentType`$$
CREATE PROCEDURE `sp_updateIdentityDocumentType`(IN p_id INT, IN p_codigo VARCHAR(2), IN p_nombre VARCHAR(100), IN p_descripcion TEXT, IN p_estado BOOLEAN)
BEGIN
    UPDATE tipo_documento_identidad SET codigo = p_codigo, nombre = p_nombre, descripcion = p_descripcion, estado = p_estado WHERE id = p_id;
END$$

DROP PROCEDURE IF EXISTS `sp_deleteIdentityDocumentType`$$
CREATE PROCEDURE `sp_deleteIdentityDocumentType`(IN p_id INT)
BEGIN
    UPDATE tipo_documento_identidad SET estado = 0 WHERE id = p_id;
END$$

DELIMITER ;
