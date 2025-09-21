-- Migration to add sale document types table and procedures

-- -----------------------------------------------------
-- Table `tipo_documento_venta`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `tipo_documento_venta` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `codigo` VARCHAR(2) NOT NULL UNIQUE,
  `nombre` VARCHAR(100) NOT NULL,
  `descripcion` TEXT,
  `estado` BOOLEAN NOT NULL DEFAULT TRUE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- -----------------------------------------------------
-- Initial Data for `tipo_documento_venta`
-- -----------------------------------------------------
INSERT INTO `tipo_documento_venta` (`codigo`, `nombre`, `descripcion`) VALUES
('01', 'Factura', 'Factura Electrónica'),
('03', 'Boleta de Venta', 'Boleta de Venta Electrónica'),
('07', 'Nota de Crédito', 'Nota de Crédito Electrónica'),
('08', 'Nota de Débito', 'Nota de Débito Electrónica'),
('12', 'Ticket de Máquina Registradora', 'Ticket o cinta emitido por máquina registradora'),
('00', 'Otros', 'Otros tipos de comprobantes de pago');

-- -----------------------------------------------------
-- Stored Procedures for `tipo_documento_venta`
-- -----------------------------------------------------
DELIMITER $$

DROP PROCEDURE IF EXISTS `sp_getAllSaleDocumentTypes`$$
CREATE PROCEDURE `sp_getAllSaleDocumentTypes`()
BEGIN
    SELECT id, codigo, nombre, descripcion, estado
    FROM `tipo_documento_venta`
    ORDER BY nombre;
END$$

DROP PROCEDURE IF EXISTS `sp_getOneSaleDocumentType`$$
CREATE PROCEDURE `sp_getOneSaleDocumentType`(IN p_id INT)
BEGIN
    SELECT id, codigo, nombre, descripcion, estado
    FROM `tipo_documento_venta`
    WHERE id = p_id;
END$$

DROP PROCEDURE IF EXISTS `sp_createSaleDocumentType`$$
CREATE PROCEDURE `sp_createSaleDocumentType`(
    IN p_codigo VARCHAR(2),
    IN p_nombre VARCHAR(100),
    IN p_descripcion TEXT
)
BEGIN
    INSERT INTO `tipo_documento_venta` (codigo, nombre, descripcion)
    VALUES (p_codigo, p_nombre, p_descripcion);
    SELECT LAST_INSERT_ID() as id;
END$$

DROP PROCEDURE IF EXISTS `sp_updateSaleDocumentType`$$
CREATE PROCEDURE `sp_updateSaleDocumentType`(
    IN p_id INT,
    IN p_codigo VARCHAR(2),
    IN p_nombre VARCHAR(100),
    IN p_descripcion TEXT,
    IN p_estado BOOLEAN
)
BEGIN
    UPDATE `tipo_documento_venta`
    SET
        codigo = p_codigo,
        nombre = p_nombre,
        descripcion = p_descripcion,
        estado = p_estado
    WHERE id = p_id;
END$$

DROP PROCEDURE IF EXISTS `sp_deleteSaleDocumentType`$$
CREATE PROCEDURE `sp_deleteSaleDocumentType`(IN p_id INT)
BEGIN
    DELETE FROM `tipo_documento_venta` WHERE id = p_id;
END$$

DELIMITER ;
