DELIMITER $$

DROP PROCEDURE IF EXISTS `sp_update_venta`$$

CREATE PROCEDURE `sp_update_venta`(
    IN p_id_venta INT,
    IN p_fecha_emision DATETIME,
    IN p_nombre_cliente VARCHAR(255),
    IN p_ruc_cliente VARCHAR(20),
    IN p_direccion_cliente TEXT
)
BEGIN
    DECLARE v_id_cliente INT;

    -- Iniciar transacción para asegurar la atomicidad
    START TRANSACTION;

    -- Obtener el id_cliente de la venta
    SELECT id_cliente INTO v_id_cliente FROM ventas WHERE id = p_id_venta;

    -- Actualizar la fecha de emisión en la tabla de ventas
    UPDATE ventas
    SET fecha_emision = p_fecha_emision
    WHERE id = p_id_venta;

    -- Si hay un cliente asociado, actualizar sus datos
    IF v_id_cliente IS NOT NULL THEN
        UPDATE clientes
        SET
            nombres_apellidos = p_nombre_cliente,
            numero_documento = p_ruc_cliente,
            direccion = p_direccion_cliente
        WHERE id = v_id_cliente;
    END IF;

    -- Confirmar la transacción
    COMMIT;
END$$

DELIMITER ;