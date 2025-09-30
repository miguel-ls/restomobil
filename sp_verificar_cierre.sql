DELIMITER $$

CREATE PROCEDURE `sp_verificar_cierre_por_fecha`(
    IN p_fecha DATE
)
BEGIN
    DECLARE cierre_existente INT DEFAULT 0;

    SELECT COUNT(*)
    INTO cierre_existente
    FROM apertura_cierre_caja
    WHERE fecha_movimiento = p_fecha
      AND tipo_movimiento = 'cierre';

    SELECT cierre_existente AS cierre_existente;
END$$

DELIMITER ;