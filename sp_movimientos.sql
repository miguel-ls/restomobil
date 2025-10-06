-- =====================================================
-- PROCEDIMIENTOS ALMACENADOS PARA MOVIMIENTOS (ESTRUCTURA CORREGIDA)
-- =====================================================

-- Procedimiento para leer movimientos con filtros y paginación
DROP PROCEDURE IF EXISTS `sp_read_movimientos`;
DELIMITER $$
CREATE PROCEDURE `sp_read_movimientos`(
    IN p_filter VARCHAR(255),
    IN p_limit INT,
    IN p_offset INT
)
BEGIN
    SELECT
        m.id,
        m.anio,
        m.periodo,
        m.fecha_movimiento,
        tm.descripcion as nombre_movimiento,
        tdv.nombre as tipo_documento_nombre,
        m.serie_documento,
        m.numero_documento,
        m.estado
    FROM movimientos m
    JOIN tipo_movimiento tm ON m.codigo_movimiento = tm.id
    LEFT JOIN tipo_documento_venta tdv ON m.id_tipo_documento_venta = tdv.id
    WHERE (
        m.serie_documento LIKE CONCAT('%', p_filter, '%') COLLATE utf8mb4_unicode_ci OR
        m.numero_documento LIKE CONCAT('%', p_filter, '%') COLLATE utf8mb4_unicode_ci OR
        tm.descripcion LIKE CONCAT('%', p_filter, '%') COLLATE utf8mb4_unicode_ci
    )
    ORDER BY m.fecha_movimiento DESC, m.id DESC
    LIMIT p_limit OFFSET p_offset;
END$$
DELIMITER ;

-- Procedimiento para contar movimientos con filtros
DROP PROCEDURE IF EXISTS `sp_count_movimientos`;
DELIMITER $$
CREATE PROCEDURE `sp_count_movimientos`(
    IN p_filter VARCHAR(255)
)
BEGIN
    SELECT COUNT(m.id) as total
    FROM movimientos m
    JOIN tipo_movimiento tm ON m.codigo_movimiento = tm.id
    WHERE (
        m.serie_documento LIKE CONCAT('%', p_filter, '%') COLLATE utf8mb4_unicode_ci OR
        m.numero_documento LIKE CONCAT('%', p_filter, '%') COLLATE utf8mb4_unicode_ci OR
        tm.descripcion LIKE CONCAT('%', p_filter, '%') COLLATE utf8mb4_unicode_ci
    );
END$$
DELIMITER ;

-- Procedimiento para obtener un movimiento por su ID
DROP PROCEDURE IF EXISTS `sp_get_movimiento_by_id`;
DELIMITER $$
CREATE PROCEDURE `sp_get_movimiento_by_id`(
    IN p_id BIGINT
)
BEGIN
    -- Obtener cabecera
    SELECT
        m.*,
        tm.descripcion as nombre_movimiento
    FROM movimientos m
    JOIN tipo_movimiento tm ON m.codigo_movimiento = tm.id
    WHERE m.id = p_id;

    -- Obtener detalle
    SELECT
        md.*,
        p.nombre as nombre_producto
    FROM movimientos_detalle md
    JOIN productos p ON md.id_producto = p.id
    WHERE md.id_movimiento = p_id
    ORDER BY md.item;
END$$
DELIMITER ;

-- Procedimiento para crear un movimiento
DROP PROCEDURE IF EXISTS `sp_create_movimiento`;
DELIMITER $$
CREATE PROCEDURE `sp_create_movimiento`(
    IN p_anio CHAR(4),
    IN p_periodo CHAR(2),
    IN p_codigo_movimiento INT,
    IN p_fecha_movimiento DATE,
    IN p_id_tipo_documento_venta INT,
    IN p_serie_documento VARCHAR(10),
    IN p_numero_documento VARCHAR(20),
    IN p_tipo_entidad ENUM('C', 'P'),
    IN p_id_cliente INT,
    IN p_id_proveedor INT,
    IN p_detalle JSON
)
BEGIN
    DECLARE v_id_movimiento BIGINT;
    DECLARE v_tipo_movimiento_char CHAR(1);
    DECLARE v_index INT DEFAULT 0;
    DECLARE v_item_count INT;
    DECLARE v_item INT;
    DECLARE v_id_producto INT;
    DECLARE v_cantidad DECIMAL(14,5);
    DECLARE v_descripcion TEXT;
    DECLARE v_codigo_unidad_medida VARCHAR(3);
    DECLARE v_costo_unitario DECIMAL(14,5);

    SELECT tipo INTO v_tipo_movimiento_char FROM tipo_movimiento WHERE id = p_codigo_movimiento;

    START TRANSACTION;

    INSERT INTO movimientos (anio, periodo, tipo_movimiento, codigo_movimiento, fecha_movimiento, id_tipo_documento_venta, serie_documento, numero_documento, tipo_entidad, id_cliente, id_proveedor, estado)
    VALUES (p_anio, p_periodo, v_tipo_movimiento_char, p_codigo_movimiento, p_fecha_movimiento, p_id_tipo_documento_venta, p_serie_documento, p_numero_documento, p_tipo_entidad, p_id_cliente, p_id_proveedor, 'Activado');

    SET v_id_movimiento = LAST_INSERT_ID();
    SET v_item_count = JSON_LENGTH(p_detalle);

    WHILE v_index < v_item_count DO
        SET v_item = JSON_UNQUOTE(JSON_EXTRACT(p_detalle, CONCAT('$[', v_index, '].item')));
        SET v_id_producto = JSON_UNQUOTE(JSON_EXTRACT(p_detalle, CONCAT('$[', v_index, '].id_producto')));
        SET v_cantidad = JSON_UNQUOTE(JSON_EXTRACT(p_detalle, CONCAT('$[', v_index, '].cantidad')));
        SET v_descripcion = JSON_UNQUOTE(JSON_EXTRACT(p_detalle, CONCAT('$[', v_index, '].descripcion')));
        SET v_codigo_unidad_medida = JSON_UNQUOTE(JSON_EXTRACT(p_detalle, CONCAT('$[', v_index, '].codigo_unidad_medida')));
        SET v_costo_unitario = JSON_UNQUOTE(JSON_EXTRACT(p_detalle, CONCAT('$[', v_index, '].costo_unitario')));

        INSERT INTO movimientos_detalle (id_movimiento, item, id_producto, cantidad, descripcion, codigo_unidad_medida, costo_unitario, costo_neto)
        VALUES (v_id_movimiento, v_item, v_id_producto, v_cantidad, v_descripcion, v_codigo_unidad_medida, v_costo_unitario, v_cantidad * v_costo_unitario);

        SET v_index = v_index + 1;
    END WHILE;

    COMMIT;
    SELECT v_id_movimiento as id;
