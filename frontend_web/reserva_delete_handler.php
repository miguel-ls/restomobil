<?php
session_start();
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('Location: index.php?error=Unauthorized');
    exit();
}

include_once __DIR__ . '/../backend/config/app_config.php';

if (isset($_GET['id'])) {
    $reservation_id = intval($_GET['id']);
    $api_url = API_BASE_URL . 'reservas.php?id=' . $reservation_id;

    $ch = curl_init($api_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');

    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    $response_data = json_decode($response, true);
    $message = $response_data['message'] ?? 'Ocurrió un error.';

    if ($http_code == 200) {
        header('Location: reservas.php?success=' . urlencode($message));
    } else {
        header('Location: reservas.php?error=' . urlencode($message));
    }
    exit();

} else {
    header('Location: reservas.php?error=ID de reserva no proporcionado.');
    exit();
}
?>
