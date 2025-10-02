-- Migración para agregar columnas de impuestos a la tabla de ventas

ALTER TABLE ventas
ADD COLUMN porcentaje DECIMAL(10, 2) NULL DEFAULT NULL COMMENT 'Porcentaje de impuesto aplicado' AFTER total,
ADD COLUMN base DECIMAL(10, 2) NULL DEFAULT NULL COMMENT 'Monto base antes de impuestos' AFTER porcentaje,
ADD COLUMN impuesto DECIMAL(10, 2) NULL DEFAULT NULL COMMENT 'Monto del impuesto calculado' AFTER base;