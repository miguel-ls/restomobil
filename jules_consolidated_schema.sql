-- =====================================================
-- ESQUEMA CONSOLIDADO DE LA BASE DE DATOS
-- =====================================================

-- Tablas base
CREATE TABLE roles ( id INT AUTO_INCREMENT PRIMARY KEY, nombre_rol VARCHAR(50) NOT NULL UNIQUE );
CREATE TABLE usuarios ( id INT AUTO_INCREMENT PRIMARY KEY, username VARCHAR(50) NOT NULL UNIQUE, nombre_completo VARCHAR(100) NOT NULL, email VARCHAR(100) NOT NULL, password_hash VARCHAR(255) NOT NULL, id_rol INT NOT NULL, activo BOOLEAN DEFAULT TRUE, fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP, FOREIGN KEY (id_rol) REFERENCES roles(id) );
CREATE TABLE mesas ( id INT AUTO_INCREMENT PRIMARY KEY, numero_mesa VARCHAR(10) NOT NULL, capacidad INT NOT NULL DEFAULT 4, estado ENUM('disponible', 'ocupada', 'reservada', 'mantenimiento') NOT NULL DEFAULT 'disponible', es_libre BOOLEAN NOT NULL DEFAULT TRUE );
CREATE TABLE categorias_producto ( id INT AUTO_INCREMENT PRIMARY KEY, nombre VARCHAR(100) NOT NULL, descripcion TEXT );
CREATE TABLE productos ( id INT AUTO_INCREMENT PRIMARY KEY, nombre VARCHAR(100) NOT NULL, descripcion TEXT, precio DECIMAL(10, 2) NOT NULL, id_categoria INT, imagen_url VARCHAR(255), disponible BOOLEAN DEFAULT TRUE, FOREIGN KEY (id_categoria) REFERENCES categorias_producto(id) );

-- Tablas de documentos (dependencias para clientes y ventas)
CREATE TABLE tipo_documento_identidad (
  id int(11) NOT NULL AUTO_INCREMENT,
  codigo varchar(2) NOT NULL,
  nombre varchar(100) NOT NULL,
  descripcion text DEFAULT NULL,
  estado tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (id),
  UNIQUE INDEX codigo (codigo)
);

CREATE TABLE tipo_documento_venta (
  id int(11) NOT NULL AUTO_INCREMENT,
  codigo varchar(2) NOT NULL,
  nombre varchar(100) NOT NULL,
  descripcion text DEFAULT NULL,
  estado tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (id),
  UNIQUE INDEX codigo (codigo)
);

-- Tabla de clientes
CREATE TABLE `clientes` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `id_tipo_documento_identidad` INT NOT NULL,
  `numero_documento` VARCHAR(20) NOT NULL,
  `nombres_apellidos` VARCHAR(200) NOT NULL,
  `direccion` VARCHAR(255) NULL,
  `codigo_ubigeo` VARCHAR(10) NULL,
  `email` VARCHAR(100) NULL,
  `telefono` VARCHAR(20) NULL,
  `estado` ENUM('Activado', 'Desactivado') NOT NULL DEFAULT 'Activado',
  PRIMARY KEY (`id`),
  UNIQUE INDEX `documento_UNIQUE` (`id_tipo_documento_identidad` ASC, `numero_documento` ASC),
  CONSTRAINT `fk_clientes_tipo_documento_identidad`
    FOREIGN KEY (`id_tipo_documento_identidad`)
    REFERENCES `tipo_documento_identidad` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION
);

-- Tabla de series
CREATE TABLE series_documentos (
  id int(11) NOT NULL AUTO_INCREMENT,
  id_tipo_documento int(11) NOT NULL,
  serie varchar(10) NOT NULL,
  estado tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (id),
  CONSTRAINT fk_series_tipo_documento FOREIGN KEY (id_tipo_documento) REFERENCES tipo_documento_venta (id) ON DELETE CASCADE
);

-- Tablas de pedidos (modificada con las migraciones)
CREATE TABLE pedidos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_mesa INT NOT NULL,
    id_usuario_mozo INT NOT NULL,
    estado ENUM('recibido', 'en_preparacion', 'listo_para_servir', 'abierto', 'completado', 'cancelado', 'pagado') NOT NULL DEFAULT 'recibido',
    total DECIMAL(10, 2) DEFAULT 0.00,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    id_cliente INT NULL,
    id_tipo_documento_venta INT NULL,
    id_serie_documento INT NULL,
    numero_documento VARCHAR(20) NULL,
    FOREIGN KEY (id_mesa) REFERENCES mesas(id),
    FOREIGN KEY (id_usuario_mozo) REFERENCES usuarios(id),
    FOREIGN KEY (id_cliente) REFERENCES clientes(id) ON DELETE SET NULL ON UPDATE CASCADE,
    FOREIGN KEY (id_tipo_documento_venta) REFERENCES tipo_documento_venta(id) ON DELETE SET NULL ON UPDATE CASCADE,
    FOREIGN KEY (id_serie_documento) REFERENCES series_documentos(id) ON DELETE SET NULL ON UPDATE CASCADE
);

