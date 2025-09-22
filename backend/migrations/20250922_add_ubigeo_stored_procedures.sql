DELIMITER $$

DROP PROCEDURE IF EXISTS `sp_getDepartamentos`$$
CREATE PROCEDURE `sp_getDepartamentos`()
BEGIN
    SELECT id, nombre FROM ubigeo_departamentos ORDER BY nombre;
END$$

DROP PROCEDURE IF EXISTS `sp_getProvincias`$$
CREATE PROCEDURE `sp_getProvincias`(IN p_id_departamento VARCHAR(2))
BEGIN
    SELECT id, nombre FROM ubigeo_provincias WHERE id_departamento = p_id_departamento ORDER BY nombre;
END$$

DROP PROCEDURE IF EXISTS `sp_getDistritos`$$
CREATE PROCEDURE `sp_getDistritos`(IN p_id_provincia VARCHAR(4))
BEGIN
    SELECT id, nombre FROM `ubigeo_distritos` WHERE id_provincia = p_id_provincia ORDER BY nombre;
END$$

DELIMITER ;
