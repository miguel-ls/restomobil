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
    nombre_completo VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
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


-- -----------------------------------------------------
-- Procedimientos Almacenados
-- -----------------------------------------------------

DELIMITER $$

CREATE PROCEDURE sp_getUserByEmail(
    IN p_email VARCHAR(100)
)
BEGIN
    SELECT
        u.id,
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
        u.email = p_email AND u.activo = 1
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

DELIMITER ;
