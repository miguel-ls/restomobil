-- SQL script to update the 'productos' table and related stored procedures

-- Modify the 'productos' table
ALTER TABLE `productos`
CHANGE COLUMN `disponible` `estado` ENUM('activo', 'inactivo') NOT NULL DEFAULT 'activo';

-- Update stored procedures for 'productos'
DELIMITER $$

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

    SELECT p.id, p.nombre, p.descripcion, p.precio, p.estado, c.nombre as categoria_nombre, c.tipo as categoria_tipo
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

DROP PROCEDURE IF EXISTS sp_countAllProducts$$
CREATE PROCEDURE sp_countAllProducts(
    IN p_id INT,
    IN p_nombre VARCHAR(100),
    IN p_descripcion TEXT,
    IN p_precio DECIMAL(10, 2),
    IN p_categoria_nombre VARCHAR(100),
    IN p_estado VARCHAR(10)
)
BEGIN
    SELECT COUNT(*) as total_records
    FROM productos p
    LEFT JOIN categorias_producto c ON p.id_categoria = c.id
    WHERE
        (p_id IS NULL OR p.id = p_id) AND
        (p_nombre IS NULL OR p.nombre LIKE CONCAT('%', p_nombre, '%')) AND
        (p_descripcion IS NULL OR p.descripcion LIKE CONCAT('%', p_descripcion, '%')) AND
        (p_precio IS NULL OR p.precio = p_precio) AND
        (p_categoria_nombre IS NULL OR c.nombre = p_categoria_nombre) AND
        (p_estado IS NULL OR p.estado = p_estado);
END$$

DROP PROCEDURE IF EXISTS sp_createProduct$$
CREATE PROCEDURE sp_createProduct(IN p_nombre VARCHAR(100), IN p_descripcion TEXT, IN p_precio DECIMAL(10, 2), IN p_id_categoria INT, IN p_estado ENUM('activo', 'inactivo'))
BEGIN
    INSERT INTO productos (nombre, descripcion, precio, id_categoria, estado)
    VALUES (p_nombre, p_descripcion, p_precio, p_id_categoria, p_estado);
    SELECT LAST_INSERT_ID() as id;
END$$

DROP PROCEDURE IF EXISTS sp_updateProduct$$
CREATE PROCEDURE sp_updateProduct(IN p_id INT, IN p_nombre VARCHAR(100), IN p_descripcion TEXT, IN p_precio DECIMAL(10, 2), IN p_id_categoria INT, IN p_estado ENUM('activo', 'inactivo'))
BEGIN
    UPDATE productos
    SET nombre = p_nombre,
        descripcion = p_descripcion,
        precio = p_precio,
        id_categoria = p_id_categoria,
        estado = p_estado
    WHERE id = p_id;
END$$

DROP PROCEDURE IF EXISTS sp_readOneProduct$$
CREATE PROCEDURE sp_readOneProduct(IN p_id INT)
BEGIN
    SELECT p.id, p.nombre, p.descripcion, p.precio, p.id_categoria, p.estado, c.nombre as categoria_nombre, c.tipo as categoria_tipo
    FROM productos p
    LEFT JOIN categorias_producto c ON p.id_categoria = c.id
    WHERE p.id = p_id;
END$$

-- Update stored procedure for creating orders to only use active products
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

        -- Verify the product is active before adding it to the order
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

DELIMITER ;
