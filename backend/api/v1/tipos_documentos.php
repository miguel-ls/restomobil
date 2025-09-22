<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

include_once __DIR__ . '/../../models/SaleDocumentType.php';

$saleDocumentType = new SaleDocumentType();

if (!empty($_GET["id"])) {
    $type_id = intval($_GET["id"]);
    $type_data = $saleDocumentType->readOne($type_id);
    if ($type_data) {
        http_response_code(200);
        echo json_encode($type_data);
    } else {
        http_response_code(404);
        echo json_encode(["message" => "Tipo de documento no encontrado."]);
    }
} else {
    $stmt = $saleDocumentType->readAll();
    $num = $stmt->rowCount();

    if ($num > 0) {
        $types_arr = ["records" => []];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            array_push($types_arr["records"], $row);
        }
        http_response_code(200);
        echo json_encode($types_arr);
    } else {
        http_response_code(200);
        echo json_encode(["records" => []]);
    }
}
?>
