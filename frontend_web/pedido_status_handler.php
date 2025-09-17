<?php
session_start();

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('Location: index.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['id_pedido']) && isset($_POST['nuevo_estado'])) {
        $order_id = intval($_POST['id_pedido']);
        $new_status = $_POST['nuevo_estado'];

        $api_url = "http://localhost/restaurante_system/backend/api/v1/pedidos.php?id=$order_id";
        $data = ['estado' => $new_status];

        $ch = curl_init($api_url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Content-Length: ' . strlen(json_encode($data))
        ]);

        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $response_data = json_decode($response, true);

        if ($http_code === 200) {
            $message = urlencode($response_data['message']);
            header("Location: pedido_detalle.php?id=$order_id&success=$message");
        } else {
            $error = isset($response_data['message']) ? urlencode($response_data['message']) : 'Ocurrió un error inesperado.';
            header("Location: pedido_detalle.php?id=$order_id&error=$error");
        }
        exit();
    } else {
        header('Location: pedidos.php?error=' . urlencode('Datos insuficientes para actualizar el estado.'));
        exit();
    }
} else {
    header('Location: pedidos.php');
    exit();
}
?>
