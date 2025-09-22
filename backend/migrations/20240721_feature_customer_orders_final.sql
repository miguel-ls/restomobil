-- Migration script for the customer orders feature (v3 - Final)
-- This script is non-destructive and assumes all tables exist.

-- -----------------------------------------------------
-- Modify `pedidos` table
-- -----------------------------------------------------
ALTER TABLE `pedidos`
ADD COLUMN `id_cliente` INT NULL,
ADD COLUMN `id_tipo_documento_venta` INT NULL,
ADD CONSTRAINT `fk_pedidos_clientes`
  FOREIGN KEY (`id_cliente`)
  REFERENCES `clientes` (`id`)
  ON DELETE SET NULL
  ON UPDATE CASCADE,
ADD CONSTRAINT `fk_pedidos_tipo_documento_venta`
  FOREIGN KEY (`id_tipo_documento_venta`)
  REFERENCES `tipo_documento_venta` (`id`)
  ON DELETE SET NULL
  ON UPDATE CASCADE;

-- -----------------------------------------------------
-- Modify `clientes` table to add composite unique index
-- -----------------------------------------------------
-- Drop the old unique index if it exists
DROP PROCEDURE IF EXISTS DropIndexIfExists;
DELIMITER $$
CREATE PROCEDURE DropIndexIfExists()
BEGIN
    IF EXISTS(SELECT 1 FROM `information_schema`.`statistics` WHERE `table_schema`=DATABASE() AND `table_name`='clientes' AND `index_name`='numero_documento_UNIQUE') THEN
        ALTER TABLE `clientes` DROP INDEX `numero_documento_UNIQUE`;
    END IF;
END$$
DELIMITER ;
CALL DropIndexIfExists();
DROP PROCEDURE DropIndexIfExists;

-- Add the new composite unique index
ALTER TABLE `clientes` ADD UNIQUE INDEX `documento_UNIQUE` (`id_tipo_documento_identidad` ASC, `numero_documento` ASC);

-- -----------------------------------------------------
-- Stored Procedures for `tipo_documento_venta`
-- -----------------------------------------------------
DELIMITER $$

DROP PROCEDURE IF EXISTS `sp_getAllTipoDocumentoVenta`$$
CREATE PROCEDURE `sp_getAllTipoDocumentoVenta`()
BEGIN
    -- Assuming tipo_documento_venta has an 'estado' column
    SELECT id, codigo, nombre, descripcion, estado FROM tipo_documento_venta;
END$$

DROP PROCEDURE IF EXISTS `sp_getOneSaleDocumentType`$$
CREATE PROCEDURE `sp_getOneSaleDocumentType`(IN p_id INT)
BEGIN
    SELECT id, codigo, nombre, descripcion, estado
    FROM tipo_documento_venta
    WHERE id = p_id;
END$$

DELIMITER ;

-- -----------------------------------------------------
-- New Stored Procedure for `clientes`
-- -----------------------------------------------------
DELIMITER $$

DROP PROCEDURE IF EXISTS `sp_searchClientes`$$
CREATE PROCEDURE `sp_searchClientes`(IN p_search_term VARCHAR(200))
BEGIN
    SELECT
        c.id,
        c.id_tipo_documento_identidad,
        tdi.nombre as tipo_documento_nombre,
        c.numero_documento,
        c.nombres_apellidos,
        c.direccion,
        c.codigo_ubigeo,
        c.email,
        c.telefono
    FROM
        clientes c
    JOIN
        tipo_documento_identidad tdi ON c.id_tipo_documento_identidad = tdi.id
    WHERE
        (c.nombres_apellidos LIKE p_search_term OR c.numero_documento LIKE p_search_term)
        AND c.estado = 'Activado'
    ORDER BY
        c.nombres_apellidos;
END$$

DELIMITER ;

-- -----------------------------------------------------
-- Update Stored Procedures for `pedidos`
-- -----------------------------------------------------
DELIMITER $$

DROP PROCEDURE IF EXISTS sp_getOrderDetail$$
CREATE PROCEDURE sp_getOrderDetail(IN p_id_pedido INT)
BEGIN
    SELECT
        p.id, p.id_mesa, m.numero_mesa, p.id_usuario_mozo, u.nombre_completo AS nombre_mozo, p.estado, p.total, p.fecha_creacion, p.fecha_actualizacion,
        p.id_cliente, c.nombres_apellidos AS nombre_cliente, c.numero_documento AS ruc_cliente, c.direccion AS direccion_cliente, c.codigo_ubigeo, c.id_tipo_documento_identidad AS id_tipo_documento_identidad_cliente,
        p.id_tipo_documento_venta
    FROM pedidos p
    LEFT JOIN mesas m ON p.id_mesa = m.id
    LEFT JOIN usuarios u ON p.id_usuario_mozo = u.id
    LEFT JOIN clientes c ON p.id_cliente = c.id
    WHERE p.id = p_id_pedido;
END$$

DROP PROCEDURE IF EXISTS sp_createOrder$$
CREATE PROCEDURE sp_createOrder(IN p_id_mesa INT, IN p_id_usuario_mozo INT, IN p_items_json JSON, IN p_estado VARCHAR(50), IN p_id_cliente INT, IN p_id_tipo_documento_venta INT)
BEGIN
    DECLARE v_id_pedido INT;
    DECLARE v_total_calculado DECIMAL(10, 2) DEFAULT 0;
    DECLARE v_item JSON;
    DECLARE v_id_producto INT;
    DECLARE v_cantidad INT;
    DECLARE v_precio_unitario DECIMAL(10, 2);
    DECLARE i INT DEFAULT 0;
    START TRANSACTION;
    INSERT INTO pedidos (id_mesa, id_usuario_mozo, total, estado, id_cliente, id_tipo_documento_venta) VALUES (p_id_mesa, p_id_usuario_mozo, 0, p_estado, p_id_cliente, p_id_tipo_documento_venta);
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

DROP PROCEDURE IF EXISTS sp_updateOrder$$
CREATE PROCEDURE sp_updateOrder(IN p_id_pedido INT, IN p_id_mesa INT, IN p_id_usuario_mozo INT, IN p_estado VARCHAR(50), IN p_items_json JSON, IN p_id_cliente INT, IN p_id_tipo_documento_venta INT)
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
    UPDATE pedidos SET id_mesa = p_id_mesa, id_usuario_mozo = p_id_usuario_mozo, estado = p_estado, total = v_total_calculado, id_cliente = p_id_cliente, id_tipo_documento_venta = p_id_tipo_documento_venta, fecha_actualizacion = CURRENT_TIMESTAMP WHERE id = p_id_pedido;
    COMMIT;
END$$

DELIMITER ;
