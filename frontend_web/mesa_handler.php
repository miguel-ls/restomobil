<?php
session_start();

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('Location: index.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $api_url = 'http://localhost/restaurante_system/backend/api/v1/mesas.php';
    $data = [
        'numero_mesa' => $_POST['numero_mesa'],
        'capacidad' => $_POST['capacidad'],
        'estado' => $_POST['estado']
    ];
    $http_method = 'POST';

    if (!empty($_POST['id'])) {
        $data['id'] = $_POST['id'];
        $http_method = 'PUT';
    }

    $ch = curl_init($api_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $http_method);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Content-Length: ' . strlen(json_encode($data))
    ]);

    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    $response_data = json_decode($response, true);

    if ($http_code === 201 || $http_code === 200) {
        $message = urlencode($response_data['message']);
        header("Location: mesas.php?success=$message");
    } else {
        $error = isset($response_data['message']) ? urlencode($response_data['message']) : 'Ocurrió un error inesperado.';
        header("Location: mesa_form.php?id=" . $_POST['id'] . "&error=$error");
    }
    exit();
} else {
    header('Location: mesas.php');
    exit();
}
?>
