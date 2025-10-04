DELIMITER $$

DROP PROCEDURE IF EXISTS `sp_deleteIdentityDocumentType`$$
CREATE PROCEDURE `sp_deleteIdentityDocumentType`(IN p_id INT)
BEGIN
  DELETE FROM `tipo_documento_identidad` WHERE id = p_id;
END$$

DROP PROCEDURE IF EXISTS `sp_updateIdentityDocumentType`$$
CREATE PROCEDURE `sp_updateIdentityDocumentType`(
    IN p_id INT,
    IN p_codigo VARCHAR(2),
    IN p_nombre VARCHAR(100),
    IN p_descripcion TEXT,
    IN p_estado BOOLEAN
)
BEGIN
  UPDATE `tipo_documento_identidad`
  SET
      codigo = p_codigo,
      nombre = p_nombre,
      descripcion = p_descripcion,
      estado = p_estado
  WHERE
      id = p_id;
END$$

DROP PROCEDURE IF EXISTS `sp_createIdentityDocumentType`$$
CREATE PROCEDURE `sp_createIdentityDocumentType`(
    IN p_codigo VARCHAR(2),
    IN p_nombre VARCHAR(100),
    IN p_descripcion TEXT
)
BEGIN
  INSERT INTO `tipo_documento_identidad` (codigo, nombre, descripcion)
  VALUES (p_codigo, p_nombre, p_descripcion);
  SELECT LAST_INSERT_ID() AS id;
END$$

DROP PROCEDURE IF EXISTS `sp_getOneIdentityDocumentType`$$
CREATE PROCEDURE `sp_getOneIdentityDocumentType`(IN p_id INT)
BEGIN
  SELECT
    id,
    codigo,
    nombre,
    descripcion,
    estado
  FROM `tipo_documento_identidad`
  WHERE id = p_id;
END$$

DROP PROCEDURE IF EXISTS `sp_getAllIdentityDocumentTypes`$$
CREATE PROCEDURE `sp_getAllIdentityDocumentTypes`()
BEGIN
  SELECT
    id,
    codigo,
    nombre,
    descripcion,
    estado
  FROM `tipo_documento_identidad`
  ORDER BY nombre;
END$$

DELIMITER ;