-- =================================================================
-- MIGRATION SCRIPT FOR SERVICE PRODUCT FEATURES
-- Adds 'observaciones' to order items and updates related procedures
-- =================================================================

-- 1. Add 'observaciones' to 'detalle_pedidos' table
ALTER TABLE `detalle_pedidos`
ADD COLUMN `observaciones` TEXT COMMENT 'Observaciones adicionales para el item del pedido, útil para servicios.';


DELIMITER $$

-- 2. Update Stored Procedures

-- From backend/update_productos_add_status.sql
DROP PROCEDURE IF EXISTS sp_getAllProducts$$
CREATE PROCEDURE sp_getAllProducts(
    IN p_id INT,
    IN p_nombre VARCHAR(100),
    IN p_descripcion TEXT,
    IN p_precio DECIMAL(10, 2),
    IN p_categoria_nombre VARCHAR(100),
    IN p_estado VARCHAR(10),
    IN p_page_number INT,
    IN p_page_size INT
)
BEGIN
    DECLARE v_offset INT;
    SET v_offset = (p_page_number - 1) * p_page_size;

    SELECT p.id, p.nombre, p.descripcion, p.precio, p.estado, c.nombre as categoria_nombre, c.tipo_categoria as categoria_tipo
    FROM productos p
    LEFT JOIN categorias_producto c ON p.id_categoria = c.id
    WHERE
        (p_id IS NULL OR p.id = p_id) AND
        (p_nombre IS NULL OR p.nombre LIKE CONCAT('%', p_nombre, '%')) AND
        (p_descripcion IS NULL OR p.descripcion LIKE CONCAT('%', p_descripcion, '%')) AND
        (p_precio IS NULL OR p.precio = p_precio) AND
        (p_categoria_nombre IS NULL OR c.nombre = p_categoria_nombre) AND
        (p_estado IS NULL OR p.estado = p_estado)
    ORDER BY p.nombre
    LIMIT p_page_size OFFSET v_offset;
END$$

-- From backend/update_productos_add_status.sql
DROP PROCEDURE IF EXISTS sp_readOneProduct$$
CREATE PROCEDURE sp_readOneProduct(IN p_id INT)
BEGIN
    SELECT p.id, p.nombre, p.descripcion, p.precio, p.id_categoria, p.estado, c.nombre as categoria_nombre, c.tipo_categoria as categoria_tipo
    FROM productos p
    LEFT JOIN categorias_producto c ON p.id_categoria = c.id
    WHERE p.id = p_id;
END$$

-- From backend/database.sql
DROP PROCEDURE IF EXISTS sp_getOrderItems$$
CREATE PROCEDURE sp_getOrderItems(IN p_id_pedido INT)
BEGIN
    SELECT
        dp.id_producto,
        pr.nombre AS nombre_producto,
        dp.cantidad,
        dp.precio_unitario,
        dp.subtotal,
        dp.observaciones,
        cp.tipo_categoria as categoria_tipo
    FROM detalle_pedidos dp
    JOIN productos pr ON dp.id_producto = pr.id
    LEFT JOIN categorias_producto cp ON pr.id_categoria = cp.id
    WHERE dp.id_pedido = p_id_pedido;
END$$

