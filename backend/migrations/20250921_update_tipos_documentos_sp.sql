-- Migration script for fixing and adding stored procedures for tipo_documento_venta

DELIMITER $$

-- Get all sale document types (updated to include all fields)
DROP PROCEDURE IF EXISTS `sp_getAllTipoDocumentoVenta`$$
CREATE PROCEDURE `sp_getAllTipoDocumentoVenta`()
BEGIN
    SELECT id, codigo, nombre, descripcion, estado FROM tipo_documento_venta;
END$$

-- Get one sale document type
DROP PROCEDURE IF EXISTS `sp_getOneSaleDocumentType`$$
CREATE PROCEDURE `sp_getOneSaleDocumentType`(IN p_id INT)
BEGIN
    SELECT id, codigo, nombre, descripcion, estado
    FROM tipo_documento_venta
    WHERE id = p_id;
END$$

-- Update a sale document type
DROP PROCEDURE IF EXISTS `sp_updateSaleDocumentType`$$
CREATE PROCEDURE `sp_updateSaleDocumentType`(
    IN p_id INT,
    IN p_codigo VARCHAR(2),
    IN p_nombre VARCHAR(100),
    IN p_descripcion TEXT,
    IN p_estado BOOLEAN
)
BEGIN
    UPDATE tipo_documento_venta
    SET
        codigo = p_codigo,
        nombre = p_nombre,
        descripcion = p_descripcion,
        estado = p_estado
    WHERE
        id = p_id;
END$$

DELIMITER ;
