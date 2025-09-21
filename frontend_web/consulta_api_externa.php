<?php
header("Content-Type: application/json; charset=UTF-8");

// Simple token-based security. In a real app, use something more robust.
// This is just to prevent wide-open access. A real implementation
// might use session-based authentication.
// For now, this token is not required, but it's a placeholder for better security.
$EXPECTED_TOKEN = "un-token-secreto-muy-seguro";

if (!isset($_GET['tipo']) || !isset($_GET['numero'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Parámetros tipo y numero son requeridos.']);
    exit;
}

$tipo = strtolower($_GET['tipo']);
$numero = $_GET['numero'];
$base_url = 'https://api.apis.net.pe/v1/';
$api_url = '';

if ($tipo === 'ruc') {
    $api_url = $base_url . 'ruc?numero=' . urlencode($numero);
} elseif ($tipo === 'dni') {
    $api_url = $base_url . 'dni?numero=' . urlencode($numero);
} else {
    http_response_code(400);
    echo json_encode(['error' => 'Tipo de consulta no válido. Use "ruc" o "dni".']);
    exit;
}

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $api_url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
// Optional: Add an Authorization header if the API requires it.
// curl_setopt($ch, CURLOPT_HTTPHEADER, array('Authorization: Bearer YOUR_API_TOKEN'));
curl_setopt($ch, CURLOPT_TIMEOUT, 10); // 10-second timeout

$response = curl_exec($ch);
$httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curl_error = curl_error($ch);
curl_close($ch);

if ($curl_error) {
    http_response_code(500);
    echo json_encode(['error' => 'Error en la consulta a la API externa.', 'details' => $curl_error]);
    exit;
}

if ($httpcode >= 400) {
    // Forward the status code from the external API
    http_response_code($httpcode);
}

// Return the response from the external API directly to the client
echo $response;
?>
