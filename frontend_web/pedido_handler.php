<?php
session_start();
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('Location: index.php?error=No+autorizado');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: pedidos.php');
    exit();
}

$is_editing = isset($_GET['id']);
$order_id = $is_editing ? intval($_GET['id']) : null;
$view = $_GET['view'] ?? 'edit';
$is_pago_view = $view === 'pago';

// Recoger los datos del formulario
$id_mesa = $_POST['id_mesa'] ?? null;
$id_usuario_mozo = $_POST['id_usuario_mozo'] ?? null;
$estado = $_POST['estado'] ?? 'recibido';
$items_json = $_POST['items'] ?? '[]';
$items = json_decode($items_json);

$redirect_url = $is_pago_view ? 'caja.php' : 'pedidos.php';

if (!$id_mesa || !$id_usuario_mozo || empty($items)) {
    $error_param = $is_editing ? "?id=$order_id" : "";
    if ($is_pago_view) {
        $error_param .= ($is_editing ? "&" : "?") . "view=pago";
    }
    header("Location: pedido_form.php$error_param&error=Faltan+datos+esenciales.");
    exit();
}

// Construir el cuerpo de la solicitud para la API
$api_data = [
    'id_mesa' => $id_mesa,
    'id_usuario_mozo' => $id_usuario_mozo,
    'estado' => $estado,
    'items' => $items
];

// Incluir configuración de la API
require_once 'config.php';
$api_url = API_BASE_URL . 'pedidos.php';
$method = 'POST';

if ($is_editing) {
    $api_url .= "?id=$order_id";
    $method = 'PUT';
}

$ch = curl_init($api_url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($api_data));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Content-Length: ' . strlen(json_encode($api_data))
]);

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

$response_data = json_decode($response, true);
$message_key = ($http_code >= 200 && $http_code < 300) ? 'success' : 'error';
$message = urlencode($response_data['message'] ?? 'Ocurrió un error inesperado.');

header("Location: $redirect_url?$message_key=$message");
exit();
?>
