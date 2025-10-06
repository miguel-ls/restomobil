<?php
session_start();
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('Location: index.php?error=Por+favor+inicie+sesión');
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    require_once 'config.php';

    $movimiento_id = $_POST['id'] ? intval($_POST['id']) : 0;
    $action = $movimiento_id ? 'update' : 'create';

    // --- Estructurar datos de la cabecera ---
    $data = [
        'fecha_movimiento' => $_POST['fecha_movimiento'],
        'codigo_movimiento' => intval($_POST['codigo_movimiento']),
        'estado' => $_POST['estado'],
        'tipo_documento' => $_POST['tipo_documento'] ?? null,
        'serie_documento' => $_POST['serie_documento'] ?? null,
        'numero_documento' => $_POST['numero_documento'] ?? null,
        'tipo_entidad' => $_POST['tipo_entidad'] ?? null,
        'id_entidad' => !empty($_POST['id_entidad']) ? intval($_POST['id_entidad']) : null,
        'detalle' => []
    ];

    // --- Estructurar datos del detalle ---
    if (isset($_POST['detalle']) && is_array($_POST['detalle'])) {
        foreach ($_POST['detalle'] as $item) {
            if (!empty($item['id_producto'])) {
                $data['detalle'][] = [
                    'item' => count($data['detalle']) + 1,
                    'id_producto' => intval($item['id_producto']),
                    'cantidad' => floatval($item['cantidad']),
                    'costo_unitario' => floatval($item['costo_unitario']),
                    'descripcion' => $item['descripcion'] ?? '',
                    'codigo_unidad_medida' => 'NIU' // Simplificación, debería venir del producto
                ];
            }
        }
    }

    // Determinar año, periodo y tipo de movimiento
    $fecha = new DateTime($data['fecha_movimiento']);
    $data['anio'] = $fecha->format('Y');
    $data['periodo'] = $fecha->format('m');

    // Para obtener el tipo (E/S), necesitamos consultarlo.
    // Una forma simple es pasarlo desde el formulario, pero para mantenerlo seguro, lo ideal es consultarlo en el backend.
    // Por ahora, el backend lo deduce desde el `codigo_movimiento`.
    // El modelo de la API ya lo hace, así que no es necesario enviarlo desde aquí.

    // --- Configurar y ejecutar cURL ---
    $ch = curl_init();
    $api_url = API_BASE_URL . 'movimientos.php';

    if ($action == 'create') {
        curl_setopt($ch, CURLOPT_URL, $api_url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    } else { // update
        curl_setopt($ch, CURLOPT_URL, $api_url . '?id=' . $movimiento_id);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    }

    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Content-Length: ' . strlen(json_encode($data))
    ]);

    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);

    if ($error) {
        header('Location: movimientos.php?error=' . urlencode('Error de cURL: ' . $error));
        exit();
    }

    if ($http_code >= 200 && $http_code < 300) {
        header('Location: movimientos.php?success=Operación realizada con éxito');
    } else {
        $error_message = json_decode($response, true)['message'] ?? 'Error desconocido en la API. Código: ' . $http_code;
        header('Location: movimiento_form.php?id=' . $movimiento_id . '&error=' . urlencode($error_message));
    }
    exit();

} else {
    header('Location: movimientos.php');
    exit();
}
?>