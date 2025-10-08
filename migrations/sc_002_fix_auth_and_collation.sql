-- Fix admin password hash
UPDATE usuarios
SET password_hash = '$2y$10$1TXTqTxdzyDC3cuDCJ3rKeBfJGDoJ6YLicp5JjRPmNcrOznGbX9Qu'
WHERE username = 'admin';

-- Fix collation issue in sp_getUserByUsername
DROP PROCEDURE IF EXISTS sp_getUserByUsername;
DELIMITER $$
CREATE PROCEDURE `sp_getUserByUsername`(IN p_username VARCHAR(50))
BEGIN
  SELECT
    u.id,
    u.username,
    u.nombre_completo,
    u.email,
    u.password_hash,
    u.id_rol,
    r.nombre_rol
  FROM usuarios u
  JOIN roles r ON u.id_rol = r.id
  WHERE u.username COLLATE utf8mb4_unicode_ci = p_username;
END$$
DELIMITER ;