-- Creación de la tabla para registrar la apertura y cierre de caja
CREATE TABLE IF NOT EXISTS `apertura_cierre_caja` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `fecha` DATETIME NOT NULL COMMENT 'Fecha y hora del movimiento',
  `tipo_movimiento` ENUM('apertura', 'cierre') NOT NULL COMMENT 'Tipo de movimiento: apertura o cierre de caja',
  `importe` DECIMAL(10, 2) NOT NULL COMMENT 'Monto del movimiento',
  `descripcion` TEXT COMMENT 'Descripción o concepto del movimiento',
  `usuario_id` INT NOT NULL COMMENT 'ID del usuario que registra el movimiento',
  `fecha_creacion` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `fecha_actualizacion` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`usuario_id`) REFERENCES `usuarios`(`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  -- Restricción para asegurar una sola apertura y un solo cierre por día.
  -- Se usa una columna virtual para extraer solo la fecha de la columna `fecha`.
  CONSTRAINT uq_fecha_tipo UNIQUE (tipo_movimiento, (DATE(fecha)))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Agregar un comentario a la tabla para describir su propósito
ALTER TABLE `apertura_cierre_caja` COMMENT = 'Registra los importes de apertura y cierre de caja para cada día.';
