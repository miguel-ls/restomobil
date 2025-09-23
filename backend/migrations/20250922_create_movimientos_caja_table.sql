-- Creación de la tabla para registrar los movimientos de caja (entradas y salidas de dinero)

CREATE TABLE IF NOT EXISTS `movimientos_caja` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `fecha` DATETIME NOT NULL COMMENT 'Fecha y hora del movimiento',
  `tipo_movimiento` ENUM('entrada', 'salida') NOT NULL COMMENT 'Tipo de movimiento: entrada o salida de dinero',
  `importe` DECIMAL(10, 2) NOT NULL COMMENT 'Monto del movimiento',
  `descripcion` TEXT COMMENT 'Descripción o concepto del movimiento',
  `usuario_id` INT NOT NULL COMMENT 'ID del usuario que registra el movimiento',
  `fecha_creacion` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `fecha_actualizacion` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`usuario_id`) REFERENCES `usuarios`(`id`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Agregar un comentario a la tabla para describir su propósito
ALTER TABLE `movimientos_caja` COMMENT = 'Registra las entradas y salidas de dinero de la caja, como un kardex.';
