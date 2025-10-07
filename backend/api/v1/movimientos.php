<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

require_once __DIR__ . '/../../models/Movimiento.php';

try {
    $movimiento = new Movimiento();

    $filter = $_GET['filter'] ?? '';
    $tipo_movimiento = $_GET['tipo_movimiento'] ?? null;
    $tipo_entidad = $_GET['tipo_entidad'] ?? null;
    $page = isset($_GET['page']) ? intval($_GET['page']) : 1;
    $limit = 10;
    $offset = ($page - 1) * $limit;

    $movimientos = $movimiento->getAll($filter, $tipo_movimiento, $tipo_entidad, $limit, $offset);
    $total_records = $movimiento->count($filter, $tipo_movimiento, $tipo_entidad);

    $response = [
        "records" => $movimientos,
        "pagination" => [
            "page" => $page,
            "total_records" => (int)$total_records,
            "total_pages" => ceil($total_records / $limit)
        ]
    ];

    http_response_code(200);
    echo json_encode($response, JSON_INVALID_UTF8_SUBSTITUTE);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["message" => "Error en el servidor al obtener movimientos.", "error" => $e->getMessage()]);
}
?>