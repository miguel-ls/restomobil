<?php
session_start();
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('Location: index.php?error=Unauthorized');
    exit();
}

include_once __DIR__ . '/../backend/config/app_config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $api_url = API_BASE_URL . 'reservas.php';
    $is_editing = isset($_POST['id']) && !empty($_POST['id']);

    $data = [
        'nombre_cliente' => $_POST['nombre_cliente'],
        'telefono_cliente' => $_POST['telefono_cliente'],
        'email_cliente' => $_POST['email_cliente'],
        'fecha_reserva' => $_POST['fecha_reserva'],
        'cantidad_personas' => $_POST['cantidad_personas'],
        'id_mesa' => $_POST['id_mesa'],
        'observaciones' => $_POST['observaciones']
    ];

    if ($is_editing) {
        $data['id'] = $_POST['id'];
        $data['estado'] = $_POST['estado']; // El estado solo se envía al editar
        $http_method = 'PUT';
    } else {
        $data['estado'] = 'confirmada'; // Estado por defecto para nuevas reservas
        $http_method = 'POST';
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
    $message = $response_data['message'] ?? 'Ocurrió un error.';

    if ($http_code >= 200 && $http_code < 300) {
        header('Location: reservas.php?success=' . urlencode($message));
    } else {
        header('Location: reserva_form.php?id=' . ($data['id'] ?? '') . '&error=' . urlencode($message));
    }
    exit();

} else {
    header('Location: reservas.php');
    exit();
}
?>
