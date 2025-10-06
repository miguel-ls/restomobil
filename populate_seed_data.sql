-- =====================================================
-- SCRIPT PARA POBLAR DATOS DE CONFIGURACIÓN ESENCIALES
-- =====================================================

-- 1. Poblar la tabla `tipo_documento_identidad`
-- Se usa INSERT IGNORE para no fallar si los datos ya existen.
INSERT IGNORE INTO `tipo_documento_identidad` (`id`, `codigo`, `nombre`, `descripcion`, `estado`) VALUES
(1, '01', 'DNI', 'Documento Nacional de Identidad', 1),
(6, '06', 'RUC', 'Registro Único de Contribuyentes', 1);

-- 2. Poblar la tabla `clientes` con un cliente de prueba
INSERT IGNORE INTO `clientes` (`id_tipo_documento_identidad`, `numero_documento`, `nombres_apellidos`, `estado`)
VALUES (1, '12345678', 'Cliente de Prueba S.A.C.', 'Activado');

-- 3. Poblar la tabla `proveedores` con un proveedor de prueba
INSERT IGNORE INTO `proveedores` (`id_tipo_documento_identidad`, `numero_documento`, `nombres_apellidos`, `estado`)
VALUES (6, '87654321', 'Proveedor de Prueba S.R.L.', 'Activado');

-- 4. Poblar la tabla `tipo_documento_venta` (si está vacía)
INSERT IGNORE INTO `tipo_documento_venta` (`codigo`, `nombre`, `descripcion`, `estado`) VALUES
('01', 'Factura', 'Factura Electrónica', 1),
('03', 'Boleta de Venta', 'Boleta de Venta Electrónica', 1);