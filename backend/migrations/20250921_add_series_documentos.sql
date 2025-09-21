-- Migration to add series_documentos table and procedures

-- -----------------------------------------------------
-- Table `series_documentos`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `series_documentos` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `id_tipo_documento` INT NOT NULL,
  `serie` VARCHAR(10) NOT NULL,
  `estado` BOOLEAN NOT NULL DEFAULT TRUE,
  CONSTRAINT `fk_series_tipo_documento`
    FOREIGN KEY (`id_tipo_documento`)
    REFERENCES `tipo_documento_venta` (`id`)
    ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- -----------------------------------------------------
-- Stored Procedures for `series_documentos`
-- -----------------------------------------------------
DELIMITER $$

DROP PROCEDURE IF EXISTS `sp_getAllSeries`$$
CREATE PROCEDURE `sp_getAllSeries`()
BEGIN
    SELECT
        s.id,
        s.id_tipo_documento,
        tdv.nombre AS nombre_tipo_documento,
        s.serie,
        s.estado
    FROM
        `series_documentos` s
    JOIN
        `tipo_documento_venta` tdv ON s.id_tipo_documento = tdv.id
    ORDER BY
        tdv.nombre, s.serie;
END$$

DROP PROCEDURE IF EXISTS `sp_getOneSerie`$$
CREATE PROCEDURE `sp_getOneSerie`(IN p_id INT)
BEGIN
    SELECT
        s.id,
        s.id_tipo_documento,
        tdv.nombre AS nombre_tipo_documento,
        s.serie,
        s.estado
    FROM
        `series_documentos` s
    JOIN
        `tipo_documento_venta` tdv ON s.id_tipo_documento = tdv.id
    WHERE
        s.id = p_id;
END$$

DROP PROCEDURE IF EXISTS `sp_createSerie`$$
CREATE PROCEDURE `sp_createSerie`(
    IN p_id_tipo_documento INT,
    IN p_serie VARCHAR(10)
)
BEGIN
    INSERT INTO `series_documentos` (id_tipo_documento, serie, estado)
    VALUES (p_id_tipo_documento, p_serie, TRUE);
    SELECT LAST_INSERT_ID() as id;
END$$

DROP PROCEDURE IF EXISTS `sp_updateSerie`$$
CREATE PROCEDURE `sp_updateSerie`(
    IN p_id INT,
    IN p_id_tipo_documento INT,
    IN p_serie VARCHAR(10),
    IN p_estado BOOLEAN
)
BEGIN
    UPDATE `series_documentos`
    SET
        id_tipo_documento = p_id_tipo_documento,
        serie = p_serie,
        estado = p_estado
    WHERE id = p_id;
END$$

DROP PROCEDURE IF EXISTS `sp_deleteSerie`$$
CREATE PROCEDURE `sp_deleteSerie`(IN p_id INT)
BEGIN
    DELETE FROM `series_documentos` WHERE id = p_id;
END$$

DELIMITER ;
