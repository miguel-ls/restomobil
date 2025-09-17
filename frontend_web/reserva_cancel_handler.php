<?php
session_start();

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('Location: index.php');
    exit();
}

if (isset($_GET['id'])) {
    $reservation_id = intval($_GET['id']);
    $api_url = "http://localhost/restaurante_system/backend/api/v1/reservas.php?id=$reservation_id";

    $ch = curl_init($api_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');

    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    $response_data = json_decode($response, true);

    if ($http_code === 200) {
        $message = urlencode($response_data['message']);
        header("Location: reservas.php?success=$message");
    } else {
        $error = isset($response_data['message']) ? urlencode($response_data['message']) : 'Error al cancelar la reserva.';
        header("Location: reservas.php?error=$error");
    }
    exit();
} else {
    header('Location: reservas.php?error=' . urlencode('No se proporcionó un ID para cancelar.'));
    exit();
}
?>
