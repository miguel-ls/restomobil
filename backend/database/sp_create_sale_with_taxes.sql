DELIMITER $$

DROP PROCEDURE IF EXISTS sp_crear_venta_con_impuestos;

CREATE PROCEDURE sp_crear_venta_con_impuestos(
    IN p_id_pedido INT,
    IN p_id_cliente INT,
    IN p_id_usuario_cajero INT,
    IN p_id_tipo_documento_venta INT,
    IN p_id_serie_documento INT
)
BEGIN
    -- Declaración de variables
    DECLARE v_id_venta INT;
    DECLARE v_total_pedido DECIMAL(10, 2);
    DECLARE v_numero_documento VARCHAR(20);

    -- Iniciar transacción para asegurar la atomicidad
    START TRANSACTION;

    -- 1. Obtener el total del pedido
    SELECT total INTO v_total_pedido FROM pedidos WHERE id = p_id_pedido;

    -- 2. Obtener el siguiente número de documento para la serie
    SELECT LPAD(IFNULL(MAX(CAST(numero_documento AS UNSIGNED)), 0) + 1, 8, '0')
    INTO v_numero_documento
    FROM ventas
    WHERE id_serie_documento = p_id_serie_documento;

    -- 3. Crear la cabecera de la venta
    INSERT INTO ventas (
        id_pedido, id_cliente, id_usuario_cajero, id_tipo_documento_venta,
        id_serie_documento, numero_documento, total, fecha_emision
    ) VALUES (
        p_id_pedido, p_id_cliente, p_id_usuario_cajero, p_id_tipo_documento_venta,
        p_id_serie_documento, v_numero_documento, v_total_pedido, NOW()
    );
    SET v_id_venta = LAST_INSERT_ID();

    -- 4. Copiar el detalle del pedido al detalle de la venta
    INSERT INTO venta_detalle (id_venta, id_producto, cantidad, precio_unitario)
    SELECT v_id_venta, id_producto, cantidad, precio_unitario
    FROM detalle_pedidos
    WHERE id_pedido = p_id_pedido;

    -- 5. Actualizar el estado del pedido a 'pagado'
    UPDATE pedidos SET estado = 'pagado' WHERE id = p_id_pedido;

    -- 6. Calcular y actualizar los impuestos para la nueva venta
    CALL sp_calcular_y_actualizar_impuestos_venta(v_id_venta);

    -- Confirmar la transacción
    COMMIT;

    -- 7. Devolver el ID de la venta y el número de documento generado
    SELECT v_id_venta AS id_venta, v_numero_documento AS numero_documento;

END$$

DELIMITER ;