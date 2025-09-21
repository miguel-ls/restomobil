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

DELIMITER ;
