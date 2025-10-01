<?php
session_start();
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('Location: index.php?error=Por+favor+inicie+sesión');
    exit();
}

include_once __DIR__ . '/config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_GET['id'])) {
    $venta_id = intval($_GET['id']);

    // Recoger los datos del formulario
    $data = [
        'id' => $venta_id,
        'fecha_emision' => $_POST['fecha_emision'] ?? null,
        'nombre_cliente' => $_POST['nombre_cliente'] ?? null,
        'ruc_cliente' => $_POST['ruc_cliente'] ?? null,
        'direccion_cliente' => $_POST['direccion_cliente'] ?? null,
    ];

    // Validar datos (simplificado, se puede expandir)
    if (empty($data['fecha_emision']) || empty($data['nombre_cliente'])) {
        header('Location: venta_edit_form.php?id=' . $venta_id . '&error=Datos incompletos');
        exit;
    }

    // Enviar datos a la API para actualizar
    $api_url = API_BASE_URL . 'ventas.php';
    $ch = curl_init($api_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT'); // Usar PUT para actualizaciones
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);

    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    $response_data = json_decode($response, true);

    if ($http_code === 200 && $response_data && ($response_data['success'] ?? false)) {
        header('Location: ventas.php?success=Venta+actualizada+correctamente');
    } else {
        $error_message = $response_data['message'] ?? 'Error al actualizar la venta.';
        header('Location: venta_edit_form.php?id=' . $venta_id . '&error=' . urlencode($error_message));
    }
    exit;

} else {
    // Redirigir si no es una solicitud POST válida
    header('Location: ventas.php');
    exit;
}
?>