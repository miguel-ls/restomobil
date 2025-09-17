-- Base de datos para el Sistema de Gestión de Restaurantes

-- Usar `restaurante_db` como nombre de la base de datos
-- CREATE DATABASE IF NOT EXISTS restaurante_db;
-- USE restaurante_db;

-- Tabla de Roles
-- Define los diferentes roles de usuario en el sistema.
CREATE TABLE roles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre_rol VARCHAR(50) NOT NULL UNIQUE
);

-- Tabla de Usuarios
-- Almacena la información de los empleados que usarán el sistema.
CREATE TABLE usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    nombre_completo VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    id_rol INT NOT NULL,
    activo BOOLEAN DEFAULT TRUE,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_rol) REFERENCES roles(id)
);

-- Tabla de Mesas
-- Representa las mesas físicas del restaurante.
CREATE TABLE mesas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    numero_mesa VARCHAR(10) NOT NULL,
    capacidad INT NOT NULL DEFAULT 4,
    estado ENUM('disponible', 'ocupada', 'reservada', 'mantenimiento') NOT NULL DEFAULT 'disponible'
);

-- Tabla de Categorías de Productos
-- Agrupa los productos en categorías (ej. Bebidas, Entradas, Plato Fuerte).
CREATE TABLE categorias_producto (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    descripcion TEXT
);

-- Tabla de Productos
-- El catálogo de todos los ítems que se pueden vender.
CREATE TABLE productos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    descripcion TEXT,
    precio DECIMAL(10, 2) NOT NULL,
    id_categoria INT,
    imagen_url VARCHAR(255),
    disponible BOOLEAN DEFAULT TRUE,
    FOREIGN KEY (id_categoria) REFERENCES categorias_producto(id)
);

-- Tabla de Pedidos
-- Registra cada pedido realizado.
CREATE TABLE pedidos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_mesa INT NOT NULL,
    id_usuario_mozo INT NOT NULL,
    estado ENUM('recibido', 'en_preparacion', 'listo_para_servir', 'servido', 'pagado', 'cancelado') NOT NULL DEFAULT 'recibido',
    total DECIMAL(10, 2) DEFAULT 0.00,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (id_mesa) REFERENCES mesas(id),
    FOREIGN KEY (id_usuario_mozo) REFERENCES usuarios(id)
);

-- Tabla de Detalle de Pedidos
-- Une los pedidos con los productos, especificando la cantidad de cada uno.
CREATE TABLE detalle_pedidos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_pedido INT NOT NULL,
    id_producto INT NOT NULL,
    cantidad INT NOT NULL DEFAULT 1,
    precio_unitario DECIMAL(10, 2) NOT NULL,
    subtotal DECIMAL(10, 2) GENERATED ALWAYS AS (cantidad * precio_unitario) STORED,
    FOREIGN KEY (id_pedido) REFERENCES pedidos(id) ON DELETE CASCADE,
    FOREIGN KEY (id_producto) REFERENCES productos(id)
);

-- Tabla de Reservas
-- Almacena la información de las reservas de mesas.
CREATE TABLE reservas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_mesa INT,
    nombre_cliente VARCHAR(100) NOT NULL,
    telefono_cliente VARCHAR(20),
    email_cliente VARCHAR(100),
    fecha_reserva DATETIME NOT NULL,
    cantidad_personas INT NOT NULL,
    estado ENUM('confirmada', 'cancelada', 'completada') NOT NULL DEFAULT 'confirmada',
    observaciones TEXT,
    FOREIGN KEY (id_mesa) REFERENCES mesas(id)
);

-- Insertar roles iniciales
INSERT INTO roles (nombre_rol) VALUES ('Administrador'), ('Cajero'), ('Mozo');

-- Insertar usuario administrador por defecto
-- Usuario: admin
-- Contraseña: password
INSERT INTO usuarios (username, nombre_completo, email, password_hash, id_rol) VALUES
('admin', 'Administrador del Sistema', 'admin@example.com', '$2y$10$T8Yj8A.A.JpZ.e.zY.e.IuG.t.t.t.t.t.t.t.t.t.t', 1);


-- -----------------------------------------------------
-- Procedimientos Almacenados
-- -----------------------------------------------------

DELIMITER $$

CREATE PROCEDURE sp_getUserByUsername(
    IN p_username VARCHAR(50)
)
BEGIN
    SELECT
        u.id,
        u.username,
        u.nombre_completo,
        u.email,
        u.password_hash,
        u.id_rol,
        r.nombre_rol
    FROM
        usuarios u
    JOIN
        roles r ON u.id_rol = r.id
    WHERE
        u.username = p_username AND u.activo = 1
    LIMIT 1;
END$$


CREATE PROCEDURE sp_getAllProducts()
BEGIN
    SELECT
        p.id,
        p.nombre,
        p.descripcion,
        p.precio,
        c.nombre as categoria_nombre
    FROM
        productos p
    LEFT JOIN
        categorias_producto c ON p.id_categoria = c.id
    ORDER BY
        p.nombre ASC;
END$$

CREATE PROCEDURE sp_createProduct(
    IN p_nombre VARCHAR(100),
    IN p_descripcion TEXT,
    IN p_precio DECIMAL(10, 2),
    IN p_id_categoria INT
)
BEGIN
    INSERT INTO productos (nombre, descripcion, precio, id_categoria)
    VALUES (p_nombre, p_descripcion, p_precio, p_id_categoria);
    SELECT LAST_INSERT_ID() as id;
END$$

CREATE PROCEDURE sp_readOneProduct(
    IN p_id INT
)
BEGIN
    SELECT
        p.id,
        p.nombre,
        p.descripcion,
        p.precio,
        p.id_categoria,
        c.nombre as categoria_nombre
    FROM
        productos p
    LEFT JOIN
        categorias_producto c ON p.id_categoria = c.id
    WHERE
        p.id = p_id;
END$$

CREATE PROCEDURE sp_updateProduct(
    IN p_id INT,
    IN p_nombre VARCHAR(100),
    IN p_descripcion TEXT,
    IN p_precio DECIMAL(10, 2),
    IN p_id_categoria INT
)
BEGIN
    UPDATE productos
    SET
        nombre = p_nombre,
        descripcion = p_descripcion,
        precio = p_precio,
        id_categoria = p_id_categoria
    WHERE
        id = p_id;
END$$

CREATE PROCEDURE sp_deleteProduct(
    IN p_id INT
)
BEGIN
    DELETE FROM productos WHERE id = p_id;
END$$

DELIMITER ;
