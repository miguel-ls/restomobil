USE restaurante_db;

-- Eliminar procedimientos existentes para poder recrearlos
DROP PROCEDURE IF EXISTS sp_createProduct;
DROP PROCEDURE IF EXISTS sp_readOneProduct;
DROP PROCEDURE IF EXISTS sp_updateProduct;
DROP PROCEDURE IF EXISTS sp_getAllProducts;
DROP PROCEDURE IF EXISTS sp_countAllProducts;

DELIMITER $$

--
-- Procedimiento para crear un nuevo producto
--
CREATE PROCEDURE `sp_createProduct`(
    IN p_nombre VARCHAR(100),
    IN p_descripcion TEXT,
    IN p_precio DECIMAL(10, 2),
    IN p_id_categoria INT,
    IN p_estado ENUM('activo', 'inactivo'),
    IN p_controlar_stock BOOLEAN
)
BEGIN
    INSERT INTO productos (nombre, descripcion, precio, id_categoria, estado, controlar_stock)
    VALUES (p_nombre, p_descripcion, p_precio, p_id_categoria, p_estado, p_controlar_stock);
    SELECT LAST_INSERT_ID() AS id;
END$$

--
-- Procedimiento para leer un solo producto por su ID
--
CREATE PROCEDURE `sp_readOneProduct`(IN p_id INT)
BEGIN
    SELECT
        p.id,
        p.nombre,
        p.descripcion,
        p.precio,
        p.id_categoria,
        p.estado,
        p.controlar_stock,
        c.nombre AS categoria_nombre,
        c.tipo_categoria AS categoria_tipo
    FROM
        productos p
        LEFT JOIN categorias_producto c ON p.id_categoria = c.id
    WHERE
        p.id = p_id;
END$$

--
-- Procedimiento para actualizar un producto existente
--
CREATE PROCEDURE `sp_updateProduct`(
    IN p_id INT,
    IN p_nombre VARCHAR(100),
    IN p_descripcion TEXT,
    IN p_precio DECIMAL(10, 2),
    IN p_id_categoria INT,
    IN p_estado ENUM('activo', 'inactivo'),
    IN p_controlar_stock BOOLEAN
)
BEGIN
    UPDATE productos
    SET
        nombre = p_nombre,
        descripcion = p_descripcion,
        precio = p_precio,
        id_categoria = p_id_categoria,
        estado = p_estado,
        controlar_stock = p_controlar_stock
    WHERE
        id = p_id;
END$$

--
-- Procedimiento para leer todos los productos con filtros y paginación
--
CREATE PROCEDURE `sp_getAllProducts`(
    IN p_id INT,
    IN p_nombre VARCHAR(100),
    IN p_descripcion TEXT,
    IN p_precio DECIMAL(10, 2),
    IN p_categoria_nombre VARCHAR(100),
    IN p_estado VARCHAR(10),
    IN p_controlar_stock BOOLEAN,
    IN p_page_number INT,
    IN p_page_size INT
)
BEGIN
    DECLARE v_offset INT;
    SET v_offset = (p_page_number - 1) * p_page_size;

    SELECT
        p.id,
        p.nombre,
        p.descripcion,
        p.precio,
        p.estado,
        p.controlar_stock,
        c.nombre AS categoria_nombre,
        c.tipo_categoria AS categoria_tipo
    FROM
        productos p
        LEFT JOIN categorias_producto c ON p.id_categoria = c.id
    WHERE
        (p_id IS NULL OR p.id = p_id) AND
        (p_nombre IS NULL OR p.nombre LIKE CONCAT('%', p_nombre, '%')) AND
        (p_descripcion IS NULL OR p.descripcion LIKE CONCAT('%', p_descripcion, '%')) AND
        (p_precio IS NULL OR p.precio = p_precio) AND
        (p_categoria_nombre IS NULL OR c.nombre = p_categoria_nombre) AND
        (p_estado IS NULL OR p.estado = p_estado) AND
        (p_controlar_stock IS NULL OR p.controlar_stock = p_controlar_stock)
    ORDER BY
        p.nombre
    LIMIT p_page_size OFFSET v_offset;
END$$

--
-- Procedimiento para contar todos los productos con filtros
--
CREATE PROCEDURE `sp_countAllProducts`(
    IN p_id INT,
    IN p_nombre VARCHAR(100),
    IN p_descripcion TEXT,
    IN p_precio DECIMAL(10, 2),
    IN p_categoria_nombre VARCHAR(100),
    IN p_estado VARCHAR(10),
    IN p_controlar_stock BOOLEAN
)
BEGIN
    SELECT
        COUNT(*) AS total_records
    FROM
        productos p
        LEFT JOIN categorias_producto c ON p.id_categoria = c.id
    WHERE
        (p_id IS NULL OR p.id = p_id) AND
        (p_nombre IS NULL OR p.nombre LIKE CONCAT('%', p_nombre, '%')) AND
        (p_descripcion IS NULL OR p.descripcion LIKE CONCAT('%', p_descripcion, '%')) AND
        (p_precio IS NULL OR p.precio = p_precio) AND
        (p_categoria_nombre IS NULL OR c.nombre = p_categoria_nombre) AND
        (p_estado IS NULL OR p.estado = p_estado) AND
        (p_controlar_stock IS NULL OR p.controlar_stock = p_controlar_stock);
END$$

DELIMITER ;