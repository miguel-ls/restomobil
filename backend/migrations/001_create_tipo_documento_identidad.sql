-- Migración para crear la tabla tipo_documento_identidad y poblarla con datos de SUNAT.
-- Fecha de creación: 2025-09-21

-- Crear la tabla para Tipos de Documento de Identidad
CREATE TABLE IF NOT EXISTS tipo_documento_identidad (
    id INT AUTO_INCREMENT PRIMARY KEY,
    codigo_sunat VARCHAR(2) NOT NULL,
    descripcion VARCHAR(255) NOT NULL,
    estado BOOLEAN NOT NULL DEFAULT TRUE,
    UNIQUE (codigo_sunat)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insertar los datos del Catálogo No. 06 de SUNAT, evitando duplicados.
-- Se usa INSERT IGNORE para que no falle si los códigos ya existen.
INSERT IGNORE INTO tipo_documento_identidad (codigo_sunat, descripcion) VALUES
('0', 'OTROS TIPOS DE DOCUMENTOS'),
('1', 'DOCUMENTO NACIONAL DE IDENTIDAD (DNI)'),
('4', 'CARNET DE EXTRANJERIA'),
('6', 'REGISTRO UNICO DE CONTRIBUYENTES (RUC)'),
('7', 'PASAPORTE'),
('A', 'CEDULA DIPLOMATICA DE IDENTIDAD'),
('B', 'DOC. IDENT. PAIS RESIDENCIA-NO.D'),
('C', 'TAX IDENTIFICATION NUMBER - TIN'),
('D', 'IDENTIFICATION NUMBER - IN'),
('E', 'TARJETA ANDINA DE MIGRACION (TAM)'),
('F', 'PERMISO TEMPORAL DE PERMANENCIA (PTP)');
