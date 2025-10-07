<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

require_once __DIR__ . '/../../models/TipoMovimiento.php';

$model = new TipoMovimiento();
$request_method = $_SERVER["REQUEST_METHOD"];

switch ($request_method) {
    case 'GET':
        if (!empty($_GET["id"])) {
            // Obtener un solo tipo de movimiento
            $id = intval($_GET["id"]);
            $data = $model->readOne($id);
            if ($data) {
                http_response_code(200);
                echo json_encode($data);
            } else {
                http_response_code(404);
                echo json_encode(["message" => "Tipo de Movimiento no encontrado."]);
            }
        } else {
            // Obtener todos los tipos de movimiento con filtros
            $filters = [];
            if (!empty($_GET['descripcion'])) $filters['descripcion'] = $_GET['descripcion'];
            if (!empty($_GET['estado'])) $filters['estado'] = $_GET['estado'];

            $stmt = $model->readAll($filters);
            $records = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if (count($records) > 0) {
                http_response_code(200);
                echo json_encode($records);
            } else {
                http_response_code(404);
                echo json_encode(["message" => "No se encontraron tipos de movimiento."]);
            }
        }
        break;

    case 'POST':
        $data = json_decode(file_get_contents("php://input"));
        if (!empty($data->tipo) && !empty($data->codigo) && !empty($data->descripcion)) {
            $stmt = $model->create($data->tipo, $data->codigo, $data->descripcion);
            if ($stmt) {
                $new_record = $stmt->fetch(PDO::FETCH_ASSOC);
                http_response_code(201);
                echo json_encode(["message" => "Tipo de Movimiento creado.", "id" => $new_record['id']]);
            } else {
                http_response_code(503);
                echo json_encode(["message" => "No se pudo crear el Tipo de Movimiento."]);
            }
        } else {
            http_response_code(400);
            echo json_encode(["message" => "Datos incompletos."]);
        }
        break;

    case 'PUT':
        $data = json_decode(file_get_contents("php://input"));
        if (!empty($data->id) && !empty($data->tipo) && !empty($data->codigo) && !empty($data->descripcion) && !empty($data->estado)) {
            if ($model->update($data->id, $data->tipo, $data->codigo, $data->descripcion, $data->estado)) {
                http_response_code(200);
                echo json_encode(["message" => "Tipo de Movimiento actualizado."]);
            } else {
                http_response_code(503);
                echo json_encode(["message" => "No se pudo actualizar el Tipo de Movimiento."]);
            }
        } else {
            http_response_code(400);
            echo json_encode(["message" => "Datos incompletos."]);
        }
        break;

    case 'DELETE':
        if (!empty($_GET["id"])) {
            $id = intval($_GET["id"]);
            if ($model->delete($id)) {
                http_response_code(200);
                echo json_encode(["message" => "Tipo de Movimiento eliminado (desactivado)."]);
            } else {
                http_response_code(503);
                echo json_encode(["message" => "No se pudo eliminar el Tipo de Movimiento."]);
            }
        } else {
            http_response_code(400);
            echo json_encode(["message" => "No se proporcionó un ID."]);
        }
        break;

    default:
        http_response_code(405);
        echo json_encode(["message" => "Método no permitido."]);
        break;
}
?>