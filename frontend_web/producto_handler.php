<?php
session_start();

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('Location: index.php?error=Por+favor+inicie+sesión');
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Incluir configuración de la API
    require_once 'config.php';
    $action = $_POST['action'] ?? '';
    $api_url = API_BASE_URL . 'productos.php';
    $data = [
        'nombre' => $_POST['nombre'] ?? '',
        'descripcion' => $_POST['descripcion'] ?? '',
        'precio' => $_POST['precio'] ?? '',
        'id_categoria' => $_POST['id_categoria'] ?? null,
        'estado' => $_POST['estado'] ?? '',
        'controlar_stock' => isset($_POST['controlar_stock']) ? true : false
    ];

    $ch = curl_init();

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
        header('Location: productos.php?error=Acción no válida');
        exit();
    }

    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        'Content-Type: application/json',
        'Content-Length: ' . strlen(json_encode($data))
    ));

    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($http_code == 200 || $http_code == 201) {
        header('Location: productos.php?success=Operación realizada con éxito');
    } else {
        $error_message = json_decode($response, true)['message'] ?? 'Error desconocido en la API';
        header('Location: productos.php?error=' . urlencode($error_message));
    }
    exit();

} else {
    header('Location: productos.php');
    exit();
}
?>
