-- -----------------------------------------------------
-- Archivo de Alteraciones SQL
-- Este archivo contiene los cambios incrementales a la base de datos.
-- Se ha hecho idempotente añadiendo 'DROP PROCEDURE IF EXISTS'.
-- -----------------------------------------------------

-- -----------------------------------------------------
-- Procedimientos para 'categorias_producto'
-- -----------------------------------------------------
DELIMITER $$

DROP PROCEDURE IF EXISTS sp_getAllCategories$$
CREATE PROCEDURE sp_getAllCategories()
BEGIN
    SELECT id, nombre, descripcion FROM categorias_producto ORDER BY nombre ASC;
END$$

DROP PROCEDURE IF EXISTS sp_createCategory$$
CREATE PROCEDURE sp_createCategory(IN p_nombre VARCHAR(100), IN p_descripcion TEXT)
BEGIN
    INSERT INTO categorias_producto (nombre, descripcion) VALUES (p_nombre, p_descripcion);
    SELECT LAST_INSERT_ID() as id;
END$$

DROP PROCEDURE IF EXISTS sp_readOneCategory$$
CREATE PROCEDURE sp_readOneCategory(IN p_id INT)
BEGIN
    SELECT id, nombre, descripcion FROM categorias_producto WHERE id = p_id;
END$$

DROP PROCEDURE IF EXISTS sp_updateCategory$$
CREATE PROCEDURE sp_updateCategory(IN p_id INT, IN p_nombre VARCHAR(100), IN p_descripcion TEXT)
BEGIN
    UPDATE categorias_producto SET nombre = p_nombre, descripcion = p_descripcion WHERE id = p_id;
END$$

DROP PROCEDURE IF EXISTS sp_deleteCategory$$
CREATE PROCEDURE sp_deleteCategory(IN p_id INT)
BEGIN
    DELETE FROM categorias_producto WHERE id = p_id;
END$$

DROP PROCEDURE IF EXISTS sp_getUsersByRole$$
CREATE PROCEDURE sp_getUsersByRole(IN p_role_name VARCHAR(50))
BEGIN
    SELECT
        u.id,
        u.username,
        u.nombre_completo,
        u.email,
        u.id_rol,
        r.nombre_rol,
        u.activo
    FROM
        usuarios u
    JOIN
        roles r ON u.id_rol = r.id
    WHERE
        r.nombre_rol = p_role_name AND u.activo = 1;
END$$

DELIMITER ;

-- -----------------------------------------------------
-- Procedimientos para 'reservas'
-- -----------------------------------------------------
DELIMITER $$

DROP PROCEDURE IF EXISTS sp_getAllReservations$$
CREATE PROCEDURE sp_getAllReservations()
BEGIN
    SELECT
        r.id,
        r.id_mesa,
        m.numero_mesa,
        r.nombre_cliente,
        r.fecha_reserva,
        r.cantidad_personas,
        r.estado
    FROM
        reservas r
    LEFT JOIN
        mesas m ON r.id_mesa = m.id
    ORDER BY
        r.fecha_reserva DESC;
END$$

DROP PROCEDURE IF EXISTS sp_createReservation$$
CREATE PROCEDURE sp_createReservation(
    IN p_id_mesa INT,
    IN p_nombre_cliente VARCHAR(100),
    IN p_telefono_cliente VARCHAR(20),
    IN p_email_cliente VARCHAR(100),
    IN p_fecha_reserva DATETIME,
    IN p_cantidad_personas INT,
    IN p_observaciones TEXT
)
BEGIN
    INSERT INTO reservas (id_mesa, nombre_cliente, telefono_cliente, email_cliente, fecha_reserva, cantidad_personas, observaciones)
    VALUES (p_id_mesa, p_nombre_cliente, p_telefono_cliente, p_email_cliente, p_fecha_reserva, p_cantidad_personas, p_observaciones);
    SELECT LAST_INSERT_ID() as id;
END$$

DROP PROCEDURE IF EXISTS sp_readOneReservation$$
CREATE PROCEDURE sp_readOneReservation(IN p_id INT)
BEGIN
    SELECT id, id_mesa, nombre_cliente, telefono_cliente, email_cliente, fecha_reserva, cantidad_personas, estado, observaciones
    FROM reservas
    WHERE id = p_id;
END$$

