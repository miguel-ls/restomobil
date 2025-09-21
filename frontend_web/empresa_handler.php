<?php
session_start();
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('Location: index.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_once 'config.php';
    $api_url = API_BASE_URL . 'empresas.php';

    $is_editing = !empty($_POST['id']);

    $post_data = $_POST;

    // Handle file upload
    if (isset($_FILES['logo']) && $_FILES['logo']['error'] == UPLOAD_ERR_OK) {
        $file_name = $_FILES['logo']['name'];
        $file_tmp = $_FILES['logo']['tmp_name'];
        $file_type = $_FILES['logo']['type'];

        // Use cURL to send multipart/form-data
        $cfile = new CURLFile($file_tmp, $file_type, $file_name);
        $post_data['logo'] = $cfile;
    }

    $ch = curl_init();

    // For PUT requests, we simulate it with POST and a _method field
    if ($is_editing) {
        // The API handles the PUT logic via POST override
    }

    curl_setopt($ch, CURLOPT_URL, $api_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
    // Let cURL set the Content-Type to multipart/form-data

    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    $response_data = json_decode($response, true);
    $message_key = ($http_code >= 200 && $http_code < 300) ? 'success' : 'error';
    $message = urlencode($response_data['message'] ?? 'Ocurrió un error.');

    header("Location: empresas.php?$message_key=$message");
    exit();
} else {
    header('Location: empresas.php');
    exit();
}
?>
