-- =====================================================
-- DATOS DE PRUEBA PARA VERIFICACIÓN
-- =====================================================

-- Insertar datos de la empresa (necesario para el QR y las series)
INSERT INTO `empresas` (id, ruc, nombre_largo, nombre_corto, direccion, telefonos, web, email, logo_url, estado)
VALUES (1, '20123456789', 'Mi Restaurante Fantástico S.A.C.', 'Mi Restaurante', 'Av. Aventura 123, Lima', '987654321', 'www.mi-restaurante.com', 'contacto@mi-restaurante.com', 'assets/images/logo.png', 1)
ON DUPLICATE KEY UPDATE ruc = VALUES(ruc);

-- Insertar tipos de documento de venta
INSERT INTO tipo_documento_venta (id, codigo, nombre) VALUES (1, '01', 'Factura'), (2, '03', 'Boleta de Venta')
ON DUPLICATE KEY UPDATE codigo = VALUES(codigo);

-- Insertar series de documentos (ahora con id_empresa)
INSERT INTO series_documentos (id, id_empresa, id_tipo_documento, serie, correlativo_actual)
VALUES (1, 1, 1, 'F001', 0), (2, 1, 2, 'B001', 0)
ON DUPLICATE KEY UPDATE id_empresa = VALUES(id_empresa);

-- Insertar tipos de documento de identidad
INSERT INTO tipo_documento_identidad (id, codigo, nombre) VALUES (1, '1', 'DNI'), (6, '6', 'RUC')
ON DUPLICATE KEY UPDATE codigo = VALUES(codigo);

-- Insertar un cliente de prueba
INSERT INTO clientes (id, id_tipo_documento_identidad, numero_documento, nombres_apellidos, direccion)
VALUES (1, 6, '20000000001', 'Cliente de Prueba S.A.C.', 'Av. Ficticia 456')
ON DUPLICATE KEY UPDATE numero_documento = VALUES(numero_documento);

-- Insertar una categoría de producto (necesaria para productos)
INSERT INTO categorias_producto (id, nombre, descripcion)
VALUES (1, 'Platos Principales', 'Platos fuertes de la casa')
ON DUPLICATE KEY UPDATE nombre = VALUES(nombre);

-- Insertar un producto de prueba (necesario para detalle_pedidos)
INSERT INTO productos (id, nombre, descripcion, precio, id_categoria, disponible)
VALUES (1, 'Lomo Saltado', 'Trozos de lomo fino con cebolla, tomate y papas fritas', 75.00, 1, 1)
ON DUPLICATE KEY UPDATE nombre = VALUES(nombre);

-- Insertar un pedido de prueba que pueda ser usado para generar una venta
INSERT INTO pedidos (id, id_mesa, id_usuario_mozo, estado, total, id_cliente, id_tipo_documento_venta, id_serie_documento)
VALUES (1, 1, 1, 'completado', 150.00, 1, 2, 2)
ON DUPLICATE KEY UPDATE estado = VALUES(estado);

-- Insertar detalle para el pedido de prueba
INSERT INTO detalle_pedidos (id_pedido, id_producto, cantidad, precio_unitario)
VALUES (1, 1, 2, 75.00)
ON DUPLICATE KEY UPDATE cantidad = VALUES(cantidad);

-- Insertar una venta de prueba (basada en el pedido anterior)
INSERT INTO ventas (id, id_pedido, id_usuario_cajero, id_tipo_documento_venta, id_serie_documento, numero_documento, total, base, impuesto, porcentaje, estado)
VALUES (1, 1, 1, 2, 2, 'B001-0001', 150.00, 127.12, 22.88, 18.00, 'emitida')
ON DUPLICATE KEY UPDATE estado = VALUES(estado);

-- Insertar detalle para la venta de prueba
INSERT INTO venta_detalle (id_venta, id_producto, cantidad, precio_unitario)
VALUES (1, 1, 2, 75.00)
ON DUPLICATE KEY UPDATE cantidad = VALUES(cantidad);

-- Insertar una mesa de prueba
INSERT INTO mesas (id, numero_mesa, capacidad, estado)
VALUES (1, 'Mesa 1', 4, 'disponible')
ON DUPLICATE KEY UPDATE numero_mesa = VALUES(numero_mesa);