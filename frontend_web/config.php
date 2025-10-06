<?php
// frontend_web/config.php

/**
 * Archivo de configuración para el frontend web.
 */

// Define el símbolo de la moneda que se usará en la interfaz de usuario.
define('CURRENCY_SYMBOL', 'S/.');

define('PUNTO_VENTA', 'Punto Venta');
define('VENDEDOR', 'Vendedor');

// Define la URL base para la API. Asegúrate de que apunte al servidor backend en el puerto correcto.
define('API_BASE_URL', 'http://localhost:8080/api/v1/');