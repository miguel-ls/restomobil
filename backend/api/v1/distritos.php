<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

include_once __DIR__ . '/../../models/Empresa.php';

if(isset($_GET['prov_id'])) {
    $empresa_model = new Empresa();
    $stmt = $empresa_model->getDistritos($_GET['prov_id']);
    $num = $stmt->rowCount();

    if ($num > 0) {
        $distritos_arr = ["records" => []];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            array_push($distritos_arr["records"], $row);
        }
        http_response_code(200);
        echo json_encode($distritos_arr);
    } else {
        http_response_code(200);
        echo json_encode(["records" => []]);
    }
} else {
    http_response_code(400);
    echo json_encode(["message" => "Falta el ID de la provincia."]);
}
?>
