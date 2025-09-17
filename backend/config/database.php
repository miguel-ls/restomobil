<?php
// Configuración de la conexión a la base de datos

define('DB_HOST', '127.0.0.1'); // O la IP de tu servidor de BD
define('DB_USER', 'miguel');      // Usuario de la BD
define('DB_PASS', 'Miguel123!');  // Contraseña de la BD
define('DB_NAME', 'restaurante_db');    // Nombre de la BD

// Opcional: configurar el DSN (Data Source Name) para PDO
define('DB_DSN', 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4');

// Opcional: Opciones de PDO
$pdo_options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];
?>
