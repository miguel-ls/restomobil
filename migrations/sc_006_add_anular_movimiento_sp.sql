-- Drop procedure if it exists
DROP PROCEDURE IF EXISTS sp_anular_movimiento;

DELIMITER $$

--
-- Procedimiento para anular un movimiento (cambiar estado a 'Desactivado')
--
CREATE PROCEDURE sp_anular_movimiento(IN p_id_movimiento BIGINT)
BEGIN
    UPDATE movimientos
    SET estado = 'Desactivado'
    WHERE id = p_id_movimiento;
END$$

DELIMITER ;