DELIMITER $$

CREATE OR REPLACE DEFINER = 'miguel'@'localhost'
PROCEDURE sp_createOrder (
    IN p_id_mesa INT,
    IN p_id_usuario_mozo INT,
    IN p_items_json JSON,
    IN p_estado VARCHAR(50),
    IN p_id_cliente INT,
    IN p_id_tipo_documento_venta INT,
    IN p_id_serie_documento INT,
    IN p_numero_documento VARCHAR(20)
)
BEGIN
  DECLARE v_id_pedido INT;
  DECLARE v_total_calculado DECIMAL(10, 2) DEFAULT 0;
  DECLARE v_item JSON;
  DECLARE v_id_producto INT;
  DECLARE v_cantidad INT;
  DECLARE v_precio_unitario DECIMAL(10, 2);
  DECLARE i INT DEFAULT 0;

  START TRANSACTION;

  INSERT INTO pedidos (id_mesa, id_usuario_mozo, total, estado, id_cliente, id_tipo_documento_venta, id_serie_documento, numero_documento)
  VALUES (p_id_mesa, p_id_usuario_mozo, 0, p_estado, p_id_cliente, p_id_tipo_documento_venta, p_id_serie_documento, p_numero_documento);

  SET v_id_pedido = LAST_INSERT_ID();

  WHILE i < JSON_LENGTH(p_items_json) DO
    SET v_item = JSON_EXTRACT(p_items_json, CONCAT('$[', i, ']'));
    SET v_id_producto = JSON_UNQUOTE(JSON_EXTRACT(v_item, '$.id'));
    SET v_cantidad = JSON_UNQUOTE(JSON_EXTRACT(v_item, '$.cantidad'));

    SELECT precio INTO v_precio_unitario FROM productos WHERE id = v_id_producto;

    INSERT INTO detalle_pedidos (id_pedido, id_producto, cantidad, precio_unitario)
    VALUES (v_id_pedido, v_id_producto, v_cantidad, v_precio_unitario);

    SET v_total_calculado = v_total_calculado + (v_cantidad * v_precio_unitario);
    SET i = i + 1;
  END WHILE;

  UPDATE pedidos SET total = v_total_calculado WHERE id = v_id_pedido;

  COMMIT;

  SELECT v_id_pedido AS id;
END$$

DELIMITER ;

DELIMITER $$

CREATE OR REPLACE DEFINER = 'miguel'@'localhost'
PROCEDURE sp_updateOrder (
    IN p_id_pedido INT,
    IN p_id_mesa INT,
    IN p_id_usuario_mozo INT,
    IN p_estado VARCHAR(50),
    IN p_items_json JSON,
    IN p_id_cliente INT,
    IN p_id_tipo_documento_venta INT,
    IN p_id_serie_documento INT,
    IN p_numero_documento VARCHAR(20)
)
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

  UPDATE pedidos
  SET id_mesa = p_id_mesa,
      id_usuario_mozo = p_id_usuario_mozo,
      estado = p_estado,
      total = v_total_calculado,
      id_cliente = p_id_cliente,
      id_tipo_documento_venta = p_id_tipo_documento_venta,
      id_serie_documento = p_id_serie_documento,
      numero_documento = p_numero_documento,
      fecha_actualizacion = CURRENT_TIMESTAMP
  WHERE id = p_id_pedido;

  COMMIT;
END$$

DELIMITER ;