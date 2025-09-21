<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

include_once __DIR__ . '/../../models/Empresa.php';

$empresa_model = new Empresa();
$stmt = $empresa_model->getDepartamentos();
$num = $stmt->rowCount();

if ($num > 0) {
    $departamentos_arr = ["records" => []];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        array_push($departamentos_arr["records"], $row);
    }
    http_response_code(200);
    echo json_encode($departamentos_arr);
} else {
    http_response_code(200);
    echo json_encode(["records" => []]);
}
?>
