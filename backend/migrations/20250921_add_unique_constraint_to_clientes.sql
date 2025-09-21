-- Migration script for adding a unique constraint to the clientes table

ALTER TABLE `clientes`
ADD UNIQUE INDEX `idx_unique_cliente_documento` (`id_tipo_documento_identidad` ASC, `numero_documento` ASC);
