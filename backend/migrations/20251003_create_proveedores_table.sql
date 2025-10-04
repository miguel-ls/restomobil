-- =============================================
-- MIGRATION SCRIPT FOR PROVEEDORES FEATURE
-- =============================================

-- 1. Create 'proveedores' table
CREATE TABLE `proveedores` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_tipo_documento_identidad` int(11) NOT NULL,
  `numero_documento` varchar(20) NOT NULL,
  `nombres_apellidos` varchar(200) NOT NULL,
  `direccion` varchar(255) DEFAULT NULL,
  `codigo_ubigeo` varchar(10) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `telefono` varchar(20) DEFAULT NULL,
  `estado` enum('Activado','Desactivado') NOT NULL DEFAULT 'Activado',
  `fecha_creacion` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `fecha_modificacion` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  CONSTRAINT `fk_proveedores_tipo_documento_identidad` FOREIGN KEY (`id_tipo_documento_identidad`) REFERENCES `tipo_documento_identidad` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- 2. Add indexes
ALTER TABLE `proveedores`
ADD UNIQUE INDEX `idx_unique_proveedor_documento` (`id_tipo_documento_identidad`, `numero_documento`);

-- 3. Stored Procedures for 'proveedores'
DELIMITER $$

-- Get all proveedores
DROP PROCEDURE IF EXISTS `sp_getAllProveedores`$$
CREATE PROCEDURE `sp_getAllProveedores`()
BEGIN
    SELECT
        p.id,
        p.id_tipo_documento_identidad,
        tdi.nombre as tipo_documento_nombre,
        p.numero_documento,
        p.nombres_apellidos,
        p.direccion,
        p.email,
        p.telefono,
        p.estado
    FROM
        `proveedores` p
    JOIN
        `tipo_documento_identidad` tdi ON p.id_tipo_documento_identidad = tdi.id
    ORDER BY
        p.nombres_apellidos ASC;
END$$

-- Get one proveedor by ID
DROP PROCEDURE IF EXISTS `sp_getOneProveedor`$$
CREATE PROCEDURE `sp_getOneProveedor`(IN p_id INT)
BEGIN
    SELECT
        id,
        id_tipo_documento_identidad,
        numero_documento,
        nombres_apellidos,
        direccion,
        codigo_ubigeo,
        email,
        telefono,
        estado
    FROM
        `proveedores`
    WHERE
        id = p_id;
END$$

-- Create a new proveedor
DROP PROCEDURE IF EXISTS `sp_createProveedor`$$
CREATE PROCEDURE `sp_createProveedor`(
    IN p_id_tipo_documento_identidad INT,
    IN p_numero_documento VARCHAR(20),
    IN p_nombres_apellidos VARCHAR(200),
    IN p_direccion VARCHAR(255),
    IN p_codigo_ubigeo VARCHAR(10),
    IN p_email VARCHAR(100),
    IN p_telefono VARCHAR(20)
)
BEGIN
    INSERT INTO `proveedores` (id_tipo_documento_identidad, numero_documento, nombres_apellidos, direccion, codigo_ubigeo, email, telefono)
    VALUES (p_id_tipo_documento_identidad, p_numero_documento, p_nombres_apellidos, p_direccion, p_codigo_ubigeo, p_email, p_telefono);
    SELECT LAST_INSERT_ID() as id;
END$$

-- Update a proveedor
DROP PROCEDURE IF EXISTS `sp_updateProveedor`$$
CREATE PROCEDURE `sp_updateProveedor`(
    IN p_id INT,
    IN p_id_tipo_documento_identidad INT,
    IN p_numero_documento VARCHAR(20),
    IN p_nombres_apellidos VARCHAR(200),
    IN p_direccion VARCHAR(255),
    IN p_codigo_ubigeo VARCHAR(10),
    IN p_email VARCHAR(100),
    IN p_telefono VARCHAR(20),
    IN p_estado ENUM('Activado','Desactivado')
)
BEGIN
    UPDATE `proveedores`
    SET
        id_tipo_documento_identidad = p_id_tipo_documento_identidad,
        numero_documento = p_numero_documento,
        nombres_apellidos = p_nombres_apellidos,
        direccion = p_direccion,
        codigo_ubigeo = p_codigo_ubigeo,
        email = p_email,
        telefono = p_telefono,
        estado = p_estado
    WHERE
        id = p_id;
END$$

-- Delete a proveedor (logical delete)
DROP PROCEDURE IF EXISTS `sp_deleteProveedor`$$
CREATE PROCEDURE `sp_deleteProveedor`(IN p_id INT)
BEGIN
    UPDATE `proveedores`
    SET estado = 'Desactivado'
    WHERE id = p_id;
END$$

DELIMITER ;