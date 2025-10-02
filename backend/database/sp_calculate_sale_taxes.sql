DELIMITER $$

CREATE PROCEDURE sp_calcular_y_actualizar_impuestos_venta(
    IN p_id_venta INT
)
BEGIN
    -- Declaración de variables para almacenar los valores calculados
    DECLARE v_total DECIMAL(10, 2);
    DECLARE v_porcentaje DECIMAL(10, 2);
    DECLARE v_base DECIMAL(10, 2);
    DECLARE v_impuesto DECIMAL(10, 2);

    -- 1. Obtener el total de la venta
    SELECT total INTO v_total FROM ventas WHERE id = p_id_venta;

    -- 2. Obtener el porcentaje de impuesto aplicable
    -- Se busca el impuesto activo más reciente según la fecha actual.
    -- Un impuesto está activo si la fecha actual está entre la fecha inicial y final,
    -- o si la fecha final es nula y la fecha actual es posterior a la inicial.
    SELECT valor INTO v_porcentaje
    FROM impuestos
    WHERE (NOW() BETWEEN fecha_inicial AND fecha_final)
       OR (fecha_final IS NULL AND NOW() >= fecha_inicial)
    ORDER BY id DESC
    LIMIT 1;

    -- Si no se encuentra un impuesto aplicable, se puede establecer un valor por defecto o manejar el error.
    -- Por ahora, si no se encuentra, el porcentaje será NULL y los cálculos también.
    IF v_porcentaje IS NOT NULL AND v_total > 0 THEN
        -- 3. Calcular la base imponible
        -- Fórmula: base = total / (1 + (porcentaje / 100))
        SET v_base = v_total / (1 + (v_porcentaje / 100));

        -- 4. Calcular el monto del impuesto
        -- Fórmula: impuesto = total - base
        SET v_impuesto = v_total - v_base;
    ELSE
        -- Si no hay porcentaje o total, los valores se establecen en NULL o 0.
        SET v_base = v_total; -- O NULL si se prefiere
        SET v_impuesto = 0.00; -- O NULL
    END IF;

    -- 5. Actualizar la tabla de ventas con los nuevos valores
    UPDATE ventas
    SET
        porcentaje = v_porcentaje,
        base = v_base,
        impuesto = v_impuesto
    WHERE id = p_id_venta;

END$$

DELIMITER ;