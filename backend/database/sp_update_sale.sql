DELIMITER $$

-- Eliminar la versión anterior si existe
DROP PROCEDURE IF EXISTS sp_update_venta_and_recalculate_taxes$$
DROP PROCEDURE IF EXISTS sp_update_venta$$

-- Procedimiento para actualizar campos editables de una venta y recalcular impuestos.
CREATE PROCEDURE sp_update_venta(
    IN p_id_venta INT,
    IN p_fecha_emision DATETIME
)
BEGIN
    -- Iniciar transacción para asegurar la atomicidad de la operación
    START TRANSACTION;

    -- 1. Actualizar la fecha de emisión de la venta.
    --    Actualmente, es el único campo editable desde el formulario.
    UPDATE ventas
    SET
        fecha_emision = p_fecha_emision
    WHERE id = p_id_venta;

    -- 2. Llamar al procedimiento de cálculo de impuestos.
    --    Esto asegura que si el impuesto aplicable ha cambiado con el tiempo,
    --    los valores se actualicen de acuerdo a la nueva fecha de emisión.
    CALL sp_calcular_y_actualizar_impuestos_venta(p_id_venta);

    -- Confirmar la transacción
    COMMIT;

END$$

DELIMITER ;