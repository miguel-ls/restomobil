<?php
session_start();

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true || $_SESSION['user_rol'] !== 'Administrador') {
    header('Location: dashboard.php?error=Acceso+no+autorizado');
    exit();
}

if (isset($_GET['id'])) {
    $user_id = intval($_GET['id']);

    // Salvaguarda para no eliminar al administrador principal
    if ($user_id === 1) {
        header('Location: usuarios.php?error=' . urlencode('No se puede desactivar al administrador principal.'));
        exit();
    }

    $api_url = "http://localhost/restaurante_system/backend/api/v1/usuarios.php?id=$user_id";

    $ch = curl_init($api_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');

    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    $response_data = json_decode($response, true);

    if ($http_code === 200) {
        $message = urlencode($response_data['message']);
        header("Location: usuarios.php?success=$message");
    } else {
        $error = isset($response_data['message']) ? urlencode($response_data['message']) : 'Error al desactivar el usuario.';
        header("Location: usuarios.php?error=$error");
    }
    exit();
} else {
    header('Location: usuarios.php?error=' . urlencode('No se proporcionó un ID.'));
    exit();
}
?>
