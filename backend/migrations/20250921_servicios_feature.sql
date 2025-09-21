-- =====================================================
-- MIGRATION SCRIPT FOR SERVICIOS AND OBSERVACIONES
-- =====================================================

-- 1. Add 'tipo' to 'categorias_producto' table
ALTER TABLE `categorias_producto`
ADD COLUMN `tipo` ENUM('bienes', 'servicios') NOT NULL DEFAULT 'bienes' COMMENT 'Define si la categoría es de bienes tangibles o servicios.';

-- 2. Add 'observaciones' to 'detalle_pedidos' table
ALTER TABLE `detalle_pedidos`
ADD COLUMN `observaciones` TEXT COMMENT 'Observaciones adicionales para el item del pedido, útil para servicios.';
