-- Migration script for adding customer and document type to orders

-- -----------------------------------------------------
-- Table `tipos_comprobante`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `tipos_comprobante`;

CREATE TABLE `tipos_comprobante` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `codigo` VARCHAR(2) NOT NULL,
  `nombre` VARCHAR(50) NOT NULL,
  `estado` BOOLEAN NOT NULL DEFAULT TRUE,
  PRIMARY KEY (`id`),
  UNIQUE INDEX `codigo_UNIQUE` (`codigo` ASC)
) ENGINE = InnoDB;

-- -----------------------------------------------------
-- Initial data for `tipos_comprobante`
-- -----------------------------------------------------
INSERT INTO `tipos_comprobante` (`codigo`, `nombre`) VALUES
('01', 'Factura'),
('03', 'Boleta de Venta');

-- -----------------------------------------------------
-- Modify `pedidos` table
-- -----------------------------------------------------
ALTER TABLE `pedidos`
ADD COLUMN `id_cliente` INT NULL,
ADD COLUMN `id_tipo_comprobante` INT NULL,
ADD CONSTRAINT `fk_pedidos_clientes`
  FOREIGN KEY (`id_cliente`)
  REFERENCES `clientes` (`id`)
  ON DELETE SET NULL
  ON UPDATE CASCADE,
ADD CONSTRAINT `fk_pedidos_tipos_comprobante`
  FOREIGN KEY (`id_tipo_comprobante`)
  REFERENCES `tipos_comprobante` (`id`)
  ON DELETE SET NULL
  ON UPDATE CASCADE;

-- -----------------------------------------------------
-- Stored Procedures for `tipos_comprobante`
-- -----------------------------------------------------
DELIMITER $$

DROP PROCEDURE IF EXISTS `sp_getAllTiposComprobante`$$
CREATE PROCEDURE `sp_getAllTiposComprobante`()
BEGIN
    SELECT id, codigo, nombre, estado FROM tipos_comprobante WHERE estado = 1;
END$$

DROP PROCEDURE IF EXISTS `sp_getOneTipoComprobante`$$
CREATE PROCEDURE `sp_getOneTipoComprobante`(IN p_id INT)
BEGIN
    SELECT id, codigo, nombre, estado FROM tipos_comprobante WHERE id = p_id;
END$$

DROP PROCEDURE IF EXISTS `sp_createTipoComprobante`$$
CREATE PROCEDURE `sp_createTipoComprobante`(IN p_codigo VARCHAR(2), IN p_nombre VARCHAR(50))
BEGIN
    INSERT INTO tipos_comprobante (codigo, nombre) VALUES (p_codigo, p_nombre);
    SELECT LAST_INSERT_ID() as id;
END$$

DROP PROCEDURE IF EXISTS `sp_updateTipoComprobante`$$
CREATE PROCEDURE `sp_updateTipoComprobante`(IN p_id INT, IN p_codigo VARCHAR(2), IN p_nombre VARCHAR(50), IN p_estado BOOLEAN)
BEGIN
    UPDATE tipos_comprobante SET codigo = p_codigo, nombre = p_nombre, estado = p_estado WHERE id = p_id;
END$$

DROP PROCEDURE IF EXISTS `sp_deleteTipoComprobante`$$
CREATE PROCEDURE `sp_deleteTipoComprobante`(IN p_id INT)
BEGIN
    UPDATE tipos_comprobante SET estado = 0 WHERE id = p_id;
END$$

-- -----------------------------------------------------
-- Update Stored Procedures for `pedidos`
-- -----------------------------------------------------
DROP PROCEDURE IF EXISTS sp_getOrderDetail$$
CREATE PROCEDURE sp_getOrderDetail(IN p_id_pedido INT)
BEGIN
    SELECT
        p.id,
        p.id_mesa,
        m.numero_mesa,
        p.id_usuario_mozo,
        u.nombre_completo AS nombre_mozo,
        p.estado,
        p.total,
        p.fecha_creacion,
        p.fecha_actualizacion,
        p.id_cliente,
        c.nombres_apellidos AS nombre_cliente,
        c.numero_documento AS ruc_cliente,
        c.direccion AS direccion_cliente,
        p.id_tipo_comprobante
    FROM pedidos p
    LEFT JOIN mesas m ON p.id_mesa = m.id
    LEFT JOIN usuarios u ON p.id_usuario_mozo = u.id
    LEFT JOIN clientes c ON p.id_cliente = c.id
    WHERE p.id = p_id_pedido;
END$$

DROP PROCEDURE IF EXISTS sp_createOrder$$
CREATE PROCEDURE sp_createOrder(IN p_id_mesa INT, IN p_id_usuario_mozo INT, IN p_items_json JSON, IN p_estado VARCHAR(50), IN p_id_cliente INT, IN p_id_tipo_comprobante INT)
BEGIN
    DECLARE v_id_pedido INT;
    DECLARE v_total_calculado DECIMAL(10, 2) DEFAULT 0;
    DECLARE v_item JSON;
    DECLARE v_id_producto INT;
    DECLARE v_cantidad INT;
    DECLARE v_precio_unitario DECIMAL(10, 2);
    DECLARE i INT DEFAULT 0;
    START TRANSACTION;
    INSERT INTO pedidos (id_mesa, id_usuario_mozo, total, estado, id_cliente, id_tipo_comprobante) VALUES (p_id_mesa, p_id_usuario_mozo, 0, p_estado, p_id_cliente, p_id_tipo_comprobante);
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
CREATE PROCEDURE sp_updateOrder(IN p_id_pedido INT, IN p_id_mesa INT, IN p_id_usuario_mozo INT, IN p_estado VARCHAR(50), IN p_items_json JSON, IN p_id_cliente INT, IN p_id_tipo_comprobante INT)
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
    UPDATE pedidos SET id_mesa = p_id_mesa, id_usuario_mozo = p_id_usuario_mozo, estado = p_estado, total = v_total_calculado, id_cliente = p_id_cliente, id_tipo_comprobante = p_id_tipo_comprobante, fecha_actualizacion = CURRENT_TIMESTAMP WHERE id = p_id_pedido;
    COMMIT;
END$$

DELIMITER ;
