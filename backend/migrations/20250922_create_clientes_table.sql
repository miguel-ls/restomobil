-- Migration script for creating the clientes table and stored procedures

-- -----------------------------------------------------
-- Table `clientes`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `clientes`;

CREATE TABLE `clientes` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `id_tipo_documento_identidad` INT NOT NULL,
  `numero_documento` VARCHAR(20) NOT NULL,
  `nombres_apellidos` VARCHAR(200) NOT NULL,
  `direccion` VARCHAR(255) NULL,
  `codigo_ubigeo` VARCHAR(10) NULL,
  `email` VARCHAR(100) NULL,
  `telefono` VARCHAR(20) NULL,
  `estado` ENUM('Activado', 'Desactivado') NOT NULL DEFAULT 'Activado',
  PRIMARY KEY (`id`),
  UNIQUE INDEX `numero_documento_UNIQUE` (`numero_documento` ASC),
  CONSTRAINT `fk_clientes_tipo_documento_identidad`
    FOREIGN KEY (`id_tipo_documento_identidad`)
    REFERENCES `tipo_documento_identidad` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION
) ENGINE = InnoDB;

-- -----------------------------------------------------
-- Stored Procedures for `clientes`
-- -----------------------------------------------------
DELIMITER $$

-- Get all clients
DROP PROCEDURE IF EXISTS `sp_getAllClientes`$$
CREATE PROCEDURE `sp_getAllClientes`()
BEGIN
    SELECT
        c.id,
        c.id_tipo_documento_identidad,
        tdi.nombre as tipo_documento_nombre,
        c.numero_documento,
        c.nombres_apellidos,
        c.direccion,
        c.codigo_ubigeo,
        c.email,
        c.telefono,
        c.estado
    FROM
        clientes c
    JOIN
        tipo_documento_identidad tdi ON c.id_tipo_documento_identidad = tdi.id
    ORDER BY
        c.nombres_apellidos;
END$$

-- Get one client
DROP PROCEDURE IF EXISTS `sp_getOneCliente`$$
CREATE PROCEDURE `sp_getOneCliente`(IN p_id INT)
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
        clientes
    WHERE
        id = p_id;
END$$

-- Create a new client
DROP PROCEDURE IF EXISTS `sp_createCliente`$$
CREATE PROCEDURE `sp_createCliente`(
    IN p_id_tipo_documento_identidad INT,
    IN p_numero_documento VARCHAR(20),
    IN p_nombres_apellidos VARCHAR(200),
    IN p_direccion VARCHAR(255),
    IN p_codigo_ubigeo VARCHAR(10),
    IN p_email VARCHAR(100),
    IN p_telefono VARCHAR(20)
)
BEGIN
    INSERT INTO clientes (
        id_tipo_documento_identidad,
        numero_documento,
        nombres_apellidos,
        direccion,
        codigo_ubigeo,
        email,
        telefono
    ) VALUES (
        p_id_tipo_documento_identidad,
        p_numero_documento,
        p_nombres_apellidos,
        p_direccion,
        p_codigo_ubigeo,
        p_email,
        p_telefono
    );
    SELECT LAST_INSERT_ID() as id;
END$$

-- Update a client
DROP PROCEDURE IF EXISTS `sp_updateCliente`$$
CREATE PROCEDURE `sp_updateCliente`(
    IN p_id INT,
    IN p_id_tipo_documento_identidad INT,
    IN p_numero_documento VARCHAR(20),
    IN p_nombres_apellidos VARCHAR(200),
    IN p_direccion VARCHAR(255),
    IN p_codigo_ubigeo VARCHAR(10),
    IN p_email VARCHAR(100),
    IN p_telefono VARCHAR(20),
    IN p_estado ENUM('Activado', 'Desactivado')
)
BEGIN
    UPDATE clientes
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

-- Delete a client (logical delete)
DROP PROCEDURE IF EXISTS `sp_deleteCliente`$$
CREATE PROCEDURE `sp_deleteCliente`(IN p_id INT)
BEGIN
    UPDATE clientes SET estado = 'Desactivado' WHERE id = p_id;
END$$

DELIMITER ;
