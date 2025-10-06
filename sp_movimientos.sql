-- =====================================================
-- PROCEDIMIENTOS ALMACENADOS PARA MOVIMIENTOS
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
    SET @sql = CONCAT('
        SELECT
            m.id,
            m.anio,
            m.periodo,
            m.fecha_movimiento,
            tm.nombre as nombre_movimiento,
            m.tipo_documento,
            m.serie_documento,
            m.numero_documento,
            m.estado
        FROM movimientos m
        JOIN tipos_movimiento tm ON m.codigo_movimiento = tm.id
        WHERE (
            m.serie_documento LIKE ''%', p_filter, '%'' COLLATE utf8mb4_unicode_ci OR
            m.numero_documento LIKE ''%', p_filter, '%'' COLLATE utf8mb4_unicode_ci OR
            tm.nombre LIKE ''%', p_filter, '%'' COLLATE utf8mb4_unicode_ci
        )
        ORDER BY m.fecha_movimiento DESC, m.id DESC
        LIMIT ', p_limit, ' OFFSET ', p_offset, ';
    ');
    PREPARE stmt FROM @sql;
    EXECUTE stmt;
    DEALLOCATE PREPARE stmt;
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
    JOIN tipos_movimiento tm ON m.codigo_movimiento = tm.id
    WHERE (
        m.serie_documento LIKE CONCAT('%', p_filter, '%') COLLATE utf8mb4_unicode_ci OR
        m.numero_documento LIKE CONCAT('%', p_filter, '%') COLLATE utf8mb4_unicode_ci OR
        tm.nombre LIKE CONCAT('%', p_filter, '%') COLLATE utf8mb4_unicode_ci
    );
END$$
DELIMITER ;

-- Procedimiento para obtener un movimiento por su ID (cabecera y detalle)
DROP PROCEDURE IF EXISTS `sp_get_movimiento_by_id`;
DELIMITER $$
CREATE PROCEDURE `sp_get_movimiento_by_id`(
    IN p_id BIGINT
)
BEGIN
    -- Obtener cabecera
    SELECT
        m.*,
        tm.nombre as nombre_movimiento
    FROM movimientos m
    JOIN tipos_movimiento tm ON m.codigo_movimiento = tm.id
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

-- Procedimiento para crear un movimiento y su detalle
DROP PROCEDURE IF EXISTS `sp_create_movimiento`;
DELIMITER $$
CREATE PROCEDURE `sp_create_movimiento`(
    IN p_anio CHAR(4),
    IN p_periodo CHAR(2),
    IN p_tipo_movimiento ENUM('E', 'S'),
    IN p_codigo_movimiento INT,
    IN p_fecha_movimiento DATE,
    IN p_tipo_documento VARCHAR(50),
    IN p_serie_documento VARCHAR(10),
    IN p_numero_documento VARCHAR(20),
    IN p_tipo_entidad ENUM('C', 'P'),
    IN p_id_entidad BIGINT,
    IN p_detalle JSON
)
BEGIN
    DECLARE v_id_movimiento BIGINT;
    DECLARE v_index INT DEFAULT 0;
    DECLARE v_item_count INT;
    DECLARE v_item INT;
    DECLARE v_id_producto INT;
    DECLARE v_cantidad DECIMAL(14,5);
    DECLARE v_descripcion TEXT;
    DECLARE v_codigo_unidad_medida VARCHAR(3);
    DECLARE v_costo_unitario DECIMAL(14,5);

    -- Iniciar transacción
    START TRANSACTION;

    -- Insertar cabecera
    INSERT INTO movimientos (anio, periodo, tipo_movimiento, codigo_movimiento, fecha_movimiento, tipo_documento, serie_documento, numero_documento, tipo_entidad, id_entidad, estado)
    VALUES (p_anio, p_periodo, p_tipo_movimiento, p_codigo_movimiento, p_fecha_movimiento, p_tipo_documento, p_serie_documento, p_numero_documento, p_tipo_entidad, p_id_entidad, 'Activado');

    SET v_id_movimiento = LAST_INSERT_ID();
    SET v_item_count = JSON_LENGTH(p_detalle);

    -- Insertar detalle
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

    -- Confirmar transacción
    COMMIT;

    SELECT v_id_movimiento as id;
END$$
DELIMITER ;

-- Procedimiento para actualizar un movimiento y su detalle
DROP PROCEDURE IF EXISTS `sp_update_movimiento`;
DELIMITER $$
CREATE PROCEDURE `sp_update_movimiento`(
    IN p_id_movimiento BIGINT,
    IN p_anio CHAR(4),
    IN p_periodo CHAR(2),
    IN p_tipo_movimiento ENUM('E', 'S'),
    IN p_codigo_movimiento INT,
    IN p_fecha_movimiento DATE,
    IN p_tipo_documento VARCHAR(50),
    IN p_serie_documento VARCHAR(10),
    IN p_numero_documento VARCHAR(20),
    IN p_tipo_entidad ENUM('C', 'P'),
    IN p_id_entidad BIGINT,
    IN p_estado ENUM('Activado', 'Desactivado'),
    IN p_detalle JSON
)
BEGIN
    DECLARE v_index INT DEFAULT 0;
    DECLARE v_item_count INT;
    DECLARE v_item INT;
    DECLARE v_id_producto INT;
    DECLARE v_cantidad DECIMAL(14,5);
    DECLARE v_descripcion TEXT;
    DECLARE v_codigo_unidad_medida VARCHAR(3);
    DECLARE v_costo_unitario DECIMAL(14,5);

    -- Iniciar transacción
    START TRANSACTION;

    -- Actualizar cabecera
    UPDATE movimientos SET
        anio = p_anio,
        periodo = p_periodo,
        tipo_movimiento = p_tipo_movimiento,
        codigo_movimiento = p_codigo_movimiento,
        fecha_movimiento = p_fecha_movimiento,
        tipo_documento = p_tipo_documento,
        serie_documento = p_serie_documento,
        numero_documento = p_numero_documento,
        tipo_entidad = p_tipo_entidad,
        id_entidad = p_id_entidad,
        estado = p_estado
    WHERE id = p_id_movimiento;

    -- Eliminar detalle existente
    DELETE FROM movimientos_detalle WHERE id_movimiento = p_id_movimiento;

    -- Insertar nuevo detalle
    SET v_item_count = JSON_LENGTH(p_detalle);
    WHILE v_index < v_item_count DO
        SET v_item = JSON_UNQUOTE(JSON_EXTRACT(p_detalle, CONCAT('$[', v_index, '].item')));
        SET v_id_producto = JSON_UNQUOTE(JSON_EXTRACT(p_detalle, CONCAT('$[', v_index, '].id_producto')));
        SET v_cantidad = JSON_UNQUOTE(JSON_EXTRACT(p_detalle, CONCAT('$[', v_index, '].cantidad')));
        SET v_descripcion = JSON_UNQUOTE(JSON_EXTRACT(p_detalle, CONCAT('$[', v_index, '].descripcion')));
        SET v_codigo_unidad_medida = JSON_UNQUOTE(JSON_EXTRACT(p_detalle, CONCAT('$[', v_index, '].codigo_unidad_medida')));
        SET v_costo_unitario = JSON_UNQUOTE(JSON_EXTRACT(p_detalle, CONCAT('$[', v_index, '].costo_unitario')));

        INSERT INTO movimientos_detalle (id_movimiento, item, id_producto, cantidad, descripcion, codigo_unidad_medida, costo_unitario, costo_neto)
        VALUES (p_id_movimiento, v_item, v_id_producto, v_cantidad, v_descripcion, v_codigo_unidad_medida, v_costo_unitario, v_cantidad * v_costo_unitario);

        SET v_index = v_index + 1;
    END WHILE;

    -- Confirmar transacción
    COMMIT;
END$$
DELIMITER ;

-- Procedimiento para anular un movimiento (cambio de estado)
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