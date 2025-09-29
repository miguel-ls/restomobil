-- =================================================================================================
-- SCRIPT DE CORRECCIÓN PARA EL PROCEDIMIENTO ALMACENADO sp_updateOrder
--
-- **Problema:**
-- El procedimiento almacenado `sp_updateOrder` no estaba guardando correctamente el precio unitario
-- ni las observaciones cuando se editaba un pedido. En su lugar, tomaba el precio por defecto
-- de la tabla de productos e ignoraba las observaciones.
--
-- **Solución:**
-- Se ha modificado el procedimiento para que lea y utilice el `precio` y las `observaciones`
-- que vienen directamente en el parámetro JSON `p_items_json`.
--
-- **Instrucciones:**
-- Ejecute este script en su base de datos para reemplazar el procedimiento almacenado existente
-- por la versión corregida.
-- =================================================================================================

DELIMITER $$

DROP PROCEDURE IF EXISTS `sp_updateOrder`$$

CREATE PROCEDURE `sp_updateOrder`(
    IN p_id_pedido INT,
    IN p_id_mesa INT,
    IN p_id_usuario_mozo INT,
    IN p_estado VARCHAR(50),
    IN p_items_json JSON,
    IN p_id_cliente INT,
    IN p_id_tipo_documento_venta INT
)
BEGIN
    DECLARE v_total_calculado DECIMAL(10, 2) DEFAULT 0;
    DECLARE v_item JSON;
    DECLARE v_id_producto INT;
    DECLARE v_cantidad INT;
    DECLARE v_precio_unitario DECIMAL(10, 2);
    DECLARE v_observaciones TEXT;
    DECLARE i INT DEFAULT 0;

    START TRANSACTION;

    -- Primero, se eliminan los detalles existentes del pedido para reemplazarlos.
    DELETE FROM detalle_pedidos WHERE id_pedido = p_id_pedido;

    -- Luego, se itera sobre el JSON de items para insertar los nuevos detalles.
    WHILE i < JSON_LENGTH(p_items_json) DO
        SET v_item = JSON_EXTRACT(p_items_json, CONCAT('$[', i, ']'));
        SET v_id_producto = JSON_UNQUOTE(JSON_EXTRACT(v_item, '$.id'));
        SET v_cantidad = JSON_UNQUOTE(JSON_EXTRACT(v_item, '$.cantidad'));

        -- ===== CORRECCIÓN CLAVE =====
        -- Se extrae el precio y las observaciones directamente del JSON enviado desde el formulario.
        SET v_precio_unitario = JSON_UNQUOTE(JSON_EXTRACT(v_item, '$.precio'));
        SET v_observaciones = JSON_UNQUOTE(JSON_EXTRACT(v_item, '$.observaciones'));
        -- ============================

        -- Se inserta el detalle del pedido con los datos correctos.
        INSERT INTO detalle_pedidos (id_pedido, id_producto, cantidad, precio_unitario, observaciones)
        VALUES (p_id_pedido, v_id_producto, v_cantidad, v_precio_unitario, v_observaciones);

        -- Se recalcula el total basado en el precio que viene del JSON.
        SET v_total_calculado = v_total_calculado + (v_cantidad * v_precio_unitario);
        SET i = i + 1;
    END WHILE;

    -- Finalmente, se actualiza la cabecera del pedido con los nuevos totales y datos.
    UPDATE pedidos
    SET
        id_mesa = p_id_mesa,
        id_usuario_mozo = p_id_usuario_mozo,
        estado = p_estado,
        total = v_total_calculado,
        id_cliente = p_id_cliente,
        id_tipo_documento_venta = p_id_tipo_documento_venta,
        fecha_actualizacion = CURRENT_TIMESTAMP
    WHERE id = p_id_pedido;

    COMMIT;
END$$

DELIMITER ;