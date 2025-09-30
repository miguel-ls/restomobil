DELIMITER $$

CREATE PROCEDURE `sp_updateOrderStatus`(IN `p_id_pedido` INT, IN `p_estado` VARCHAR(50))
BEGIN
    UPDATE pedidos
    SET
        estado = p_estado,
        fecha_actualizacion = CURRENT_TIMESTAMP
    WHERE
        id = p_id_pedido;
END$$

DELIMITER ;