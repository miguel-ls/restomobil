<?php
session_start();

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('Location: index.php?error=Por+favor+inicie+sesión');
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    require_once 'config.php';
    $action = $_POST['action'] ?? '';
    $api_url = API_BASE_URL . 'apertura_cierre.php';

    $data = [
        'fecha' => $_POST['fecha'] ?? '',
        'tipo_movimiento' => $_POST['tipo_movimiento'] ?? '',
        'importe' => $_POST['importe'] ?? '',
        'descripcion' => $_POST['descripcion'] ?? '',
        'usuario_id' => $_SESSION['user_id']
    ];

    session_write_close();

    $ch = curl_init();

    curl_setopt($ch, CURLOPT_COOKIE, "PHPSESSID=" . $_COOKIE['PHPSESSID']);

    if ($action == 'create') {
        curl_setopt($ch, CURLOPT_URL, $api_url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    } elseif ($action == 'update') {
        $data['id'] = $_POST['id'];
        curl_setopt($ch, CURLOPT_URL, $api_url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    } else {
        header('Location: apertura_cierre.php?error=Acción no válida');
        exit();
    }

    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Content-Length: ' . strlen(json_encode($data))
    ]);

    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($http_code == 200 || $http_code == 201) {
        $success_message = ($action == 'create') ? 'Registro guardado con éxito.' : 'Registro actualizado con éxito.';
        header('Location: apertura_cierre.php?success=' . urlencode($success_message));
    } else {
        $error_data = json_decode($response, true);
        $error_message = $error_data['message'] ?? 'Ocurrió un error en la operación.';
        $redirect_url = 'apertura_cierre_form.php?error=' . urlencode($error_message);
        if ($action == 'update' && !empty($_POST['id'])) {
            $redirect_url .= '&id=' . $_POST['id'];
        }
        header('Location: ' . $redirect_url);
    }
    exit();

} else {
    header('Location: apertura_cierre.php');
    exit();
}
?>
