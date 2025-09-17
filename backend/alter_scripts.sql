-- -----------------------------------------------------
-- Archivo de Alteraciones SQL
-- -----------------------------------------------------
-- Este archivo contiene los cambios incrementales a la
-- base de datos, como nuevos procedimientos almacenados
-- o alteraciones de tablas.
-- -----------------------------------------------------

-- -----------------------------------------------------
-- Procedimientos Almacenados para 'categorias_producto'
-- -----------------------------------------------------

DELIMITER $$

-- Obtener todas las categorías
CREATE PROCEDURE sp_getAllCategories()
BEGIN
    SELECT
        id,
        nombre,
        descripcion
    FROM
        categorias_producto
    ORDER BY
        nombre ASC;
END$$

-- Crear una nueva categoría
CREATE PROCEDURE sp_createCategory(
    IN p_nombre VARCHAR(100),
    IN p_descripcion TEXT
)
BEGIN
    INSERT INTO categorias_producto (nombre, descripcion)
    VALUES (p_nombre, p_descripcion);
    SELECT LAST_INSERT_ID() as id;
END$$

-- Leer una categoría por su ID
CREATE PROCEDURE sp_readOneCategory(
    IN p_id INT
)
BEGIN
    SELECT
        id,
        nombre,
        descripcion
    FROM
        categorias_producto
    WHERE
        id = p_id;
END$$

-- Actualizar una categoría
CREATE PROCEDURE sp_updateCategory(
    IN p_id INT,
    IN p_nombre VARCHAR(100),
    IN p_descripcion TEXT
)
BEGIN
    UPDATE categorias_producto
    SET
        nombre = p_nombre,
        descripcion = p_descripcion
    WHERE
        id = p_id;
END$$

-- Eliminar una categoría
CREATE PROCEDURE sp_deleteCategory(
    IN p_id INT
)
BEGIN
    DELETE FROM categorias_producto WHERE id = p_id;
END$$

DELIMITER ;

-- -----------------------------------------------------
-- Procedimientos Almacenados para 'mesas'
-- -----------------------------------------------------

DELIMITER $$

-- Obtener todas las mesas
CREATE PROCEDURE sp_getAllTables()
BEGIN
    SELECT
        id,
        numero_mesa,
        capacidad,
        estado
    FROM
        mesas
    ORDER BY
        numero_mesa ASC;
END$$

-- Crear una nueva mesa
CREATE PROCEDURE sp_createTable(
    IN p_numero_mesa VARCHAR(10),
    IN p_capacidad INT,
    IN p_estado ENUM('disponible', 'ocupada', 'reservada', 'mantenimiento')
)
BEGIN
    INSERT INTO mesas (numero_mesa, capacidad, estado)
    VALUES (p_numero_mesa, p_capacidad, p_estado);
    SELECT LAST_INSERT_ID() as id;
END$$

-- Leer una mesa por su ID
CREATE PROCEDURE sp_readOneTable(
    IN p_id INT
)
BEGIN
    SELECT
        id,
        numero_mesa,
        capacidad,
        estado
    FROM
        mesas
    WHERE
        id = p_id;
END$$

-- Actualizar una mesa
CREATE PROCEDURE sp_updateTable(
    IN p_id INT,
    IN p_numero_mesa VARCHAR(10),
    IN p_capacidad INT,
    IN p_estado ENUM('disponible', 'ocupada', 'reservada', 'mantenimiento')
)
BEGIN
    UPDATE mesas
    SET
        numero_mesa = p_numero_mesa,
        capacidad = p_capacidad,
        estado = p_estado
    WHERE
        id = p_id;
END$$

-- Eliminar una mesa
CREATE PROCEDURE sp_deleteTable(
    IN p_id INT
)
BEGIN
    DELETE FROM mesas WHERE id = p_id;
END$$

DELIMITER ;

-- -----------------------------------------------------
-- Procedimientos Almacenados para 'usuarios' y 'roles'
-- -----------------------------------------------------

DELIMITER $$

-- Obtener todos los roles
CREATE PROCEDURE sp_getAllRoles()
BEGIN
    SELECT id, nombre_rol FROM roles ORDER BY nombre_rol ASC;
END$$

-- Obtener todos los usuarios
CREATE PROCEDURE sp_getAllUsers()
BEGIN
    SELECT
        u.id,
        u.username,
        u.nombre_completo,
        u.email,
        u.id_rol,
        r.nombre_rol,
        u.activo
    FROM
        usuarios u
    JOIN
        roles r ON u.id_rol = r.id
    ORDER BY
        u.nombre_completo ASC;
END$$

-- Crear un nuevo usuario
CREATE PROCEDURE sp_createUser(
    IN p_username VARCHAR(50),
    IN p_nombre_completo VARCHAR(100),
    IN p_email VARCHAR(100),
    IN p_password_hash VARCHAR(255),
    IN p_id_rol INT
)
BEGIN
    INSERT INTO usuarios (username, nombre_completo, email, password_hash, id_rol)
    VALUES (p_username, p_nombre_completo, p_email, p_password_hash, p_id_rol);
    SELECT LAST_INSERT_ID() as id;
END$$

-- Leer un usuario por su ID
CREATE PROCEDURE sp_readOneUser(
    IN p_id INT
)
BEGIN
    SELECT
        id,
        username,
        nombre_completo,
        email,
        id_rol,
        activo
    FROM
        usuarios
    WHERE
        id = p_id;
END$$

-- Actualizar un usuario
CREATE PROCEDURE sp_updateUser(
    IN p_id INT,
    IN p_nombre_completo VARCHAR(100),
    IN p_email VARCHAR(100),
    IN p_id_rol INT,
    IN p_activo BOOLEAN,
    IN p_password_hash VARCHAR(255)
)
BEGIN
    UPDATE usuarios
    SET
        nombre_completo = p_nombre_completo,
        email = p_email,
        id_rol = p_id_rol,
        activo = p_activo,
        -- Solo actualiza la contraseña si se proporciona una nueva
        password_hash = IF(p_password_hash IS NOT NULL AND p_password_hash != '', p_password_hash, password_hash)
    WHERE
        id = p_id;
END$$

-- Eliminar (desactivar) un usuario
CREATE PROCEDURE sp_deleteUser(
    IN p_id INT
)
BEGIN
    -- Se realiza un borrado lógico para no perder la integridad referencial
    UPDATE usuarios SET activo = 0 WHERE id = p_id;
END$$

DELIMITER ;
