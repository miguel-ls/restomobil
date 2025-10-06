<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

require_once __DIR__ . '/../../models/UnidadMedida.php';

$unidadMedida = new UnidadMedida();
$request_method = $_SERVER["REQUEST_METHOD"];

switch ($request_method) {
    case 'GET':
        if (!empty($_GET["id"])) {
            $id = intval($_GET["id"]);
            $data = $unidadMedida->readOne($id);

            if ($data) {
                http_response_code(200);
                echo json_encode($data);
            } else {
                http_response_code(404);
                echo json_encode(["message" => "Unidad de medida no encontrada."]);
            }
        } else {
            $filter = isset($_GET['filter']) ? $_GET['filter'] : '';
            $page = isset($_GET['page']) ? intval($_GET['page']) : 1;
            $limit = 10;
            $offset = ($page - 1) * $limit;

            $records = $unidadMedida->readAll($filter, $limit, $offset);
            $total_records = $unidadMedida->countAll($filter);

            $response_data = [
                "records" => $records,
                "pagination" => [
                    "total_records" => (int)$total_records,
                    "total_pages" => $total_records > 0 ? ceil($total_records / $limit) : 0,
                    "current_page" => $page,
                    "limit" => $limit
                ]
            ];
            http_response_code(200);
            echo json_encode($response_data);
        }
        break;

    case 'POST':
        $data = json_decode(file_get_contents("php://input"));

        if (!empty($data->codigo) && !empty($data->descripcion)) {
            $new_record = $unidadMedida->create($data->codigo, $data->descripcion);
            if ($new_record) {
                http_response_code(201);
                echo json_encode(["message" => "Unidad de medida creada con éxito.", "id" => $new_record['id']]);
            } else {
                http_response_code(503);
                echo json_encode(["message" => "No se pudo crear la unidad de medida."]);
            }
        } else {
            http_response_code(400);
            echo json_encode(["message" => "Datos incompletos. Código y descripción son obligatorios."]);
        }
        break;

    case 'PUT':
        $data = json_decode(file_get_contents("php://input"));
        $id = !empty($_GET['id']) ? intval($_GET['id']) : null;

        if ($id && !empty($data->codigo) && !empty($data->descripcion) && isset($data->estado)) {
            if ($unidadMedida->update($id, $data->codigo, $data->descripcion, $data->estado)) {
                http_response_code(200);
                echo json_encode(["message" => "Unidad de medida actualizada con éxito."]);
            } else {
                http_response_code(503);
                echo json_encode(["message" => "No se pudo actualizar la unidad de medida."]);
            }
        } else {
            http_response_code(400);
            echo json_encode(["message" => "Datos incompletos para actualizar."]);
        }
        break;

    case 'DELETE':
        $id = !empty($_GET['id']) ? intval($_GET['id']) : null;

        if ($id) {
            if ($unidadMedida->delete($id)) {
                http_response_code(200);
                echo json_encode(["message" => "Unidad de medida eliminada con éxito."]);
            } else {
                http_response_code(503);
                echo json_encode(["message" => "No se pudo eliminar la unidad de medida."]);
            }
        } else {
            http_response_code(400);
            echo json_encode(["message" => "ID no proporcionado."]);
        }
        break;

    default:
        http_response_code(405);
        echo json_encode(["message" => "Método no permitido."]);
        break;
}
?>