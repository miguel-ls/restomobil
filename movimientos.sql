-- Tabla de Cabecera de Movimientos (Estructura Corregida Definitiva)
CREATE TABLE IF NOT EXISTS `movimientos` (
  `id` BIGINT AUTO_INCREMENT PRIMARY KEY,
  `anio` CHAR(4) NOT NULL,
  `periodo` CHAR(2) NOT NULL,
  `tipo_movimiento` ENUM('E', 'S') NOT NULL COMMENT 'E: Entrada, S: Salida',
  `codigo_movimiento` INT NOT NULL,
  `fecha_movimiento` DATE NOT NULL,
  `id_tipo_documento_venta` INT NULL,
  `serie_documento` VARCHAR(10) NULL,
  `numero_documento` VARCHAR(20) NULL,
  `tipo_entidad` ENUM('C', 'P') NULL COMMENT 'C: Cliente, P: Proveedor',
  `id_cliente` INT NULL,
  `id_proveedor` INT NULL,
  `estado` ENUM('Activado', 'Desactivado') NOT NULL DEFAULT 'Activado',
  `fecha_creacion` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `fecha_modificacion` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`codigo_movimiento`) REFERENCES `tipo_movimiento`(`id`),
  FOREIGN KEY (`id_tipo_documento_venta`) REFERENCES `tipo_documento_venta`(`id`),
  FOREIGN KEY (`id_cliente`) REFERENCES `clientes`(`id`) ON DELETE SET NULL,
  FOREIGN KEY (`id_proveedor`) REFERENCES `proveedores`(`id`) ON DELETE SET NULL
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