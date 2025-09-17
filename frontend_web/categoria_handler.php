<?php
session_start();

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('Location: index.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $api_url = 'http://localhost/restaurante_system/backend/api/v1/categorias.php';
    $data = [
        'nombre' => $_POST['nombre'],
        'descripcion' => $_POST['descripcion']
    ];
    $http_method = 'POST'; // Por defecto, para crear

    // Si hay un ID, es una actualización (PUT)
    if (!empty($_POST['id'])) {
        $data['id'] = $_POST['id'];
        $http_method = 'PUT';
    }

    // Configurar cURL
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

    // Redirigir según el resultado
    if ($http_code === 201 || $http_code === 200) {
        $message = urlencode($response_data['message']);
        header("Location: categorias.php?success=$message");
    } else {
        $error = isset($response_data['message']) ? urlencode($response_data['message']) : 'Ocurrió un error inesperado.';
        header("Location: categorias.php?error=$error");
    }
    exit();
} else {
    // Si no es POST, redirigir
    header('Location: categorias.php');
    exit();
}
?>
