<?php
session_start();

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('Location: index.php?error=Por+favor+inicie+sesión');
    exit();
}

if (isset($_GET['id'])) {
    $product_id = intval($_GET['id']);

    // Incluir configuración de la API
    require_once '../backend/config/app_config.php';
    $api_url = API_BASE_URL . "productos.php?id=" . $product_id;

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $api_url);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($http_code == 200) {
        header('Location: productos.php?success=Producto eliminado con éxito');
    } else {
        $error_message = json_decode($response, true)['message'] ?? 'Error al eliminar el producto';
        header('Location: productos.php?error=' . urlencode($error_message));
    }
    exit();

} else {
    header('Location: productos.php?error=No se proporcionó un ID de producto');
    exit();
}
?>
