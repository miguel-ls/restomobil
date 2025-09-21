-- Script para añadir la columna es_libre a la tabla mesas
ALTER TABLE `mesas` ADD COLUMN `es_libre` BOOLEAN NOT NULL DEFAULT TRUE;

DELIMITER $$

-- Procedimientos para 'mesas'
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
