<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'] ?? null;
    $is_edit = !empty($id);

    $data = [
        'tipo' => $_POST['tipo'],
        'codigo' => $_POST['codigo'],
        'descripcion' => $_POST['descripcion'],
    ];

    if ($is_edit) {
        $data['id'] = $id;
        $data['estado'] = $_POST['estado'];
    }

    $api_url = API_BASE_URL . 'tipo_movimiento.php';
    $method = $is_edit ? 'PUT' : 'POST';

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

    $response_data = json_decode($result, true);
    $http_response_header = $http_response_header ?? [];

    $success = false;
    foreach ($http_response_header as $header) {
        if (strpos($header, 'HTTP/1.1 200') !== false || strpos($header, 'HTTP/1.1 201') !== false) {
            $success = true;
            break;
        }
    }

    if ($success) {
        $message = $is_edit ? "Registro actualizado con éxito." : "Registro creado con éxito.";
        header("Location: tipo_movimiento.php?success=" . urlencode($message));
    } else {
        $error_message = $response_data['message'] ?? 'Ocurrió un error desconocido.';
        header("Location: tipo_movimiento_form.php?" . ($is_edit ? "id=$id&" : "") . "error=" . urlencode($error_message));
    }
    exit();

} else {
    header('Location: tipo_movimiento.php');
    exit();
}
?>