DROP PROCEDURE IF EXISTS sp_updateReservation$$
CREATE PROCEDURE sp_updateReservation(
    IN p_id INT,
    IN p_id_mesa INT,
    IN p_nombre_cliente VARCHAR(100),
    IN p_telefono_cliente VARCHAR(20),
    IN p_email_cliente VARCHAR(100),
    IN p_fecha_reserva DATETIME,
    IN p_cantidad_personas INT,
    IN p_estado ENUM('confirmada', 'cancelada', 'completada'),
    IN p_observaciones TEXT
)
BEGIN
    UPDATE reservas
    SET
        id_mesa = p_id_mesa,
        nombre_cliente = p_nombre_cliente,
        telefono_cliente = p_telefono_cliente,
        email_cliente = p_email_cliente,
        fecha_reserva = p_fecha_reserva,
        cantidad_personas = p_cantidad_personas,
        estado = p_estado,
        observaciones = p_observaciones
    WHERE
        id = p_id;
END$$

DROP PROCEDURE IF EXISTS sp_cancelReservation$$
CREATE PROCEDURE sp_cancelReservation(IN p_id INT)
BEGIN
    UPDATE reservas SET estado = 'cancelada' WHERE id = p_id;
END$$

DELIMITER ;

-- -----------------------------------------------------
-- Procedimientos para 'pedidos'
-- -----------------------------------------------------
DELIMITER $$

DROP PROCEDURE IF EXISTS sp_getAllOrders$$
CREATE PROCEDURE sp_getAllOrders()
BEGIN
    SELECT p.id, p.id_mesa, m.numero_mesa, p.id_usuario_mozo, u.nombre_completo AS nombre_mozo, p.estado, p.total, p.fecha_creacion
    FROM pedidos p
    JOIN mesas m ON p.id_mesa = m.id
    JOIN usuarios u ON p.id_usuario_mozo = u.id
    ORDER BY p.fecha_creacion DESC;
END$$

DROP PROCEDURE IF EXISTS sp_getOrderDetail$$
CREATE PROCEDURE sp_getOrderDetail(IN p_id_pedido INT)
BEGIN
    SELECT p.id, p.id_mesa, m.numero_mesa, p.id_usuario_mozo, u.nombre_completo AS nombre_mozo, p.estado, p.total, p.fecha_creacion, p.fecha_actualizacion
    FROM pedidos p
    JOIN mesas m ON p.id_mesa = m.id
    JOIN usuarios u ON p.id_usuario_mozo = u.id
    WHERE p.id = p_id_pedido;
END$$

DROP PROCEDURE IF EXISTS sp_getOrderItems$$
CREATE PROCEDURE sp_getOrderItems(IN p_id_pedido INT)
BEGIN
    SELECT dp.id_producto, pr.nombre AS nombre_producto, dp.cantidad, dp.precio_unitario, dp.subtotal
    FROM detalle_pedidos dp
    JOIN productos pr ON dp.id_producto = pr.id
    WHERE dp.id_pedido = p_id_pedido;
END$$

DROP PROCEDURE IF EXISTS sp_createOrder$$
CREATE PROCEDURE sp_createOrder(IN p_id_mesa INT, IN p_id_usuario_mozo INT, IN p_items_json JSON)
BEGIN
    DECLARE v_id_pedido INT;
    DECLARE v_total_calculado DECIMAL(10, 2) DEFAULT 0;
    DECLARE v_item JSON;
    DECLARE v_id_producto INT;
    DECLARE v_cantidad INT;
    DECLARE v_precio_unitario DECIMAL(10, 2);
    DECLARE i INT DEFAULT 0;
    START TRANSACTION;
    INSERT INTO pedidos (id_mesa, id_usuario_mozo, total) VALUES (p_id_mesa, p_id_usuario_mozo, 0);
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

DROP PROCEDURE IF EXISTS sp_updateOrderStatus$$
CREATE PROCEDURE sp_updateOrderStatus(IN p_id_pedido INT, IN p_nuevo_estado ENUM('recibido', 'en_preparacion', 'listo_para_servir', 'servido', 'pagado', 'cancelado'))
BEGIN
    UPDATE pedidos SET estado = p_nuevo_estado WHERE id = p_id_pedido;
END$$

DELIMITER ;

-- -----------------------------------------------------
-- Procedimientos para 'mesas'
-- -----------------------------------------------------
DELIMITER $$

