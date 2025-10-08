-- Archivo de migración para las nuevas funcionalidades de pedidos y caja.

-- 1. Procedimiento para actualizar solo el estado de un pedido.
-- Este SP es invocado por las "Acciones Rápidas" para no afectar toda la orden.
DROP PROCEDURE IF EXISTS sp_updateOrderStatus;
DELIMITER //
CREATE PROCEDURE sp_updateOrderStatus(
    IN p_id_pedido INT,
    IN p_estado VARCHAR(50)
)
BEGIN
    -- Actualiza solo el estado del pedido
    UPDATE pedidos
    SET estado = p_estado
    WHERE id = p_id_pedido;

    -- Si el estado es 'completado', también actualiza el estado de la mesa a 'disponible'.
    -- Asumiendo que un pedido completado libera la mesa.
    IF p_estado = 'completado' OR p_estado = 'cancelado' THEN
        UPDATE mesas
        SET estado = 'disponible'
        WHERE id = (SELECT id_mesa FROM pedidos WHERE id = p_id_pedido);
    END IF;
END //
DELIMITER ;

-- 3. Procedimiento para verificar si un pedido ya tiene una venta generada.
DROP PROCEDURE IF EXISTS sp_verificarVentaPorPedido;
DELIMITER //
CREATE PROCEDURE sp_verificarVentaPorPedido(
    IN p_id_pedido INT
)
BEGIN
    SELECT COUNT(id) AS venta_existente
    FROM ventas
    WHERE id_pedido = p_id_pedido;
END //
DELIMITER ;


-- 2. Procedimiento para verificar si hay una caja abierta para una fecha específica.
-- Este SP es crucial para el endpoint de "procesar_venta.php".
DROP PROCEDURE IF EXISTS sp_verificarAperturaActiva;
DELIMITER //
CREATE PROCEDURE sp_verificarAperturaActiva(
    IN p_fecha DATE
)
BEGIN
    DECLARE v_apertura_count INT;
    DECLARE v_cierre_count INT;

    -- Contar las aperturas para la fecha dada
    SELECT COUNT(*)
    INTO v_apertura_count
    FROM apertura_cierre_caja
    WHERE fecha_movimiento = p_fecha AND tipo_movimiento = 'Apertura';

    -- Contar los cierres para la fecha dada
    SELECT COUNT(*)
    INTO v_cierre_count
    FROM apertura_cierre_caja
    WHERE fecha_movimiento = p_fecha AND tipo_movimiento = 'Cierre';

    -- Determinar si la caja está activa y devolver el resultado
    IF v_apertura_count > 0 AND v_cierre_count = 0 THEN
        SELECT 1 AS activa;
    ELSE
        SELECT 0 AS activa;
    END IF;
END //
DELIMITER ;