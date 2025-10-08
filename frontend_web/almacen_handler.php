<?php
session_start();
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('Location: index.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_once 'config.php';

    $is_editing = !empty($_POST['id']);
    $api_url = API_BASE_URL . 'almacenes.php';

    // Preparar los datos para enviar a la API
    $data = [
        'nombre' => $_POST['nombre'],
        'estado' => isset($_POST['estado']) ? (int)$_POST['estado'] : 0,
        'predeterminado' => isset($_POST['predeterminado']) ? 1 : 0,
    ];

    $ch = curl_init();
    $method = 'POST'; // Por defecto, para crear

    if ($is_editing) {
        $api_url .= '?id=' . intval($_POST['id']);
        $method = 'PUT'; // Cambiar a PUT para actualizar
    }

    curl_setopt($ch, CURLOPT_URL, $api_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Content-Length: ' . strlen(json_encode($data))
    ]);

    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    $response_data = json_decode($response, true);

    // Preparar mensaje para la redirección
    $message_key = ($http_code >= 200 && $http_code < 300) ? 'success' : 'error';
    $message = urlencode($response_data['message'] ?? 'Ocurrió un error inesperado.');

    header("Location: almacenes.php?$message_key=$message");
    exit();
} else {
    // Si no es una solicitud POST, redirigir a la lista
    header('Location: almacenes.php');
    exit();
}
?>