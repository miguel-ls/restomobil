-- ===============================================================
-- MIGRATION SCRIPT TO FIX CATEGORY SCHEMA AND RELATED PROCEDURES
-- ===============================================================

-- 1. Drop the erroneously added 'tipo' column from 'categorias_producto'
-- This column was added in a previous migration and is redundant. The correct column is 'tipo_categoria'.
ALTER TABLE `categorias_producto` DROP COLUMN `tipo`;

-- The following stored procedures need to be updated to use 'tipo_categoria' instead of 'tipo'.
-- This script serves as documentation for the changes that will be applied to other SQL files.

/*
-- In backend/update_productos_add_status.sql:

-- sp_getAllProducts should be updated to:
SELECT p.id, p.nombre, p.descripcion, p.precio, p.estado, c.nombre as categoria_nombre, c.tipo_categoria as categoria_tipo
...

-- sp_readOneProduct should be updated to:
SELECT p.id, p.nombre, p.descripcion, p.precio, p.id_categoria, p.estado, c.nombre as categoria_nombre, c.tipo_categoria as categoria_tipo
...

-- In backend/database.sql:

-- sp_getOrderItems should be updated to:
SELECT ..., cp.tipo_categoria as categoria_tipo
...

*/
