DELIMITER $$

DROP PROCEDURE IF EXISTS `sp_leer_venta_por_id`$$

CREATE PROCEDURE `sp_leer_venta_por_id`(IN `p_id_venta` INT)
BEGIN
    -- Primero, obtener los datos principales de la venta y del cliente
    SELECT
        v.id,
        v.fecha_emision,
        v.total,
        c.nombres_apellidos AS nombre_cliente,
        c.numero_documento AS ruc_cliente,
        c.direccion AS direccion_cliente
    FROM ventas v
    LEFT JOIN clientes c ON v.id_cliente = c.id
    WHERE v.id = p_id_venta;

    -- Segundo, obtener los detalles de la venta (items)
    SELECT
        vd.id_producto,
        p.nombre AS nombre_producto,
        vd.cantidad,
        vd.precio_unitario,
        vd.subtotal
    FROM venta_detalle vd
    JOIN productos p ON vd.id_producto = p.id
    WHERE vd.id_venta = p_id_venta;

END$$

DELIMITER ;