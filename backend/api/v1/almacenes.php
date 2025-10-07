<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

// Incluir el modelo de Almacen (se creará en el siguiente paso)
include_once __DIR__ . '/../../models/Almacen.php';

// Instanciar el objeto Almacen
try {
    $almacen = new Almacen();
} catch (PDOException $e) {
    http_response_code(503);
    echo json_encode(["message" => "No se puede conectar a la base de datos."]);
    exit();
}

$request_method = $_SERVER["REQUEST_METHOD"];

switch ($request_method) {
    case 'GET':
        if (!empty($_GET["id"])) {
            // Obtener un solo almacén por ID
            $almacen_id = intval($_GET["id"]);
            $almacen_data = $almacen->readOne($almacen_id);

            if ($almacen_data) {
                http_response_code(200);
                echo json_encode($almacen_data);
            } else {
                http_response_code(404);
                echo json_encode(["message" => "Almacén no encontrado."]);
            }
        } else {
            // Obtener lista de almacenes con filtros y paginación
            $nombre = isset($_GET['nombre']) ? $_GET['nombre'] : null;
            $estado_param = isset($_GET['estado']) && $_GET['estado'] !== '' ? $_GET['estado'] : null;
            $estado = is_null($estado_param) ? null : filter_var($estado_param, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);

            $page = isset($_GET['page']) ? intval($_GET['page']) : 1;
            $limit = 10;
            $offset = ($page - 1) * $limit;

            $records = $almacen->readAll($nombre, $estado, $offset, $limit);
            $total_records = $almacen->countAll($nombre, $estado);

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

        if (!empty($data->nombre)) {
            $new_id = $almacen->create($data->nombre);
            if ($new_id) {
                http_response_code(201);
                echo json_encode(["message" => "Almacén creado con éxito.", "id" => $new_id]);
            } else {
                http_response_code(503);
                echo json_encode(["message" => "No se pudo crear el almacén."]);
            }
        } else {
            http_response_code(400);
            echo json_encode(["message" => "Datos incompletos. El nombre es obligatorio."]);
        }
        break;

    case 'PUT':
        $data = json_decode(file_get_contents("php://input"));
        $almacen_id = !empty($_GET['id']) ? intval($_GET['id']) : null;

        if ($almacen_id && !empty($data->nombre) && isset($data->estado)) {
            $estado = (bool)$data->estado;
            if ($almacen->update($almacen_id, $data->nombre, $estado)) {
                http_response_code(200);
                echo json_encode(["message" => "Almacén actualizado con éxito."]);
            } else {
                http_response_code(503);
                echo json_encode(["message" => "No se pudo actualizar el almacén."]);
            }
        } else {
            http_response_code(400);
            echo json_encode(["message" => "Datos incompletos para actualizar. Se requiere ID, nombre y estado."]);
        }
        break;

    case 'DELETE':
        $almacen_id = !empty($_GET['id']) ? intval($_GET['id']) : null;

        if ($almacen_id) {
            // Usamos el método de desactivación para eliminación lógica
            if ($almacen->deactivate($almacen_id)) {
                http_response_code(200);
                echo json_encode(["message" => "Almacén desactivado con éxito."]);
            } else {
                http_response_code(503);
                echo json_encode(["message" => "No se pudo desactivar el almacén."]);
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