END$$
DELIMITER ;

-- Procedimiento para actualizar un movimiento
DROP PROCEDURE IF EXISTS `sp_update_movimiento`;
DELIMITER $$
CREATE PROCEDURE `sp_update_movimiento`(
    IN p_id_movimiento BIGINT,
    IN p_anio CHAR(4),
    IN p_periodo CHAR(2),
    IN p_codigo_movimiento INT,
    IN p_fecha_movimiento DATE,
    IN p_id_tipo_documento_venta INT,
    IN p_serie_documento VARCHAR(10),
    IN p_numero_documento VARCHAR(20),
    IN p_tipo_entidad ENUM('C', 'P'),
    IN p_id_cliente INT,
    IN p_id_proveedor INT,
    IN p_estado ENUM('Activado', 'Desactivado'),
    IN p_detalle JSON
)
BEGIN
    DECLARE v_tipo_movimiento_char CHAR(1);
    DECLARE v_index INT DEFAULT 0;
    DECLARE v_item_count INT;

    SELECT tipo INTO v_tipo_movimiento_char FROM tipo_movimiento WHERE id = p_codigo_movimiento;

    START TRANSACTION;

    UPDATE movimientos SET
        anio = p_anio,
        periodo = p_periodo,
        tipo_movimiento = v_tipo_movimiento_char,
        codigo_movimiento = p_codigo_movimiento,
        fecha_movimiento = p_fecha_movimiento,
        id_tipo_documento_venta = p_id_tipo_documento_venta,
        serie_documento = p_serie_documento,
        numero_documento = p_numero_documento,
        tipo_entidad = p_tipo_entidad,
        id_cliente = p_id_cliente,
        id_proveedor = p_id_proveedor,
        estado = p_estado
    WHERE id = p_id_movimiento;

    DELETE FROM movimientos_detalle WHERE id_movimiento = p_id_movimiento;

    SET v_item_count = JSON_LENGTH(p_detalle);
    WHILE v_index < v_item_count DO
        INSERT INTO movimientos_detalle (id_movimiento, item, id_producto, cantidad, descripcion, codigo_unidad_medida, costo_unitario, costo_neto)
        VALUES (
            p_id_movimiento,
            JSON_UNQUOTE(JSON_EXTRACT(p_detalle, CONCAT('$[', v_index, '].item'))),
            JSON_UNQUOTE(JSON_EXTRACT(p_detalle, CONCAT('$[', v_index, '].id_producto'))),
            JSON_UNQUOTE(JSON_EXTRACT(p_detalle, CONCAT('$[', v_index, '].cantidad'))),
            JSON_UNQUOTE(JSON_EXTRACT(p_detalle, CONCAT('$[', v_index, '].descripcion'))),
            JSON_UNQUOTE(JSON_EXTRACT(p_detalle, CONCAT('$[', v_index, '].codigo_unidad_medida'))),
            JSON_UNQUOTE(JSON_EXTRACT(p_detalle, CONCAT('$[', v_index, '].costo_unitario'))),
            JSON_UNQUOTE(JSON_EXTRACT(p_detalle, CONCAT('$[', v_index, '].cantidad'))) * JSON_UNQUOTE(JSON_EXTRACT(p_detalle, CONCAT('$[', v_index, '].costo_unitario')))
        );
        SET v_index = v_index + 1;
    END WHILE;

    COMMIT;
END$$
DELIMITER ;

-- Procedimiento para anular un movimiento
DROP PROCEDURE IF EXISTS `sp_delete_movimiento`;
DELIMITER $$
CREATE PROCEDURE `sp_delete_movimiento`(
    IN p_id_movimiento BIGINT
)
BEGIN
    UPDATE movimientos
    SET estado = 'Desactivado'
    WHERE id = p_id_movimiento;
END$$
DELIMITER ;