CREATE TABLE detalle_pedidos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_pedido INT NOT NULL,
    id_producto INT NOT NULL,
    cantidad INT NOT NULL DEFAULT 1,
    precio_unitario DECIMAL(10, 2) NOT NULL,
    subtotal DECIMAL(10, 2) GENERATED ALWAYS AS (cantidad * precio_unitario) STORED,
    observaciones TEXT,
    FOREIGN KEY (id_pedido) REFERENCES pedidos(id) ON DELETE CASCADE,
    FOREIGN KEY (id_producto) REFERENCES productos(id)
);

-- Tabla de reservas (sin cambios aparentes)
CREATE TABLE reservas ( id INT AUTO_INCREMENT PRIMARY KEY, id_mesa INT, nombre_cliente VARCHAR(100) NOT NULL, telefono_cliente VARCHAR(20), email_cliente VARCHAR(100), fecha_reserva DATETIME NOT NULL, cantidad_personas INT NOT NULL, estado ENUM('confirmada', 'cancelada', 'completada') NOT NULL DEFAULT 'confirmada', observaciones TEXT, FOREIGN KEY (id_mesa) REFERENCES mesas(id) );

-- Tablas de ventas (modificada con migraciones)
CREATE TABLE `ventas` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `id_pedido` INT NOT NULL,
  `id_cliente` INT,
  `id_usuario_cajero` INT NOT NULL,
  `id_tipo_documento_venta` INT NOT NULL,
  `id_serie_documento` INT NOT NULL,
  `numero_documento` VARCHAR(20) NOT NULL,
  `total` DECIMAL(10, 2) NOT NULL,
  `base` DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
  `impuesto` DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
  `porcentaje` DECIMAL(5, 2) NOT NULL DEFAULT 0.00,
  `estado` ENUM('emitida', 'anulada') NOT NULL DEFAULT 'emitida',
  `fecha_emision` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`id_pedido`) REFERENCES `pedidos`(`id`),
  FOREIGN KEY (`id_cliente`) REFERENCES `clientes`(`id`),
  FOREIGN KEY (`id_usuario_cajero`) REFERENCES `usuarios`(`id`),
  FOREIGN KEY (`id_tipo_documento_venta`) REFERENCES `tipo_documento_venta`(`id`),
  FOREIGN KEY (`id_serie_documento`) REFERENCES `series_documentos`(`id`)
);

CREATE TABLE `venta_detalle` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `id_venta` INT NOT NULL,
  `id_producto` INT NOT NULL,
  `cantidad` INT NOT NULL,
  `precio_unitario` DECIMAL(10, 2) NOT NULL,
  `subtotal` DECIMAL(10, 2) GENERATED ALWAYS AS (cantidad * precio_unitario) STORED,
  FOREIGN KEY (`id_venta`) REFERENCES `ventas`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`id_producto`) REFERENCES `productos`(`id`)
);

-- Tablas de caja
CREATE TABLE IF NOT EXISTS `apertura_cierre_caja` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_usuario` int(11) NOT NULL,
  `fecha_apertura` datetime NOT NULL,
  `monto_inicial` decimal(10,2) NOT NULL,
  `fecha_cierre` datetime DEFAULT NULL,
  `monto_final` decimal(10,2) DEFAULT NULL,
  `total_ventas` decimal(10,2) DEFAULT NULL,
  `diferencia` decimal(10,2) DEFAULT NULL,
  `estado` varchar(20) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `id_usuario` (`id_usuario`),
  CONSTRAINT `apertura_cierre_caja_ibfk_1` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id`)
);

CREATE TABLE IF NOT EXISTS `movimientos_caja` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_apertura_cierre` int(11) NOT NULL,
  `tipo_movimiento` varchar(50) NOT NULL,
  `monto` decimal(10,2) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `id_usuario` int(11) NOT NULL,
  `fecha_movimiento` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `id_apertura_cierre` (`id_apertura_cierre`),
  KEY `id_usuario` (`id_usuario`),
  CONSTRAINT `movimientos_caja_ibfk_1` FOREIGN KEY (`id_apertura_cierre`) REFERENCES `apertura_cierre_caja` (`id`),
  CONSTRAINT `movimientos_caja_ibfk_2` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id`)
);

-- Datos iniciales
INSERT INTO roles (nombre_rol) VALUES ('Administrador'), ('Cajero'), ('Mozo');
INSERT INTO usuarios (username, nombre_completo, email, password_hash, id_rol) VALUES ('admin', 'Administrador del Sistema', 'admin@example.com', '$2y$10$1GMmW3jA/iGvlCxLYsqUHu75Nbpmi7pkwQHSaTOslPimuNB/Eaxqa', 1);