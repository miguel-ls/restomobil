<?php
session_start();
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('Location: index.php');
    exit();
}

if (isset($_GET['id'])) {
    require_once 'config.php';
    $id = intval($_GET['id']);
    $api_url = API_BASE_URL . 'unidades_medida.php?id=' . $id;

    $ch = curl_init($api_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');

    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    $response_data = json_decode($response, true);
    $message_key = ($http_code >= 200 && $http_code < 300) ? 'success' : 'error';
    $message = urlencode($response_data['message'] ?? 'Ocurrió un error al eliminar la unidad de medida.');

    header("Location: unidades_medida.php?$message_key=$message");
    exit();
} else {
    // Si no se proporciona un ID, redirigir a la lista
    header('Location: unidades_medida.php');
    exit();
}
?>