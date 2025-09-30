DELIMITER //

CREATE PROCEDURE sp_calcular_cierre(
    IN p_fecha DATE
)
BEGIN
    DECLARE total_movimientos DECIMAL(10, 2);
    DECLARE total_ventas DECIMAL(10, 2);
    DECLARE total_cierre DECIMAL(10, 2);

    -- Calcular el total de movimientos de caja para la fecha dada
    SELECT
        COALESCE(SUM(CASE WHEN tipo_movimiento = 'ENTRADA' THEN importe ELSE 0 END) -
                 SUM(CASE WHEN tipo_movimiento = 'SALIDA' THEN importe ELSE 0 END), 0)
    INTO total_movimientos
    FROM movimientos_caja
    WHERE DATE(fecha) = p_fecha;

    -- Calcular el total de ventas emitidas para la fecha dada
    SELECT
        COALESCE(SUM(total), 0)
    INTO total_ventas
    FROM ventas
    WHERE DATE(fecha_emision) = p_fecha AND estado = 'Emitida';

    -- Calcular el total de cierre
    SET total_cierre = total_movimientos + total_ventas;

    -- Devolver el resultado
    SELECT total_cierre AS total_cierre;

END //

DELIMITER ;