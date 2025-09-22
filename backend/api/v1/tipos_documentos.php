<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

include_once __DIR__ . '/../../models/SaleDocumentType.php';

$saleDocumentType = new SaleDocumentType();

$stmt = $saleDocumentType->readAll();
$num = $stmt->rowCount();

if ($num > 0) {
    $types_arr = ["records" => []];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        // The SP only returns active records, but we ensure the 'estado' key exists for consistency if needed later.
        if (!isset($row['estado'])) {
            $row['estado'] = true;
        }
        array_push($types_arr["records"], $row);
    }
    http_response_code(200);
    echo json_encode($types_arr);
} else {
    http_response_code(200);
    echo json_encode(["records" => []]);
}
?>
