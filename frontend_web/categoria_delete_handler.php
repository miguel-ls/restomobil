<?php
session_start();

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('Location: index.php');
    exit();
}

if (isset($_GET['id'])) {
    $category_id = $_GET['id'];
    $api_url = "http://localhost/restaurante_system/backend/api/v1/categorias.php?id=$category_id";

    // Configurar cURL para una solicitud DELETE
    $ch = curl_init($api_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');

    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    $response_data = json_decode($response, true);

    if ($http_code === 200) {
        $message = urlencode($response_data['message']);
        header("Location: categorias.php?success=$message");
    } else {
        $error = isset($response_data['message']) ? urlencode($response_data['message']) : 'Error al eliminar la categoría.';
        header("Location: categorias.php?error=$error");
    }
    exit();
} else {
    // Si no se proporciona ID, redirigir
    header('Location: categorias.php?error=' . urlencode('No se proporcionó un ID para eliminar.'));
    exit();
}
?>
