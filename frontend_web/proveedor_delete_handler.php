<?php
session_start();
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('Location: index.php');
    exit();
}

require_once 'config.php';

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $api_url = API_BASE_URL . "proveedores.php?id=$id";

    $options = [
        'http' => [
            'method'  => 'DELETE',
            'ignore_errors' => true
        ],
    ];

    $context  = stream_context_create($options);
    $result = file_get_contents($api_url, false, $context);

    $response = json_decode($result, true);
    $http_response_header = $http_response_header ?? [];

    $success = false;
    foreach ($http_response_header as $header) {
        if (strpos($header, 'HTTP/1.1 200') !== false) {
            $success = true;
            break;
        }
    }

    if ($success) {
        header("Location: proveedores.php?success=" . urlencode("Proveedor desactivado con éxito."));
    } else {
        $error_message = $response['message'] ?? 'Ocurrió un error al desactivar el proveedor.';
        header("Location: proveedores.php?error=" . urlencode($error_message));
    }
    exit();

} else {
    header('Location: proveedores.php');
    exit();
}
?>