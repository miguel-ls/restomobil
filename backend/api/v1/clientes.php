<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

include_once __DIR__ . '/../../models/Cliente.php';

$cliente = new Cliente();
$request_method = $_SERVER["REQUEST_METHOD"];

switch ($request_method) {
    case 'GET':
        if (!empty($_GET["id"])) {
            $cliente_id = intval($_GET["id"]);
            $cliente_data = $cliente->readOne($cliente_id);
            if ($cliente_data) {
                http_response_code(200);
                echo json_encode($cliente_data);
            } else {
                http_response_code(404);
                echo json_encode(["message" => "Cliente no encontrado."]);
            }
        } else {
            $stmt = $cliente->readAll();
            $num = $stmt->rowCount();
            if ($num > 0) {
                $clientes_arr = ["records" => []];
                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    array_push($clientes_arr["records"], $row);
                }
                http_response_code(200);
                echo json_encode($clientes_arr);
            } else {
                http_response_code(200);
                echo json_encode(["records" => []]);
            }
        }
        break;
    case 'POST':
        $data = json_decode(file_get_contents("php://input"));
        if (empty($data->id_tipo_documento_identidad) || empty($data->numero_documento) || empty($data->nombres_apellidos)) {
            http_response_code(400);
            echo json_encode(["message" => "Datos incompletos."]);
            break;
        }

        try {
            $stmt = $cliente->create(
                $data->id_tipo_documento_identidad,
                $data->numero_documento,
                $data->nombres_apellidos,
                $data->direccion ?? '',
                $data->codigo_ubigeo ?? '',
                $data->email ?? '',
                $data->telefono ?? ''
            );

            if ($stmt) {
                $new_cliente = $stmt->fetch(PDO::FETCH_ASSOC);
                http_response_code(201);
                echo json_encode(["message" => "Cliente creado.", "id" => $new_cliente['id']]);
            } else {
                http_response_code(503);
                echo json_encode(["message" => "No se pudo crear el cliente."]);
            }
        } catch (PDOException $e) {
            if ($e->getCode() == '23000') { // 23000 is the SQLSTATE for integrity constraint violation
                http_response_code(409); // Conflict
                echo json_encode(["message" => "Ya existe un cliente con el tipo y número de documento especificado."]);
            } else {
                http_response_code(503);
                echo json_encode(["message" => "Error en la base de datos.", "error" => $e->getMessage()]);
            }
        }
        break;
    case 'PUT':
        $data = json_decode(file_get_contents("php://input"));
        $cliente_id = !empty($_GET['id']) ? intval($_GET['id']) : null;
        if ($cliente_id && !empty($data->id_tipo_documento_identidad) && !empty($data->numero_documento) && !empty($data->nombres_apellidos)) {
            $estado = $data->estado ?? 'Activado';
            if ($cliente->update(
                $cliente_id,
                $data->id_tipo_documento_identidad,
                $data->numero_documento,
                $data->nombres_apellidos,
                $data->direccion ?? '',
                $data->codigo_ubigeo ?? '',
                $data->email ?? '',
                $data->telefono ?? '',
                $estado
            )) {
                http_response_code(200);
                echo json_encode(["message" => "Cliente actualizado."]);
            } else {
                http_response_code(503);
                echo json_encode(["message" => "No se pudo actualizar el cliente."]);
            }
        } else {
            http_response_code(400);
            echo json_encode(["message" => "Datos incompletos."]);
        }
        break;
    case 'DELETE':
        $cliente_id = !empty($_GET['id']) ? intval($_GET['id']) : null;
        if ($cliente_id) {
            if ($cliente->delete($cliente_id)) {
                http_response_code(200);
                echo json_encode(["message" => "Cliente eliminado."]);
            } else {
                http_response_code(503);
                echo json_encode(["message" => "No se pudo eliminar el cliente."]);
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
