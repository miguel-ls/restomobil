-- Insert a default table if one doesn't exist
INSERT INTO mesas (id, numero_mesa, capacidad, estado)
SELECT 1, 'Mesa 1', 4, 'disponible'
WHERE NOT EXISTS (SELECT 1 FROM mesas WHERE id = 1);

-- Insert a default sale document type if one doesn't exist
INSERT INTO tipo_documento_venta (id, codigo, nombre)
SELECT 1, '03', 'Boleta de Venta'
WHERE NOT EXISTS (SELECT 1 FROM tipo_documento_venta WHERE id = 1);

-- Insert a default series for the document type if one doesn't exist
INSERT INTO series_documentos (id, id_tipo_documento, serie)
SELECT 1, 1, 'B001'
WHERE NOT EXISTS (SELECT 1 FROM series_documentos WHERE id = 1);

-- Insert a default order (pedido)
INSERT INTO pedidos (id, id_mesa, id_usuario_mozo, estado, total)
SELECT 1, 1, 1, 'pagado', 100.00
WHERE NOT EXISTS (SELECT 1 FROM pedidos WHERE id = 1);

-- Insert a default sale (venta) linked to the order
INSERT INTO ventas (id, id_pedido, id_usuario_cajero, id_tipo_documento_venta, id_serie_documento, numero_documento, total, estado)
SELECT 1, 1, 1, 1, 1, '00000001', 100.00, 'emitida'
WHERE NOT EXISTS (SELECT 1 FROM ventas WHERE id = 1);