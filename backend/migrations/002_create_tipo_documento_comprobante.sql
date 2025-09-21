-- Migración para crear la tabla tipo_documento_comprobante y poblarla con datos de SUNAT.
-- Fecha de creación: 2025-09-21

-- Crear la tabla para Tipos de Documento (Comprobantes de Venta)
CREATE TABLE IF NOT EXISTS tipo_documento_comprobante (
    id INT AUTO_INCREMENT PRIMARY KEY,
    codigo_sunat VARCHAR(2) NOT NULL,
    descripcion VARCHAR(255) NOT NULL,
    estado BOOLEAN NOT NULL DEFAULT TRUE,
    UNIQUE (codigo_sunat)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insertar los datos del Catálogo No. 01 de SUNAT, evitando duplicados.
INSERT IGNORE INTO tipo_documento_comprobante (codigo_sunat, descripcion) VALUES
('01', 'FACTURA'),
('03', 'BOLETA DE VENTA'),
('07', 'NOTA DE CREDITO'),
('08', 'NOTA DE DEBITO'),
('09', 'GUIA DE REMISION - REMITENTE'),
('12', 'TICKET DE MAQUINA REGISTRADORA'),
('00', 'OTROS');
