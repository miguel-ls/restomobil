-- Script de actualización para la funcionalidad de Caja

-- Añadir el estado 'pagado' a la tabla de pedidos
ALTER TABLE `pedidos` MODIFY `estado` ENUM('recibido', 'en_preparacion', 'listo_para_servir', 'abierto', 'completado', 'cancelado', 'pagado') NOT NULL DEFAULT 'recibido';

DELIMITER $$

-- Nuevo procedimiento para obtener pedidos por uno o más estados
DROP PROCEDURE IF EXISTS sp_getOrdersByStatus$$
CREATE PROCEDURE sp_getOrdersByStatus(IN p_status VARCHAR(255))
BEGIN
    SELECT p.id, p.id_mesa, m.numero_mesa, p.id_usuario_mozo, u.nombre_completo AS nombre_mozo, p.estado, p.total, p.fecha_creacion
    FROM pedidos p
    LEFT JOIN mesas m ON p.id_mesa = m.id
    LEFT JOIN usuarios u ON p.id_usuario_mozo = u.id
    WHERE FIND_IN_SET(p.estado, p_status)
    ORDER BY p.fecha_creacion DESC;
END$$

-- Actualizar procedimiento para que acepte el nuevo estado 'pagado'
DROP PROCEDURE IF EXISTS sp_updateOrder$$
CREATE PROCEDURE sp_updateOrder(IN p_id_pedido INT, IN p_id_mesa INT, IN p_id_usuario_mozo INT, IN p_estado ENUM('recibido', 'en_preparacion', 'listo_para_servir', 'abierto', 'completado', 'cancelado', 'pagado'), IN p_items_json JSON)
BEGIN
    DECLARE v_total_calculado DECIMAL(10, 2) DEFAULT 0;
    DECLARE v_item JSON;
    DECLARE v_id_producto INT;
    DECLARE v_cantidad INT;
    DECLARE v_precio_unitario DECIMAL(10, 2);
    DECLARE i INT DEFAULT 0;
    START TRANSACTION;
    DELETE FROM detalle_pedidos WHERE id_pedido = p_id_pedido;
    WHILE i < JSON_LENGTH(p_items_json) DO
        SET v_item = JSON_EXTRACT(p_items_json, CONCAT('$[', i, ']'));
        SET v_id_producto = JSON_UNQUOTE(JSON_EXTRACT(v_item, '$.id'));
        SET v_cantidad = JSON_UNQUOTE(JSON_EXTRACT(v_item, '$.cantidad'));
        SELECT precio INTO v_precio_unitario FROM productos WHERE id = v_id_producto;
        INSERT INTO detalle_pedidos (id_pedido, id_producto, cantidad, precio_unitario)
        VALUES (p_id_pedido, v_id_producto, v_cantidad, v_precio_unitario);
        SET v_total_calculado = v_total_calculado + (v_cantidad * v_precio_unitario);
        SET i = i + 1;
    END WHILE;
    UPDATE pedidos SET id_mesa = p_id_mesa, id_usuario_mozo = p_id_usuario_mozo, estado = p_estado, total = v_total_calculado, fecha_actualizacion = CURRENT_TIMESTAMP WHERE id = p_id_pedido;
    COMMIT;
END$$

DELIMITER ;