DROP PROCEDURE IF EXISTS sp_getAllTables$$
CREATE PROCEDURE sp_getAllTables()
BEGIN
    SELECT id, numero_mesa, capacidad, estado FROM mesas ORDER BY numero_mesa ASC;
END$$

DROP PROCEDURE IF EXISTS sp_createTable$$
CREATE PROCEDURE sp_createTable(IN p_numero_mesa VARCHAR(10), IN p_capacidad INT, IN p_estado ENUM('disponible', 'ocupada', 'reservada', 'mantenimiento'))
BEGIN
    INSERT INTO mesas (numero_mesa, capacidad, estado) VALUES (p_numero_mesa, p_capacidad, p_estado);
    SELECT LAST_INSERT_ID() as id;
END$$

DROP PROCEDURE IF EXISTS sp_readOneTable$$
CREATE PROCEDURE sp_readOneTable(IN p_id INT)
BEGIN
    SELECT id, numero_mesa, capacidad, estado FROM mesas WHERE id = p_id;
END$$

DROP PROCEDURE IF EXISTS sp_updateTable$$
CREATE PROCEDURE sp_updateTable(IN p_id INT, IN p_numero_mesa VARCHAR(10), IN p_capacidad INT, IN p_estado ENUM('disponible', 'ocupada', 'reservada', 'mantenimiento'))
BEGIN
    UPDATE mesas SET numero_mesa = p_numero_mesa, capacidad = p_capacidad, estado = p_estado WHERE id = p_id;
END$$

DROP PROCEDURE IF EXISTS sp_deleteTable$$
CREATE PROCEDURE sp_deleteTable(IN p_id INT)
BEGIN
    DELETE FROM mesas WHERE id = p_id;
END$$

DELIMITER ;

-- -----------------------------------------------------
-- Procedimientos para 'usuarios' y 'roles'
-- -----------------------------------------------------
DELIMITER $$

DROP PROCEDURE IF EXISTS sp_getAllRoles$$
CREATE PROCEDURE sp_getAllRoles()
BEGIN
    SELECT id, nombre_rol FROM roles ORDER BY nombre_rol ASC;
END$$

DROP PROCEDURE IF EXISTS sp_getAllUsers$$
CREATE PROCEDURE sp_getAllUsers()
BEGIN
    SELECT u.id, u.username, u.nombre_completo, u.email, u.id_rol, r.nombre_rol, u.activo
    FROM usuarios u
    JOIN roles r ON u.id_rol = r.id
    ORDER BY u.nombre_completo ASC;
END$$

DROP PROCEDURE IF EXISTS sp_createUser$$
CREATE PROCEDURE sp_createUser(IN p_username VARCHAR(50), IN p_nombre_completo VARCHAR(100), IN p_email VARCHAR(100), IN p_password_hash VARCHAR(255), IN p_id_rol INT)
BEGIN
    INSERT INTO usuarios (username, nombre_completo, email, password_hash, id_rol) VALUES (p_username, p_nombre_completo, p_email, p_password_hash, p_id_rol);
    SELECT LAST_INSERT_ID() as id;
END$$

DROP PROCEDURE IF EXISTS sp_readOneUser$$
CREATE PROCEDURE sp_readOneUser(IN p_id INT)
BEGIN
    SELECT id, username, nombre_completo, email, id_rol, activo FROM usuarios WHERE id = p_id;
END$$

DROP PROCEDURE IF EXISTS sp_updateUser$$
CREATE PROCEDURE sp_updateUser(IN p_id INT, IN p_nombre_completo VARCHAR(100), IN p_email VARCHAR(100), IN p_id_rol INT, IN p_activo BOOLEAN, IN p_password_hash VARCHAR(255))
BEGIN
    UPDATE usuarios
    SET nombre_completo = p_nombre_completo, email = p_email, id_rol = p_id_rol, activo = p_activo,
        password_hash = IF(p_password_hash IS NOT NULL AND p_password_hash != '', p_password_hash, password_hash)
    WHERE id = p_id;
END$$

DROP PROCEDURE IF EXISTS sp_deleteUser$$
CREATE PROCEDURE sp_deleteUser(IN p_id INT)
BEGIN
    UPDATE usuarios SET activo = 0 WHERE id = p_id;
END$$

DELIMITER ;