-- From backend/migrations/20250921_features.sql (latest version of sp_createOrder)
DROP PROCEDURE IF EXISTS sp_createOrder$$
CREATE PROCEDURE sp_createOrder(IN p_id_mesa INT, IN p_id_usuario_mozo INT, IN p_items_json JSON, IN p_estado VARCHAR(50))
BEGIN
    DECLARE v_id_pedido INT;
    DECLARE v_total_calculado DECIMAL(10, 2) DEFAULT 0;
    DECLARE v_item JSON;
    DECLARE v_id_producto INT;
    DECLARE v_cantidad INT;
    DECLARE v_precio_unitario DECIMAL(10, 2);
    DECLARE v_observaciones TEXT;
    DECLARE v_is_product_active BOOLEAN;
    DECLARE i INT DEFAULT 0;

    START TRANSACTION;

    INSERT INTO pedidos (id_mesa, id_usuario_mozo, estado, total) VALUES (p_id_mesa, p_id_usuario_mozo, p_estado, 0);
    SET v_id_pedido = LAST_INSERT_ID();

    WHILE i < JSON_LENGTH(p_items_json) DO
        SET v_item = JSON_EXTRACT(p_items_json, CONCAT('$[', i, ']'));
        SET v_id_producto = JSON_UNQUOTE(JSON_EXTRACT(v_item, '$.id'));
        SET v_cantidad = JSON_UNQUOTE(JSON_EXTRACT(v_item, '$.cantidad'));
        SET v_precio_unitario = JSON_UNQUOTE(JSON_EXTRACT(v_item, '$.precio'));
        SET v_observaciones = JSON_UNQUOTE(JSON_EXTRACT(v_item, '$.observaciones'));

        SELECT (estado = 'activo') INTO v_is_product_active FROM productos WHERE id = v_id_producto;

        IF v_is_product_active THEN
            INSERT INTO detalle_pedidos (id_pedido, id_producto, cantidad, precio_unitario, observaciones)
            VALUES (v_id_pedido, v_id_producto, v_cantidad, v_precio_unitario, v_observaciones);

            SET v_total_calculado = v_total_calculado + (v_cantidad * v_precio_unitario);
        END IF;

        SET i = i + 1;
    END WHILE;

    UPDATE pedidos SET total = v_total_calculado WHERE id = v_id_pedido;

    COMMIT;

    SELECT v_id_pedido as id;
END$$

-- From backend/migrations/20250921_features.sql (latest version of sp_updateOrder)
DROP PROCEDURE IF EXISTS sp_updateOrder$$
CREATE PROCEDURE sp_updateOrder(IN p_id_pedido INT, IN p_id_mesa INT, IN p_id_usuario_mozo INT, IN p_estado ENUM('recibido', 'en_preparacion', 'listo_para_servir', 'abierto', 'completado', 'cancelado', 'pagado'), IN p_items_json JSON)
BEGIN
    DECLARE v_total_calculado DECIMAL(10, 2) DEFAULT 0;
    DECLARE v_item JSON;
    DECLARE v_id_producto INT;
    DECLARE v_cantidad INT;
    DECLARE v_precio_unitario DECIMAL(10, 2);
    DECLARE v_observaciones TEXT;
    DECLARE i INT DEFAULT 0;

    START TRANSACTION;

    DELETE FROM detalle_pedidos WHERE id_pedido = p_id_pedido;

    WHILE i < JSON_LENGTH(p_items_json) DO
        SET v_item = JSON_EXTRACT(p_items_json, CONCAT('$[', i, ']'));
        SET v_id_producto = JSON_UNQUOTE(JSON_EXTRACT(v_item, '$.id'));
        SET v_cantidad = JSON_UNQUOTE(JSON_EXTRACT(v_item, '$.cantidad'));
        SET v_precio_unitario = JSON_UNQUOTE(JSON_EXTRACT(v_item, '$.precio'));
        SET v_observaciones = JSON_UNQUOTE(JSON_EXTRACT(v_item, '$.observaciones'));

        INSERT INTO detalle_pedidos (id_pedido, id_producto, cantidad, precio_unitario, observaciones)
        VALUES (p_id_pedido, v_id_producto, v_cantidad, v_precio_unitario, v_observaciones);

        SET v_total_calculado = v_total_calculado + (v_cantidad * v_precio_unitario);
        SET i = i + 1;
    END WHILE;

    UPDATE pedidos
    SET
        id_mesa = p_id_mesa,
        id_usuario_mozo = p_id_usuario_mozo,
        estado = p_estado,
        total = v_total_calculado,
        fecha_actualizacion = CURRENT_TIMESTAMP
    WHERE id = p_id_pedido;

    COMMIT;
END$$

DELIMITER ;
