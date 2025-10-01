DELIMITER $$

DROP PROCEDURE IF EXISTS `sp_update_venta`$$

CREATE PROCEDURE `sp_update_venta`(
    IN p_id_venta INT,
    IN p_fecha_emision DATETIME
)
BEGIN
    -- Actualizar solo la fecha de emisión en la tabla de ventas.
    -- La modificación de los datos del cliente se ha eliminado para
    -- mantener la integridad de los datos.
    UPDATE ventas
    SET fecha_emision = p_fecha_emision
    WHERE id = p_id_venta;
END$$

DELIMITER ;