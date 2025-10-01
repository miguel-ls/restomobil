<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

include_once __DIR__ . '/../../models/Impuesto.php';

$impuesto = new Impuesto();
$request_method = $_SERVER["REQUEST_METHOD"];

switch ($request_method) {
    case 'GET':
        // Si se solicita la lista de códigos para el filtro
        if (isset($_GET['action']) && $_GET['action'] == 'getCodigos') {
            $stmt = $impuesto->readCodigos();
            $num = $stmt->rowCount();

            if ($num > 0) {
                $codigos_arr = ["records" => []];
                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    array_push($codigos_arr["records"], $row);
                }
                http_response_code(200);
                echo json_encode($codigos_arr);
            } else {
                http_response_code(200);
                echo json_encode(["records" => []]);
            }
        }
        // Si se solicita un impuesto por ID
        elseif (!empty($_GET["id"])) {
            $impuesto_id = intval($_GET["id"]);
            $impuesto_data = $impuesto->readOne($impuesto_id);

            if ($impuesto_data) {
                http_response_code(200);
                echo json_encode($impuesto_data);
            } else {
                http_response_code(404);
                echo json_encode(["message" => "Impuesto no encontrado."]);
            }
        }
        // Si se solicita la lista de impuestos con filtros y paginación (versión corregida)
        else {
            // Parámetros de filtro y paginación
            $codigo = isset($_GET['codigo']) && $_GET['codigo'] !== '' ? $_GET['codigo'] : null;
            $estado_param = isset($_GET['estado']) && $_GET['estado'] !== '' ? $_GET['estado'] : null;
            $estado = is_null($estado_param) ? null : filter_var($estado_param, FILTER_VALIDATE_BOOLEAN);

            $page = isset($_GET['page']) ? intval($_GET['page']) : 1;
            $limit = 10;
            $offset = ($page - 1) * $limit;

            // Obtener registros y total. El modelo ahora maneja el cierre de cursores.
            $records = $impuesto->readAll($codigo, $estado, $offset, $limit);
            $total_records = $impuesto->countAll($codigo, $estado);

            // Procesar registros para asegurar que el estado sea booleano
            $processed_records = array_map(function($row) {
                $row['estado'] = (bool)$row['estado'];
                return $row;
            }, $records);

            $response_data = [
                "records" => $processed_records,
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

        if (!empty($data->codigo) && !empty($data->fecha_inicial) && !empty($data->fecha_final) && isset($data->valor)) {
            $estado = isset($data->estado) ? (bool)$data->estado : true;

            if ($impuesto->create($data->codigo, $data->fecha_inicial, $data->fecha_final, $data->valor, $estado)) {
                http_response_code(201);
                echo json_encode(["message" => "Impuesto creado con éxito."]);
            } else {
                http_response_code(503);
                echo json_encode(["message" => "No se pudo crear el impuesto."]);
            }
        } else {
            http_response_code(400);
            echo json_encode(["message" => "Datos incompletos."]);
        }
        break;

    case 'PUT':
        $data = json_decode(file_get_contents("php://input"));
        $impuesto_id = !empty($_GET['id']) ? intval($_GET['id']) : null;

        if ($impuesto_id && !empty($data->codigo) && !empty($data->fecha_inicial) && !empty($data->fecha_final) && isset($data->valor)) {
             $estado = isset($data->estado) ? (bool)$data->estado : true;

            if ($impuesto->update($impuesto_id, $data->codigo, $data->fecha_inicial, $data->fecha_final, $data->valor, $estado)) {
                http_response_code(200);
                echo json_encode(["message" => "Impuesto actualizado con éxito."]);
            } else {
                http_response_code(503);
                echo json_encode(["message" => "No se pudo actualizar el impuesto."]);
            }
        } else {
            http_response_code(400);
            echo json_encode(["message" => "Datos incompletos para actualizar."]);
        }
        break;

    case 'DELETE':
        $impuesto_id = !empty($_GET['id']) ? intval($_GET['id']) : null;

        if ($impuesto_id) {
            if ($impuesto->delete($impuesto_id)) {
                http_response_code(200);
                echo json_encode(["message" => "Impuesto eliminado con éxito."]);
            } else {
                http_response_code(503);
                echo json_encode(["message" => "No se pudo eliminar el impuesto."]);
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