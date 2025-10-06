-- Creación de la tabla 'tipo_movimiento' según el esquema proporcionado por el usuario.
CREATE TABLE tipo_movimiento (
  id int(11) NOT NULL AUTO_INCREMENT,
  tipo char(1) NOT NULL COMMENT 'E: Entrada, S: Salida',
  codigo char(3) NOT NULL,
  descripcion varchar(1000) NOT NULL,
  estado enum ('activado', 'desactivado') NOT NULL DEFAULT 'activado',
  fecha_creacion datetime NOT NULL DEFAULT CURRENT_TIMESTAMP(),
  fecha_modificacion datetime NOT NULL DEFAULT CURRENT_TIMESTAMP() ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id)
)
ENGINE = INNODB,
CHARACTER SET utf8mb4,
COLLATE utf8mb4_general_ci;

-- Crear el índice único para el código
ALTER TABLE tipo_movimiento
ADD UNIQUE INDEX codigo_UNIQUE (codigo);

-- Insertar datos iniciales para que la aplicación sea funcional
INSERT INTO `tipo_movimiento` (`tipo`, `codigo`, `descripcion`, `estado`) VALUES
('E', 'COM', 'Compra Nacional', 'activado'),
('S', 'VEN', 'Venta Nacional', 'activado'),
('E', 'AJI', 'Ajuste por Inventario (Entrada)', 'activado'),
('S', 'AJS', 'Ajuste por Inventario (Salida)', 'activado');