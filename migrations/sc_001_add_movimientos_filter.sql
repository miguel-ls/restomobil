-- migrations/sc_001_add_movimientos_filter.sql

-- Drop procedure `sp_read_movimientos`
DROP PROCEDURE IF EXISTS sp_read_movimientos;

-- Create procedure `sp_read_movimientos`
DELIMITER $$
CREATE PROCEDURE `sp_read_movimientos`(
    IN p_filter VARCHAR(255),
    IN p_tipo_movimiento CHAR(1),
    IN p_tipo_entidad CHAR(1),
    IN p_limit INT,
    IN p_offset INT,
    IN p_id_almacen CHAR(1),
    IN p_anio CHAR(4),
    IN p_mes CHAR(2)
)
BEGIN
    SELECT
        m.id,
        m.fecha_movimiento,
        m.tipo_movimiento,
        tm.descripcion AS nombre_movimiento,
        tdv.nombre AS tipo_documento_nombre,
        m.serie_documento,
        m.numero_documento,
        m.tipo_entidad,
        COALESCE(c.nombres_apellidos, prov.nombres_apellidos) AS entidad_nombre,
        m.estado,
        m.id_almacen,
        a.nombre AS nombre_almacen
    FROM movimientos m
    LEFT JOIN tipo_movimiento tm ON m.codigo_movimiento = tm.id
    LEFT JOIN tipo_documento_venta tdv ON m.id_tipo_documento_venta = tdv.id
    LEFT JOIN clientes c ON m.id_cliente = c.id AND m.tipo_entidad = 'C'
    LEFT JOIN proveedores prov ON m.id_proveedor = prov.id AND m.tipo_entidad = 'P'
    LEFT JOIN almacenes a ON m.id_almacen = a.id
    WHERE
        (p_filter IS NULL OR p_filter = '' OR (
            m.serie_documento LIKE CONCAT('%', p_filter, '%') COLLATE utf8mb4_unicode_ci OR
            m.numero_documento LIKE CONCAT('%', p_filter, '%') COLLATE utf8mb4_unicode_ci OR
            tm.descripcion LIKE CONCAT('%', p_filter, '%') COLLATE utf8mb4_unicode_ci OR
            COALESCE(c.nombres_apellidos, prov.nombres_apellidos) LIKE CONCAT('%', p_filter, '%') COLLATE utf8mb4_unicode_ci OR
            a.nombre LIKE CONCAT('%', p_filter, '%') COLLATE utf8mb4_unicode_ci
        )) AND
        (p_tipo_movimiento IS NULL OR p_tipo_movimiento = '' OR m.tipo_movimiento COLLATE utf8mb4_unicode_ci = p_tipo_movimiento) AND
        (p_tipo_entidad IS NULL OR p_tipo_entidad = '' OR m.tipo_entidad COLLATE utf8mb4_unicode_ci = p_tipo_entidad) AND
        (p_anio IS NULL OR p_anio = '' OR m.anio COLLATE utf8mb4_unicode_ci = p_anio) AND
        (p_mes IS NULL OR p_mes = '' OR m.periodo COLLATE utf8mb4_unicode_ci = p_mes)
    ORDER BY m.fecha_movimiento DESC, m.id DESC
    LIMIT p_limit OFFSET p_offset;
END$$
DELIMITER ;

-- Drop procedure `sp_count_movimientos`
DROP PROCEDURE IF EXISTS sp_count_movimientos;

-- Create procedure `sp_count_movimientos`
DELIMITER $$
CREATE PROCEDURE `sp_count_movimientos`(
    IN p_filter VARCHAR(255),
    IN p_tipo_movimiento CHAR(1),
    IN p_tipo_entidad CHAR(1),
    IN p_id_almacen CHAR(1),
    IN p_anio CHAR(4),
    IN p_mes CHAR(2)
)
BEGIN
    SELECT
        COUNT(m.id) AS total
    FROM movimientos m
    LEFT JOIN tipo_movimiento tm ON m.codigo_movimiento = tm.id
    LEFT JOIN clientes c ON m.id_cliente = c.id AND m.tipo_entidad = 'C'
    LEFT JOIN proveedores prov ON m.id_proveedor = prov.id AND m.tipo_entidad = 'P'
    LEFT JOIN almacenes a ON m.id_almacen = a.id
    WHERE
        (p_filter IS NULL OR p_filter = '' OR (
            m.serie_documento LIKE CONCAT('%', p_filter, '%') COLLATE utf8mb4_unicode_ci OR
            m.numero_documento LIKE CONCAT('%', p_filter, '%') COLLATE utf8mb4_unicode_ci OR
            tm.descripcion LIKE CONCAT('%', p_filter, '%') COLLATE utf8mb4_unicode_ci OR
            COALESCE(c.nombres_apellidos, prov.nombres_apellidos) LIKE CONCAT('%', p_filter, '%') COLLATE utf8mb4_unicode_ci OR
            a.nombre LIKE CONCAT('%', p_filter, '%') COLLATE utf8mb4_unicode_ci
        )) AND
        (p_tipo_movimiento IS NULL OR p_tipo_movimiento = '' OR m.tipo_movimiento COLLATE utf8mb4_unicode_ci = p_tipo_movimiento) AND
        (p_tipo_entidad IS NULL OR p_tipo_entidad = '' OR m.tipo_entidad COLLATE utf8mb4_unicode_ci = p_tipo_entidad) AND
        (p_anio IS NULL OR p_anio = '' OR m.anio COLLATE utf8mb4_unicode_ci = p_anio) AND
        (p_mes IS NULL OR p_mes = '' OR m.periodo COLLATE utf8mb4_unicode_ci = p_mes);
END$$
DELIMITER ;