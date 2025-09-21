-- Script para actualizar la lógica de obtención de mesas disponibles

DELIMITER $$

-- Actualizar procedimiento para que solo devuelva mesas que no son de servicio libre
DROP PROCEDURE IF EXISTS sp_getAvailableTables$$
CREATE PROCEDURE sp_getAvailableTables()
BEGIN
    SELECT id, numero_mesa, capacidad, estado, es_libre
    FROM mesas
    WHERE estado = 'disponible' AND es_libre = 0;
END$$

DELIMITER ;
