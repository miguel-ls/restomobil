<?php
// frontend_web/config.php

/**
 * Archivo de configuración para el frontend web.
 */

// Define el símbolo de la moneda que se usará en la interfaz de usuario.
if (!defined('CURRENCY_SYMBOL')) {
    define('CURRENCY_SYMBOL', 'S/.');
}

if (!defined('PUNTO_VENTA')) {
    define('PUNTO_VENTA', 'Punto Venta');
}

if (!defined('VENDEDOR')) {
    define('VENDEDOR', 'Vendedor');
}

// Define la URL base para la API. Asegúrate de que termine con una barra inclinada (/).
if (!defined('API_BASE_URL')) {
    define('API_BASE_URL', 'http://localhost:8001/api/v1/');
}
?>