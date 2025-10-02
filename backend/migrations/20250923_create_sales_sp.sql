DELIMITER $$

-- Procedimiento para leer ventas con filtros y paginación
DROP PROCEDURE IF EXISTS `sp_leer_ventas`$$
CREATE PROCEDURE `sp_leer_ventas`(
    IN p_fecha_inicio DATE,
    IN p_fecha_fin DATE,
    IN p_estado VARCHAR(10),
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
    v.fecha_emision,
    c.nombres_apellidos AS nombre_cliente,
    tdv.nombre AS tipo_documento,
    s.serie,
    v.numero_documento,
    v.total,
    v.porcentaje,
    v.base,
    v.impuesto,
    v.estado
  FROM ventas v
  LEFT JOIN clientes c ON v.id_cliente = c.id
  JOIN tipo_documento_venta tdv ON v.id_tipo_documento_venta = tdv.id
  JOIN series_documentos s ON v.id_serie_documento = s.id
  WHERE (p_fecha_inicio IS NULL OR v.fecha_emision >= p_fecha_inicio)
    AND (p_fecha_fin IS NULL OR v.fecha_emision <= p_fecha_fin)
    AND (p_estado IS NULL OR p_estado = 'Todos' OR v.estado COLLATE utf8mb4_unicode_ci = p_estado)
    AND (p_id_tipo_documento IS NULL OR v.id_tipo_documento_venta = p_id_tipo_documento)
    AND (p_search IS NULL OR c.nombres_apellidos COLLATE utf8mb4_unicode_ci LIKE CONCAT('%', p_search, '%') OR v.numero_documento COLLATE utf8mb4_unicode_ci LIKE CONCAT('%', p_search, '%'))
  ORDER BY v.fecha_emision DESC
  LIMIT v_offset, p_limit;
END$$

-- Procedimiento para contar ventas con filtros
DROP PROCEDURE IF EXISTS `sp_contar_ventas`$$
CREATE PROCEDURE `sp_contar_ventas`(
    IN p_fecha_inicio DATE,
    IN p_fecha_fin DATE,
    IN p_estado VARCHAR(10),
    IN p_id_tipo_documento INT,
    IN p_search VARCHAR(100)
)
BEGIN
    SELECT COUNT(v.id) as total
    FROM ventas v
    LEFT JOIN clientes c ON v.id_cliente = c.id
    JOIN tipo_documento_venta tdv ON v.id_tipo_documento_venta = tdv.id
    JOIN series_documentos s ON v.id_serie_documento = s.id
    WHERE (p_fecha_inicio IS NULL OR v.fecha_emision >= p_fecha_inicio)
      AND (p_fecha_fin IS NULL OR v.fecha_emision <= p_fecha_fin)
      AND (p_estado IS NULL OR p_estado = 'Todos' OR v.estado COLLATE utf8mb4_unicode_ci = p_estado)
      AND (p_id_tipo_documento IS NULL OR v.id_tipo_documento_venta = p_id_tipo_documento)
      AND (p_search IS NULL OR c.nombres_apellidos COLLATE utf8mb4_unicode_ci LIKE CONCAT('%', p_search, '%') OR v.numero_documento COLLATE utf8mb4_unicode_ci LIKE CONCAT('%', p_search, '%'));
END$$

DELIMITER ;