<?php
session_start();
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('Location: index.php');
    exit();
}

if (isset($_GET['id'])) {
    $category_id = intval($_GET['id']);

    // Incluir configuración de la API
    require_once '../backend/config/app_config.php';
    $api_url = API_BASE_URL . "categorias.php?id=$category_id";

    $ch = curl_init($api_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');

    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    $response_data = json_decode($response, true);
    $message_key = ($http_code === 200) ? 'success' : 'error';
    $message = urlencode($response_data['message'] ?? 'Ocurrió un error.');

    header("Location: categorias.php?$message_key=$message");
    exit();
} else {
    header('Location: categorias.php?error=' . urlencode('ID no proporcionado.'));
    exit();
}
?>
