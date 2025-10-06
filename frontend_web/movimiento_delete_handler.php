<?php
session_start();

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('Location: index.php?error=Por+favor+inicie+sesión');
    exit();
}

if (!isset($_GET['id']) || empty($_GET['id'])) {
    header('Location: movimientos.php?error=ID de movimiento no proporcionado');
    exit();
}

$movimiento_id = $_GET['id'];

require_once 'config.php';
$api_url = API_BASE_URL . 'movimientos.php?id=' . $movimiento_id;

$ch = curl_init($api_url);
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);
curl_close($ch);

if ($error) {
    header('Location: movimientos.php?error=' . urlencode('Error de cURL: ' . $error));
    exit();
}

if ($http_code == 200) {
    header('Location: movimientos.php?success=Movimiento anulado con éxito');
} else {
    $error_message = json_decode($response, true)['message'] ?? 'Error desconocido al anular el movimiento';
    header('Location: movimientos.php?error=' . urlencode($error_message));
}
exit();
?>