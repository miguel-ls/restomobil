<?php
session_start();
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('Location: index.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_once 'config.php';
    $is_editing = !empty($_POST['id']);
    $api_url = API_BASE_URL . 'tipos_documentos.php';
    if ($is_editing) {
        $api_url .= '?id=' . intval($_POST['id']);
    }

    $data = [
        'codigo' => $_POST['codigo'],
        'nombre' => $_POST['nombre'],
        'descripcion' => $_POST['descripcion']
    ];

    if ($is_editing) {
        $data['estado'] = isset($_POST['estado']) ? (int)$_POST['estado'] : 0;
    }

    $ch = curl_init($api_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $is_editing ? 'PUT' : 'POST');
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);

    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    $response_data = json_decode($response, true);
    $message_key = ($http_code >= 200 && $http_code < 300) ? 'success' : 'error';
    $message = urlencode($response_data['message'] ?? 'Ocurrió un error.');

    header("Location: tipos_documentos.php?$message_key=$message");
    exit();
} else {
    header('Location: tipos_documentos.php');
    exit();
}
?>
