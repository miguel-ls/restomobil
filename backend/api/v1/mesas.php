<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

include_once __DIR__ . '/../../models/Table.php';

$table = new Table();
$request_method = $_SERVER["REQUEST_METHOD"];

function sendRecords($stmt) {
    $num = $stmt->rowCount();
    if ($num > 0) {
        $records_arr = ["records" => []];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            array_push($records_arr["records"], $row);
        }
        http_response_code(200);
        echo json_encode($records_arr);
    } else {
        http_response_code(200);
        echo json_encode(["records" => []]);
    }
}

switch ($request_method) {
    case 'GET':
        if (isset($_GET['status']) && $_GET['status'] == 'available') {
            $stmt = $table->getAvailableTables();
            sendRecords($stmt);
        } elseif (!empty($_GET["id"])) {
            $table_id = intval($_GET["id"]);
            $table_data = $table->readOne($table_id);
            if ($table_data) {
                http_response_code(200);
                echo json_encode($table_data);
            } else {
                http_response_code(404);
                echo json_encode(["message" => "Mesa no encontrada."]);
            }
        } else {
            $stmt = $table->readAll();
            sendRecords($stmt);
        }
        break;
    case 'POST':
        $data = json_decode(file_get_contents("php://input"));
        if (!empty($data->numero_mesa) && !empty($data->capacidad) && isset($data->estado) && isset($data->es_libre)) {
            $stmt = $table->create($data->numero_mesa, $data->capacidad, $data->estado, $data->es_libre);
            if ($stmt) {
                $new_table = $stmt->fetch(PDO::FETCH_ASSOC);
                http_response_code(201);
                echo json_encode(["message" => "Mesa creada.", "id" => $new_table['id']]);
            } else {
                http_response_code(503);
                echo json_encode(["message" => "No se pudo crear la mesa."]);
            }
        } else {
            http_response_code(400);
            echo json_encode(["message" => "Datos incompletos."]);
        }
        break;
    case 'PUT':
        $data = json_decode(file_get_contents("php://input"));
        $table_id = !empty($_GET['id']) ? intval($_GET['id']) : null;
        if ($table_id && !empty($data->numero_mesa) && !empty($data->capacidad) && isset($data->estado) && isset($data->es_libre)) {
            if ($table->update($table_id, $data->numero_mesa, $data->capacidad, $data->estado, $data->es_libre)) {
                http_response_code(200);
                echo json_encode(["message" => "Mesa actualizada."]);
            } else {
                http_response_code(503);
                echo json_encode(["message" => "No se pudo actualizar la mesa."]);
            }
        } else {
            http_response_code(400);
            echo json_encode(["message" => "Datos incompletos."]);
        }
        break;
    case 'DELETE':
        $table_id = !empty($_GET['id']) ? intval($_GET['id']) : null;
        if ($table_id) {
            if ($table->delete($table_id)) {
                http_response_code(200);
                echo json_encode(["message" => "Mesa eliminada."]);
            } else {
                http_response_code(503);
                echo json_encode(["message" => "No se pudo eliminar la mesa. Puede estar en uso."]);
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
