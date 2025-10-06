<?php
session_start();
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('Location: index.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_once 'config.php';

    $is_editing = !empty($_POST['id']);
    $api_url = API_BASE_URL . 'unidades_medida.php';

    $data = [
        'codigo' => $_POST['codigo'],
        'descripcion' => $_POST['descripcion']
    ];

    if ($is_editing) {
        // En modo edición, también enviamos el estado
        $data['estado'] = isset($_POST['estado']) ? intval($_POST['estado']) : 0;
    }

    $ch = curl_init();
    $method = 'POST';

    if ($is_editing) {
        $api_url .= '?id=' . intval($_POST['id']);
        $method = 'PUT';
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
    $message_key = ($http_code >= 200 && $http_code < 300) ? 'success' : 'error';
    $message = urlencode($response_data['message'] ?? 'Ocurrió un error inesperado.');

    header("Location: unidades_medida.php?$message_key=$message");
    exit();
} else {
    header('Location: unidades_medida.php');
    exit();
}
?>