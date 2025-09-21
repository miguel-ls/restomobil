<?php
session_start();
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('Location: index.php');
    exit();
}

require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'] ?? null;
    $data = [
        'id_tipo_documento_identidad' => $_POST['id_tipo_documento_identidad'],
        'numero_documento' => $_POST['numero_documento'],
        'nombres_apellidos' => $_POST['nombres_apellidos'],
        'direccion' => $_POST['direccion'],
        'codigo_ubigeo' => $_POST['codigo_ubigeo'],
        'email' => $_POST['email'],
        'telefono' => $_POST['telefono'],
        'estado' => $_POST['estado'] ?? 'Activado'
    ];

    $api_url = API_BASE_URL . 'clientes.php';
    $method = 'POST';

    if (!empty($id)) {
        // Update
        $api_url .= "?id=$id";
        $method = 'PUT';
    }

    $options = [
        'http' => [
            'header'  => "Content-type: application/json\r\n",
            'method'  => $method,
            'content' => json_encode($data),
            'ignore_errors' => true
        ],
    ];

    $context  = stream_context_create($options);
    $result = file_get_contents($api_url, false, $context);

    $response = json_decode($result, true);
    $http_response_header = $http_response_header ?? [];

    $success = false;
    foreach ($http_response_header as $header) {
        if (strpos($header, 'HTTP/1.1 200') !== false || strpos($header, 'HTTP/1.1 201') !== false) {
            $success = true;
            break;
        }
    }

    if ($success) {
        $message = !empty($id) ? "Cliente actualizado con éxito." : "Cliente creado con éxito.";
        header("Location: clientes.php?success=" . urlencode($message));
    } else {
        $error_message = $response['message'] ?? 'Ocurrió un error al procesar la solicitud.';
        header("Location: cliente_form.php?id=$id&error=" . urlencode($error_message));
    }
    exit();

} else {
    header('Location: clientes.php');
    exit();
}
?>
