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
$is_caja_create_view = $view === 'caja_create';

// Recoger los datos del formulario
$id_mesa = $_POST['id_mesa'] ?? null;
$id_usuario_mozo = $_POST['id_usuario_mozo'] ?? null;
$estado = $_POST['estado'] ?? 'recibido';
$items_json = $_POST['items'] ?? '[]';
$items = json_decode($items_json);

// Datos del cliente
$id_cliente = !empty($_POST['id_cliente']) ? $_POST['id_cliente'] : null;
$id_tipo_documento_identidad_cliente = $_POST['id_tipo_documento_identidad_cliente'] ?? null;
$cliente_ruc = $_POST['cliente_ruc'] ?? null;
$cliente_nombre = $_POST['cliente_nombre'] ?? null;
$cliente_direccion = $_POST['cliente_direccion'] ?? null;
$cliente_ubigeo = $_POST['cliente_ubigeo'] ?? null;
$id_tipo_documento_venta = $_POST['id_tipo_documento_venta'] ?? null;

require_once 'config.php';

// Si se proporcionó un número de documento, intentar crear o actualizar el cliente
if (!empty($cliente_ruc) && !empty($id_tipo_documento_identidad_cliente) && !empty($cliente_nombre)) {
    $cliente_api_url = API_BASE_URL . 'clientes.php';

    // Datos del cliente para la API
    $cliente_data = [
        'id_tipo_documento_identidad' => $id_tipo_documento_identidad_cliente,
        'numero_documento' => $cliente_ruc,
        'nombres_apellidos' => $cliente_nombre,
        'direccion' => $cliente_direccion,
        'codigo_ubigeo' => $cliente_ubigeo
    ];

    $method = 'POST'; // Por defecto, crear
    if ($id_cliente) {
        // Si tenemos un ID, actualizamos
        $cliente_api_url .= "?id=$id_cliente";
        $method = 'PUT';
    } else {
        // Si no hay ID, verificamos si el cliente ya existe por su documento
        $check_url = API_BASE_URL . "clientes.php?numero_documento=" . urlencode($cliente_ruc);
        $check_response = @file_get_contents($check_url);
        if ($check_response !== false) {
            $existing_client_data = json_decode($check_response, true);
            if (!empty($existing_client_data['records'])) {
                // El cliente existe, obtenemos su ID y actualizamos
                $id_cliente = $existing_client_data['records'][0]['id'];
                $cliente_api_url .= "?id=$id_cliente";
                $method = 'PUT';
            }
        }
    }

    $ch_cliente = curl_init($cliente_api_url);
    curl_setopt($ch_cliente, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch_cliente, CURLOPT_CUSTOMREQUEST, $method);
    curl_setopt($ch_cliente, CURLOPT_POSTFIELDS, json_encode($cliente_data));
    curl_setopt($ch_cliente, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    $response_cliente = curl_exec($ch_cliente);
    $http_code_cliente = curl_getinfo($ch_cliente, CURLINFO_HTTP_CODE);
    curl_close($ch_cliente);

    if ($http_code_cliente >= 200 && $http_code_cliente < 300) {
        $response_data_cliente = json_decode($response_cliente, true);
        if (isset($response_data_cliente['id'])) {
            $id_cliente = $response_data_cliente['id']; // Asignar el ID del cliente recién creado/actualizado
        }
    } else {
        // Manejar error si la creación/actualización del cliente falla
        $error_message = urlencode(json_decode($response_cliente, true)['message'] ?? 'Error al procesar datos del cliente.');
        header("Location: pedido_form.php?id=$order_id&error=$error_message");
        exit();
    }
}


if ($is_pago_view || $is_caja_create_view) {
    $redirect_url = 'caja.php';
} else {
    $redirect_url = 'pedidos.php';
}

if (!$id_mesa || !$id_usuario_mozo || empty($items)) {
    $error_param = $is_editing ? "?id=$order_id" : "";
    if ($is_pago_view) {
        $error_param .= ($is_editing ? "&" : "?") . "view=pago";
    } else if ($is_caja_create_view) {
        $error_param = "?view=caja_create";
    }
    header("Location: pedido_form.php$error_param&error=Faltan+datos+esenciales.");
    exit();
}

// Construir el cuerpo de la solicitud para la API
$api_data = [
    'id_mesa' => $id_mesa,
    'id_usuario_mozo' => $id_usuario_mozo,
    'estado' => $estado,
    'items' => $items,
    'id_cliente' => $id_cliente,
    'id_tipo_documento_venta' => $id_tipo_documento_venta
];

// Incluir configuración de la API
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
