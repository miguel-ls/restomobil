<?php
session_start();

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('Location: index.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $api_url = 'http://localhost/restaurante_system/backend/api/v1/reservas.php';
    $data = [
        'id_mesa' => $_POST['id_mesa'],
        'nombre_cliente' => $_POST['nombre_cliente'],
        'telefono_cliente' => $_POST['telefono_cliente'],
        'email_cliente' => $_POST['email_cliente'],
        'fecha_reserva' => $_POST['fecha_reserva'],
        'cantidad_personas' => $_POST['cantidad_personas'],
        'observaciones' => $_POST['observaciones']
    ];
    $http_method = 'POST';

    if (!empty($_POST['id'])) {
        $reservation_id = $_POST['id'];
        $api_url .= "?id=$reservation_id";
        $data['estado'] = $_POST['estado']; // El estado solo se envía al actualizar
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
        header("Location: reservas.php?success=$message");
    } else {
        $error = isset($response_data['message']) ? urlencode($response_data['message']) : 'Ocurrió un error inesperado.';
        header("Location: reserva_form.php?id=" . $_POST['id'] . "&error=$error");
    }
    exit();
} else {
    header('Location: reservas.php');
    exit();
}
?>
