-- =====================================================
-- PROCEDIMIENTOS ALMACENADOS PARA MOVIMIENTOS (ACTUALIZADO)
-- =====================================================

-- Procedimiento para leer movimientos con filtros y paginación
DROP PROCEDURE IF EXISTS `sp_read_movimientos`;
DELIMITER $$
CREATE PROCEDURE `sp_read_movimientos`(
    IN p_filter VARCHAR(255),
    IN p_tipo_movimiento CHAR(1),
    IN p_tipo_entidad CHAR(1),
    IN p_id_almacen INT,
    IN p_anio CHAR(4),
    IN p_mes CHAR(2),
    IN p_limit INT,
    IN p_offset INT
)
BEGIN
    SELECT
        m.id,
        a.nombre as almacen_nombre,
        m.fecha_movimiento,
        m.tipo_movimiento,
        tm.descripcion as nombre_movimiento,
        tdv.nombre as tipo_documento_nombre,
        m.serie_documento,
        m.numero_documento,
        m.tipo_entidad,
        COALESCE(c.nombres_apellidos, prov.nombres_apellidos) as entidad_nombre,
        m.estado
    FROM movimientos m
    LEFT JOIN almacenes a ON m.id_almacen = a.id
    LEFT JOIN tipo_movimiento tm ON m.codigo_movimiento = tm.id
    LEFT JOIN tipo_documento_venta tdv ON m.id_tipo_documento_venta = tdv.id
    LEFT JOIN clientes c ON m.id_cliente = c.id AND m.tipo_entidad = 'C'
    LEFT JOIN proveedores prov ON m.id_proveedor = prov.id AND m.tipo_entidad = 'P'
    WHERE
        (p_filter IS NULL OR p_filter = '' OR
            (m.serie_documento LIKE CONCAT('%', p_filter, '%') OR
             m.numero_documento LIKE CONCAT('%', p_filter, '%') OR
             tm.descripcion LIKE CONCAT('%', p_filter, '%') OR
             COALESCE(c.nombres_apellidos, prov.nombres_apellidos) LIKE CONCAT('%', p_filter, '%')))
    AND (p_tipo_movimiento IS NULL OR p_tipo_movimiento = '' OR m.tipo_movimiento = p_tipo_movimiento)
    AND (p_tipo_entidad IS NULL OR p_tipo_entidad = '' OR m.tipo_entidad = p_tipo_entidad)
    AND (p_id_almacen IS NULL OR m.id_almacen = p_id_almacen)
    AND (p_anio IS NULL OR p_anio = '' OR m.anio = p_anio)
    AND (p_mes IS NULL OR p_mes = '' OR m.periodo = p_mes)
    ORDER BY m.fecha_movimiento DESC, m.id DESC
    LIMIT p_limit OFFSET p_offset;
END$$
DELIMITER ;

-- Procedimiento para contar movimientos con filtros
DROP PROCEDURE IF EXISTS `sp_count_movimientos`;
DELIMITER $$
CREATE PROCEDURE `sp_count_movimientos`(
    IN p_filter VARCHAR(255),
    IN p_tipo_movimiento CHAR(1),
    IN p_tipo_entidad CHAR(1),
    IN p_id_almacen INT,
    IN p_anio CHAR(4),
    IN p_mes CHAR(2)
)
BEGIN
    SELECT COUNT(m.id) as total
    FROM movimientos m
    LEFT JOIN tipo_movimiento tm ON m.codigo_movimiento = tm.id
    LEFT JOIN clientes c ON m.id_cliente = c.id AND m.tipo_entidad = 'C'
    LEFT JOIN proveedores prov ON m.id_proveedor = prov.id AND m.tipo_entidad = 'P'
    WHERE
        (p_filter IS NULL OR p_filter = '' OR
            (m.serie_documento LIKE CONCAT('%', p_filter, '%') OR
             m.numero_documento LIKE CONCAT('%', p_filter, '%') OR
             tm.descripcion LIKE CONCAT('%', p_filter, '%') OR
             COALESCE(c.nombres_apellidos, prov.nombres_apellidos) LIKE CONCAT('%', p_filter, '%')))
    AND (p_tipo_movimiento IS NULL OR p_tipo_movimiento = '' OR m.tipo_movimiento = p_tipo_movimiento)
    AND (p_tipo_entidad IS NULL OR p_tipo_entidad = '' OR m.tipo_entidad = p_tipo_entidad)
    AND (p_id_almacen IS NULL OR m.id_almacen = p_id_almacen)
    AND (p_anio IS NULL OR p_anio = '' OR m.anio = p_anio)
    AND (p_mes IS NULL OR p_mes = '' OR m.periodo = p_mes);
END$$
DELIMITER ;