<?php
session_start();

// Validar que se recibieron los datos del formulario
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['email']) && isset($_POST['password'])) {

    // URL del endpoint de la API de login
    // Esta URL podría necesitar ser ajustada dependiendo del entorno
    $api_url = 'http://localhost/restaurante_system/backend/api/v1/login.php';

    // Datos a enviar a la API
    $data = array(
        'email' => $_POST['email'],
        'password' => $_POST['password']
    );

    // Configurar la petición cURL
    $ch = curl_init($api_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        'Content-Type: application/json',
        'Content-Length: ' . strlen(json_encode($data))
    ));

    // Ejecutar la petición y obtener la respuesta
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    // Decodificar la respuesta JSON
    $result = json_decode($response, true);

    // Verificar si el login fue exitoso
    if ($http_code == 200 && isset($result['user'])) {
        // Guardar la información del usuario en la sesión
        $_SESSION['user_id'] = $result['user']['id'];
        $_SESSION['user_nombre'] = $result['user']['nombre'];
        $_SESSION['user_email'] = $result['user']['email'];
        $_SESSION['user_rol'] = $result['user']['rol'];
        $_SESSION['loggedin'] = true;

        // Redirigir al dashboard
        header("Location: dashboard.php");
        exit();
    } else {
        // Si el login falla, redirigir de vuelta al index con un mensaje de error
        $error_message = isset($result['message']) ? urlencode($result['message']) : 'Error desconocido.';
        header("Location: index.php?error=" . $error_message);
        exit();
    }

} else {
    // Si no es un POST o faltan datos, redirigir al index
    header("Location: index.php");
    exit();
}
?>
