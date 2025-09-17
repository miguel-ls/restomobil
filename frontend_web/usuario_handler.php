<?php
session_start();

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true || $_SESSION['user_rol'] !== 'Administrador') {
    header('Location: dashboard.php?error=Acceso+no+autorizado');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $api_url = 'http://localhost/restaurante_system/backend/api/v1/usuarios.php';
    $data = [
        'nombre_completo' => $_POST['nombre_completo'],
        'email' => $_POST['email'],
        'id_rol' => $_POST['id_rol']
    ];
    $http_method = 'POST'; // Por defecto para crear

    // Si hay un ID, es una actualización (PUT)
    if (!empty($_POST['id'])) {
        $data['id'] = $_POST['id'];
        $data['activo'] = $_POST['activo']; // El estado solo se envía en la actualización
        $http_method = 'PUT';
    } else {
        // El username y la contraseña son obligatorios solo al crear
        $data['username'] = $_POST['username'];
        $data['password'] = $_POST['password'];
    }

    // La contraseña es opcional al actualizar
    if (!empty($_POST['password'])) {
        $data['password'] = $_POST['password'];
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
        header("Location: usuarios.php?success=$message");
    } else {
        $error = isset($response_data['message']) ? urlencode($response_data['message']) : 'Ocurrió un error inesperado.';
        header("Location: usuario_form.php?id=" . $_POST['id'] . "&error=$error");
    }
    exit();
} else {
    header('Location: usuarios.php');
    exit();
}
?>
