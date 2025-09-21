-- =============================================
-- MIGRATION SCRIPT FOR CAJA AND MESAS FEATURES
-- =============================================

-- 1. Update 'pedidos' table
ALTER TABLE `pedidos` MODIFY `estado` ENUM('recibido', 'en_preparacion', 'listo_para_servir', 'abierto', 'completado', 'cancelado', 'pagado') NOT NULL DEFAULT 'recibido';

-- 2. Update 'mesas' table
ALTER TABLE `mesas` ADD COLUMN `es_libre` BOOLEAN NOT NULL DEFAULT TRUE;


DELIMITER $$

-- 3. Stored Procedures for 'pedidos'

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

DROP PROCEDURE IF EXISTS sp_createOrder$$
CREATE PROCEDURE sp_createOrder(IN p_id_mesa INT, IN p_id_usuario_mozo INT, IN p_items_json JSON, IN p_estado VARCHAR(50))
BEGIN
    DECLARE v_id_pedido INT;
    DECLARE v_total_calculado DECIMAL(10, 2) DEFAULT 0;
    DECLARE v_item JSON;
    DECLARE v_id_producto INT;
    DECLARE v_cantidad INT;
    DECLARE v_precio_unitario DECIMAL(10, 2);
    DECLARE i INT DEFAULT 0;
    START TRANSACTION;
    INSERT INTO pedidos (id_mesa, id_usuario_mozo, estado, total) VALUES (p_id_mesa, p_id_usuario_mozo, p_estado, 0);
    SET v_id_pedido = LAST_INSERT_ID();
    WHILE i < JSON_LENGTH(p_items_json) DO
        SET v_item = JSON_EXTRACT(p_items_json, CONCAT('$[', i, ']'));
        SET v_id_producto = JSON_UNQUOTE(JSON_EXTRACT(v_item, '$.id'));
        SET v_cantidad = JSON_UNQUOTE(JSON_EXTRACT(v_item, '$.cantidad'));
        SELECT precio INTO v_precio_unitario FROM productos WHERE id = v_id_producto;
        INSERT INTO detalle_pedidos (id_pedido, id_producto, cantidad, precio_unitario) VALUES (v_id_pedido, v_id_producto, v_cantidad, v_precio_unitario);
        SET v_total_calculado = v_total_calculado + (v_cantidad * v_precio_unitario);
        SET i = i + 1;
    END WHILE;
    UPDATE pedidos SET total = v_total_calculado WHERE id = v_id_pedido;
    COMMIT;
    SELECT v_id_pedido as id;
END$$


-- 4. Stored Procedures for 'mesas'

DROP PROCEDURE IF EXISTS sp_getAllTables$$
CREATE PROCEDURE sp_getAllTables()
BEGIN
    SELECT id, numero_mesa, capacidad, estado, es_libre FROM mesas ORDER BY numero_mesa;
END$$

DROP PROCEDURE IF EXISTS sp_readOneTable$$
CREATE PROCEDURE sp_readOneTable(IN p_id INT)
BEGIN
    SELECT id, numero_mesa, capacidad, estado, es_libre FROM mesas WHERE id = p_id;
END$$

DROP PROCEDURE IF EXISTS sp_createTable$$
CREATE PROCEDURE sp_createTable(IN p_numero_mesa VARCHAR(10), IN p_capacidad INT, IN p_estado ENUM('disponible', 'ocupada', 'reservada', 'mantenimiento'), IN p_es_libre BOOLEAN)
BEGIN
    INSERT INTO mesas (numero_mesa, capacidad, estado, es_libre) VALUES (p_numero_mesa, p_capacidad, p_estado, p_es_libre);
    SELECT LAST_INSERT_ID() as id;
END$$

DROP PROCEDURE IF EXISTS sp_updateTable$$
CREATE PROCEDURE sp_updateTable(IN p_id INT, IN p_numero_mesa VARCHAR(10), IN p_capacidad INT, IN p_estado ENUM('disponible', 'ocupada', 'reservada', 'mantenimiento'), IN p_es_libre BOOLEAN)
BEGIN
    UPDATE mesas SET numero_mesa = p_numero_mesa, capacidad = p_capacidad, estado = p_estado, es_libre = p_es_libre WHERE id = p_id;
END$$

DROP PROCEDURE IF EXISTS sp_deleteTable$$
CREATE PROCEDURE sp_deleteTable(IN p_id INT)
BEGIN
    DELETE FROM mesas WHERE id = p_id;
END$$

DROP PROCEDURE IF EXISTS sp_getAvailableTables$$
CREATE PROCEDURE sp_getAvailableTables()
BEGIN
    SELECT m.id, m.numero_mesa, m.capacidad, m.estado, m.es_libre
    FROM mesas m
    WHERE m.es_libre = 0
      AND (
        NOT EXISTS (SELECT 1 FROM pedidos p WHERE p.id_mesa = m.id)
        OR
        (SELECT p.estado
         FROM pedidos p
         WHERE p.id_mesa = m.id
         ORDER BY p.fecha_creacion DESC
         LIMIT 1) = 'completado'
      );
END$$

DROP PROCEDURE IF EXISTS sp_getTablesByLibreStatus$$
CREATE PROCEDURE sp_getTablesByLibreStatus(IN p_es_libre BOOLEAN)
BEGIN
    SELECT id, numero_mesa, capacidad, estado, es_libre
    FROM mesas
    WHERE es_libre = p_es_libre;
END$$

DELIMITER ;
