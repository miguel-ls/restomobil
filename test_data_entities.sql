-- Insertar un cliente de prueba si no existe
INSERT IGNORE INTO `clientes` (`id_tipo_documento_identidad`, `numero_documento`, `nombres_apellidos`, `estado`)
VALUES (1, '12345678', 'Cliente de Prueba S.A.C.', 'Activado');

-- Insertar un proveedor de prueba si no existe
INSERT IGNORE INTO `proveedores` (`id_tipo_documento_identidad`, `numero_documento`, `nombres_apellidos`, `estado`)
VALUES (1, '87654321', 'Proveedor de Prueba S.R.L.', 'Activado');