-- Agregar las nuevas columnas a la tabla de pedidos
ALTER TABLE pedidos
ADD COLUMN id_serie_documento INT(11) DEFAULT NULL,
ADD COLUMN numero_documento VARCHAR(20) DEFAULT NULL;

-- Agregar la restricción de clave externa para id_serie_documento
ALTER TABLE pedidos
ADD CONSTRAINT fk_pedidos_serie_documento
FOREIGN KEY (id_serie_documento) REFERENCES serie_documento(id)
ON DELETE SET NULL ON UPDATE CASCADE;