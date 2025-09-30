-- Tabla para almacenar plantillas de reportes dinámicos
CREATE TABLE IF NOT EXISTS `reporte_plantillas` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `nombre_plantilla` VARCHAR(100) NOT NULL,
    `columnas` JSON NOT NULL,
    `creado_en` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `actualizado_en` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY `nombre_plantilla_unico` (`nombre_plantilla`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Comentario sobre la tabla
ALTER TABLE `reporte_plantillas` COMMENT='Almacena las configuraciones de columnas guardadas por los usuarios para el generador de reportes dinámicos.';