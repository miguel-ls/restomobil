<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

include_once __DIR__ . '/../../models/Table.php';

$table = new Table();
$request_method = $_SERVER["REQUEST_METHOD"];

switch ($request_method) {
    case 'GET':
        if (!empty($_GET["id"])) {
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
            handleGetAllTables($table);
        }
        break;

    case 'POST':
        $data = json_decode(file_get_contents("php://input"));
        if (!empty($data->numero_mesa) && !empty($data->capacidad) && !empty($data->estado)) {
            $stmt = $table->create($data->numero_mesa, $data->capacidad, $data->estado);
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
            echo json_encode(["message" => "Datos incompletos para crear la mesa."]);
        }
        break;

    case 'PUT':
        $data = json_decode(file_get_contents("php://input"));
        if (!empty($data->id) && !empty($data->numero_mesa) && !empty($data->capacidad) && !empty($data->estado)) {
            if ($table->update($data->id, $data->numero_mesa, $data->capacidad, $data->estado)) {
                http_response_code(200);
                echo json_encode(["message" => "Mesa actualizada."]);
            } else {
                http_response_code(503);
                echo json_encode(["message" => "No se pudo actualizar la mesa."]);
            }
        } else {
            http_response_code(400);
            echo json_encode(["message" => "Datos incompletos para actualizar la mesa."]);
        }
        break;

    case 'DELETE':
        if (!empty($_GET["id"])) {
            $table_id = intval($_GET["id"]);
            if ($table->delete($table_id)) {
                http_response_code(200);
                echo json_encode(["message" => "Mesa eliminada."]);
            } else {
                http_response_code(503);
                echo json_encode(["message" => "No se pudo eliminar la mesa. Es posible que esté asignada a pedidos o reservas."]);
            }
        } else {
            http_response_code(400);
            echo json_encode(["message" => "No se proporcionó un ID para eliminar."]);
        }
        break;

    default:
        http_response_code(405);
        echo json_encode(["message" => "Método no permitido."]);
        break;
}

function handleGetAllTables($table) {
    $stmt = $table->readAll();
    $num = $stmt->rowCount();
    if ($num > 0) {
        $tables_arr = ["records" => []];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            extract($row);
            $table_item = [
                "id" => $id,
                "numero_mesa" => $numero_mesa,
                "capacidad" => $capacidad,
                "estado" => $estado,
            ];
            array_push($tables_arr["records"], $table_item);
        }
        http_response_code(200);
        echo json_encode($tables_arr);
    } else {
        http_response_code(200); // OK
        echo json_encode(["records" => []]);
    }
}
?>
