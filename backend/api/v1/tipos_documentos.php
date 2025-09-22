<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

include_once __DIR__ . '/../../models/SaleDocumentType.php';

$saleDocumentType = new SaleDocumentType();
$request_method = $_SERVER["REQUEST_METHOD"];

switch ($request_method) {
    case 'GET':
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
        break;
    case 'PUT':
        $data = json_decode(file_get_contents("php://input"));
        $type_id = !empty($_GET['id']) ? intval($_GET['id']) : null;
        if ($type_id && !empty($data->codigo) && !empty($data->nombre)) {
            if ($saleDocumentType->update($type_id, $data->codigo, $data->nombre, $data->descripcion ?? '', $data->estado ?? true)) {
                http_response_code(200);
                echo json_encode(["message" => "Tipo de documento actualizado."]);
            } else {
                http_response_code(503);
                echo json_encode(["message" => "No se pudo actualizar el tipo de documento."]);
            }
        } else {
            http_response_code(400);
            echo json_encode(["message" => "Datos incompletos."]);
        }
        break;
    // POST and DELETE handlers can be added here if needed
    default:
        http_response_code(405);
        echo json_encode(["message" => "Método no permitido."]);
        break;
}
?>
