-- =====================================================
-- PROCEDIMIENTOS ALMACENADOS PARA CLIENTES Y PROVEEDORES
-- =====================================================

-- Procedimiento para obtener todos los clientes
DROP PROCEDURE IF EXISTS `sp_getAllClientes`;
DELIMITER $$
CREATE PROCEDURE `sp_getAllClientes`()
BEGIN
  SELECT
    c.id,
    c.id_tipo_documento_identidad,
    tdi.nombre AS tipo_documento_nombre,
    c.numero_documento,
    c.nombres_apellidos,
    c.direccion,
    c.codigo_ubigeo,
    c.email,
    c.telefono,
    c.estado
  FROM clientes c
    JOIN tipo_documento_identidad tdi
      ON c.id_tipo_documento_identidad = tdi.id
  ORDER BY c.nombres_apellidos;
END$$
DELIMITER ;

-- Procedimiento para obtener todos los proveedores
DROP PROCEDURE IF EXISTS `sp_getAllProveedores`;
DELIMITER $$
CREATE PROCEDURE `sp_getAllProveedores`()
BEGIN
  SELECT
    p.id,
    p.id_tipo_documento_identidad,
    tdi.nombre AS tipo_documento_nombre,
    p.numero_documento,
    p.nombres_apellidos,
    p.direccion,
    p.email,
    p.telefono,
    p.estado
  FROM `proveedores` p
    JOIN `tipo_documento_identidad` tdi
      ON p.id_tipo_documento_identidad = tdi.id
  ORDER BY p.nombres_apellidos ASC;
END$$
DELIMITER ;