-- Tabla de Tipos de Movimiento
CREATE TABLE IF NOT EXISTS `tipos_movimiento` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `nombre` VARCHAR(100) NOT NULL,
  `tipo` ENUM('E', 'S') NOT NULL COMMENT 'E: Entrada, S: Salida',
  `descripcion` TEXT,
  `estado` ENUM('Activado', 'Desactivado') NOT NULL DEFAULT 'Activado'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Datos iniciales para Tipos de Movimiento
INSERT IGNORE INTO `tipos_movimiento` (`id`, `nombre`, `tipo`, `descripcion`) VALUES
(1, 'Compra Nacional', 'E', 'Ingreso de mercadería por compra a proveedor nacional.'),
(2, 'Venta Nacional', 'S', 'Salida de mercadería por venta a cliente nacional.'),
(3, 'Ajuste por Inventario (Entrada)', 'E', 'Ajuste de inventario que resulta en un aumento de stock.'),
(4, 'Ajuste por Inventario (Salida)', 'S', 'Ajuste de inventario que resulta en una disminución de stock.');

-- Tabla de Cabecera de Movimientos
CREATE TABLE IF NOT EXISTS `movimientos` (
  `id` BIGINT AUTO_INCREMENT PRIMARY KEY,
  `anio` CHAR(4) NOT NULL,
  `periodo` CHAR(2) NOT NULL,
  `tipo_movimiento` ENUM('E', 'S') NOT NULL COMMENT 'E: Entrada, S: Salida',
  `codigo_movimiento` INT NOT NULL,
  `fecha_movimiento` DATE NOT NULL,
  `tipo_documento` VARCHAR(50),
  `serie_documento` VARCHAR(10),
  `numero_documento` VARCHAR(20),
  `tipo_entidad` ENUM('C', 'P') COMMENT 'C: Cliente, P: Proveedor',
  `id_entidad` BIGINT,
  `estado` ENUM('Activado', 'Desactivado') NOT NULL DEFAULT 'Activado',
  `fecha_creacion` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `fecha_modificacion` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`codigo_movimiento`) REFERENCES `tipos_movimiento`(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla de Detalle de Movimientos
CREATE TABLE IF NOT EXISTS `movimientos_detalle` (
  `id_movimiento` BIGINT NOT NULL,
  `item` INT NOT NULL,
  `id_producto` INT NOT NULL,
  `cantidad` DECIMAL(14, 5) NOT NULL,
  `id_producto_padre` INT,
  `cantidad_padre` DECIMAL(14, 5),
  `descripcion` TEXT,
  `codigo_unidad_medida` VARCHAR(3) NOT NULL,
  `costo_unitario` DECIMAL(14, 5) NOT NULL,
  `costo_neto` DECIMAL(14, 5) NOT NULL,
  `costo_promedio` DECIMAL(14, 5),
  PRIMARY KEY (`id_movimiento`, `item`),
  FOREIGN KEY (`id_movimiento`) REFERENCES `movimientos`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`id_producto`) REFERENCES `productos`(`id`),
  FOREIGN KEY (`id_producto_padre`) REFERENCES `productos`(`id`),
  FOREIGN KEY (`codigo_unidad_medida`) REFERENCES `unidades_medida`(`codigo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;