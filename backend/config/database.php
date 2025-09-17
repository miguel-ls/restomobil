<?php
// Configuración de la conexión a la base de datos

define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', ''); // Dejar en blanco si no hay contraseña
define('DB_NAME', 'restaurante_db');

// Opcional: configurar el DSN (Data Source Name) para PDO
define('DB_DSN', 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4');

// Opcional: Opciones de PDO
$pdo_options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];
?>
