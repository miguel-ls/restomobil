<?php
session_start();
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true || $_SESSION['user_rol'] !== 'Administrador') {
    header('Location: dashboard.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $is_editing = !empty($_POST['id']);
    $api_url = 'http://localhost/restaurante_system/backend/api/v1/usuarios.php';
    if ($is_editing) {
        $api_url .= '?id=' . intval($_POST['id']);
    }

    $data = [
        'nombre_completo' => $_POST['nombre_completo'],
        'email' => $_POST['email'],
        'id_rol' => $_POST['id_rol'],
        'password' => $_POST['password']
    ];
    if ($is_editing) {
        $data['activo'] = $_POST['activo'];
    } else {
        $data['username'] = $_POST['username'];
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

    header("Location: usuarios.php?$message_key=$message");
    exit();
} else {
    header('Location: usuarios.php');
    exit();
}
?>
