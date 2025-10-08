-- Hotfix para añadir id_pedido al listado de ventas.

DROP PROCEDURE IF EXISTS sp_leer_ventas;
DELIMITER //
CREATE PROCEDURE sp_leer_ventas(
    IN p_fecha_inicio DATE,
    IN p_fecha_fin DATE,
    IN p_estado VARCHAR(20),
    IN p_id_tipo_documento INT,
    IN p_search VARCHAR(100),
    IN p_page INT,
    IN p_limit INT
)
BEGIN
    DECLARE v_offset INT;
    SET v_offset = (p_page - 1) * p_limit;

    SELECT
        v.id,
        v.id_pedido, -- <--- Columna añadida
        v.fecha_emision,
        c.nombres_apellidos AS nombre_cliente,
        tdv.descripcion,
        sd.serie,
        v.numero_documento,
        i.porcentaje,
        v.base,
        v.impuesto,
        v.total,
        v.estado
    FROM ventas v
    LEFT JOIN clientes c ON v.id_cliente = c.id
    LEFT JOIN tipos_documentos_venta tdv ON v.id_tipo_documento_venta = tdv.id
    LEFT JOIN series_documentos sd ON v.id_serie_documento = sd.id
    LEFT JOIN impuestos i ON tdv.id_impuesto = i.id
    WHERE
        (p_fecha_inicio IS NULL OR v.fecha_emision >= p_fecha_inicio) AND
        (p_fecha_fin IS NULL OR v.fecha_emision <= p_fecha_fin) AND
        (p_estado = 'Todos' OR v.estado = p_estado) AND
        (p_id_tipo_documento IS NULL OR v.id_tipo_documento_venta = p_id_tipo_documento) AND
        (p_search IS NULL OR c.nombres_apellidos LIKE CONCAT('%', p_search, '%') OR v.numero_documento LIKE CONCAT('%', p_search, '%'))
    ORDER BY v.fecha_emision DESC, v.id DESC
    LIMIT v_offset, p_limit;
END //
DELIMITER ;