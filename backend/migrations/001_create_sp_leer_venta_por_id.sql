DELIMITER $$

DROP PROCEDURE IF EXISTS `sp_leer_venta_por_id`$$

CREATE PROCEDURE `sp_leer_venta_por_id`(IN `p_id_venta` INT)
BEGIN
    -- Primero, obtener los datos principales de la venta, del cliente y de la empresa
    SELECT
        v.id,
        v.fecha_emision,
        v.total,
        v.base,
        v.impuesto,
        v.porcentaje,
        v.estado,
        v.numero_documento,
        c.nombres_apellidos AS nombre_cliente,
        c.numero_documento AS ruc_cliente,
        c.direccion AS direccion_cliente,
        tdv.nombre AS tipo_documento,
        sd.serie,
        e.ruc AS ruc_empresa,
        e.nombre_largo,
        e.direccion,
        e.telefonos,
        e.web,
        e.email,
        e.logo_url
    FROM ventas v
    LEFT JOIN clientes c ON v.id_cliente = c.id
    LEFT JOIN tipo_documento_venta tdv ON v.id_tipo_documento_venta = tdv.id
    LEFT JOIN series_documentos sd ON v.id_serie_documento = sd.id
    LEFT JOIN empresas e ON sd.id_empresa = e.id
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