-- Drop procedures if they exist
DROP PROCEDURE IF EXISTS sp_verificar_movimiento_por_pedido;
DROP PROCEDURE IF EXISTS sp_crear_movimiento_salida_por_venta;

DELIMITER $$

--
-- Procedimiento para verificar si ya existe un movimiento de salida para un pedido
--
CREATE PROCEDURE sp_verificar_movimiento_por_pedido(IN p_id_pedido INT)
BEGIN
    SELECT COUNT(id) AS movimiento_existente
    FROM movimientos
    WHERE id_pedido = p_id_pedido AND tipo_movimiento = 'S';
END$$

--
-- Procedimiento para crear un movimiento de salida a partir de una venta (pedido)
--
CREATE PROCEDURE sp_crear_movimiento_salida_por_venta(IN p_id_pedido INT)
BEGIN
    DECLARE v_anio CHAR(4);
    DECLARE v_periodo CHAR(2);
    DECLARE v_fecha_movimiento DATE;
    DECLARE v_id_cliente INT;
    DECLARE v_id_almacen INT;
    DECLARE v_id_movimiento BIGINT;

    DECLARE v_done INT DEFAULT FALSE;
    DECLARE v_item INT;
    DECLARE v_id_producto INT;
    DECLARE v_cantidad DECIMAL(14, 5);
    DECLARE v_descripcion_producto TEXT;

    -- Cursor para iterar sobre los detalles del pedido
    DECLARE cur_detalle CURSOR FOR
        SELECT dp.id, dp.id_producto, dp.cantidad, p.nombre
        FROM detalle_pedidos dp
        JOIN productos p ON dp.id_producto = p.id
        WHERE dp.id_pedido = p_id_pedido;

    DECLARE CONTINUE HANDLER FOR NOT FOUND SET v_done = TRUE;

    -- Obtener datos de la cabecera del pedido
    SELECT
        YEAR(fecha_creacion),
        LPAD(MONTH(fecha_creacion), 2, '0'),
        DATE(fecha_creacion),
        id_cliente
    INTO v_anio, v_periodo, v_fecha_movimiento, v_id_cliente
    FROM pedidos
    WHERE id = p_id_pedido;

    -- Obtener el almacén predeterminado
    SELECT id INTO v_id_almacen FROM almacenes WHERE predeterminado = 1 LIMIT 1;

    START TRANSACTION;

        -- Insertar la cabecera del movimiento
        INSERT INTO movimientos (
            anio, periodo, tipo_movimiento, codigo_movimiento, fecha_movimiento,
            id_tipo_documento_venta, serie_documento, numero_documento, tipo_entidad,
            id_cliente, id_proveedor, estado, fecha_creacion,
            id_almacen, id_pedido
        ) VALUES (
            v_anio, v_periodo, 'S', 4, v_fecha_movimiento,
            6, '0000', DATE_FORMAT(v_fecha_movimiento, '%Y%m%d'), 'C',
            v_id_cliente, NULL, 'Activado', NOW(),
            v_id_almacen, p_id_pedido
        );

        SET v_id_movimiento = LAST_INSERT_ID();

        -- Abrir el cursor y recorrer los detalles del pedido
        OPEN cur_detalle;

        read_loop: LOOP
            FETCH cur_detalle INTO v_item, v_id_producto, v_cantidad, v_descripcion_producto;
            IF v_done THEN
                LEAVE read_loop;
            END IF;

            -- Insertar el detalle del movimiento
            INSERT INTO movimientos_detalle (
                id_movimiento, item, id_producto, cantidad, id_producto_padre,
                cantidad_padre, descripcion, codigo_unidad_medida, costo_unitario,
                costo_neto, costo_promedio
            ) VALUES (
                v_id_movimiento, v_item, v_id_producto, v_cantidad, NULL,
                NULL, v_descripcion_producto, 'NIU', 0, 0, NULL
            );

        END LOOP;

        CLOSE cur_detalle;

    COMMIT;

    SELECT v_id_movimiento AS id_movimiento;

END$$

DELIMITER ;