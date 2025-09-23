-- -----------------------------------------------------
-- Esquema para la gestión de Ventas (Boletas/Facturas)
-- -----------------------------------------------------

-- 1. Modificar la tabla de pedidos para agregar el estado 'pagado'
ALTER TABLE `pedidos` MODIFY `estado` ENUM('recibido', 'en_preparacion', 'listo_para_servir', 'abierto', 'completado', 'cancelado', 'pagado') NOT NULL DEFAULT 'recibido';

-- 2. Crear la tabla para la cabecera de la Venta
CREATE TABLE `ventas` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `id_pedido` INT NOT NULL,
  `id_cliente` INT,
  `id_usuario_cajero` INT NOT NULL,
  `id_tipo_documento_venta` INT NOT NULL,
  `id_serie_documento` INT NOT NULL,
  `numero_documento` VARCHAR(20) NOT NULL,
  `total` DECIMAL(10, 2) NOT NULL,
  `estado` ENUM('emitida', 'anulada') NOT NULL DEFAULT 'emitida',
  `fecha_emision` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

  FOREIGN KEY (`id_pedido`) REFERENCES `pedidos`(`id`),
  FOREIGN KEY (`id_cliente`) REFERENCES `clientes`(`id`),
  FOREIGN KEY (`id_usuario_cajero`) REFERENCES `usuarios`(`id`),
  FOREIGN KEY (`id_tipo_documento_venta`) REFERENCES `tipo_documento_venta`(`id`),
  FOREIGN KEY (`id_serie_documento`) REFERENCES `series_documentos`(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 3. Crear la tabla para el detalle de la Venta
CREATE TABLE `venta_detalle` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `id_venta` INT NOT NULL,
  `id_producto` INT NOT NULL,
  `cantidad` INT NOT NULL,
  `precio_unitario` DECIMAL(10, 2) NOT NULL,
  `subtotal` DECIMAL(10, 2) GENERATED ALWAYS AS (cantidad * precio_unitario) STORED,

  FOREIGN KEY (`id_venta`) REFERENCES `ventas`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`id_producto`) REFERENCES `productos`(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ---
-- Nota: Las tablas 'clientes', 'tipo_documento_venta' y 'series_documentos'
-- se asumen existentes según los requerimientos y el esquema analizado.
-- ---
