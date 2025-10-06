<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

include_once __DIR__ . '/../../models/Proveedor.php';

$proveedor = new Proveedor();
$request_method = $_SERVER["REQUEST_METHOD"];

switch ($request_method) {
    case 'GET':
        if (!empty($_GET["id"])) {
            $proveedor_id = intval($_GET["id"]);
            $proveedor_data = $proveedor->readOne($proveedor_id);
            if ($proveedor_data) {
                http_response_code(200);
                echo json_encode($proveedor_data, JSON_INVALID_UTF8_SUBSTITUTE);
            } else {
                http_response_code(404);
                echo json_encode(["message" => "Proveedor no encontrado."], JSON_INVALID_UTF8_SUBSTITUTE);
            }
        } else {
            $stmt = $proveedor->readAll();
            $num = $stmt->rowCount();
            if ($num > 0) {
                $proveedores_arr = ["records" => []];
                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    array_push($proveedores_arr["records"], $row);
                }
                http_response_code(200);
                echo json_encode($proveedores_arr, JSON_INVALID_UTF8_SUBSTITUTE);
            } else {
                http_response_code(200);
                echo json_encode(["records" => []], JSON_INVALID_UTF8_SUBSTITUTE);
            }
        }
        break;
    case 'POST':
        $data = json_decode(file_get_contents("php://input"));
        if (empty($data->id_tipo_documento_identidad) || empty($data->numero_documento) || empty($data->nombres_apellidos)) {
            http_response_code(400);
            echo json_encode(["message" => "Datos incompletos."], JSON_INVALID_UTF8_SUBSTITUTE);
            break;
        }

        try {
            $stmt = $proveedor->create(
                $data->id_tipo_documento_identidad,
                $data->numero_documento,
                $data->nombres_apellidos,
                $data->direccion ?? '',
                $data->codigo_ubigeo ?? '',
                $data->email ?? '',
                $data->telefono ?? ''
            );

            if ($stmt) {
                $new_proveedor = $stmt->fetch(PDO::FETCH_ASSOC);
                http_response_code(201);
                echo json_encode(["message" => "Proveedor creado.", "id" => $new_proveedor['id']], JSON_INVALID_UTF8_SUBSTITUTE);
            } else {
                http_response_code(503);
                echo json_encode(["message" => "No se pudo crear el proveedor."], JSON_INVALID_UTF8_SUBSTITUTE);
            }
        } catch (PDOException $e) {
            if ($e->getCode() == '23000') { // Integrity constraint violation
                http_response_code(409); // Conflict
                echo json_encode(["message" => "Ya existe un proveedor con el tipo y número de documento especificado."], JSON_INVALID_UTF8_SUBSTITUTE);
            } else {
                http_response_code(503);
                echo json_encode(["message" => "Error en la base de datos.", "error" => $e->getMessage()], JSON_INVALID_UTF8_SUBSTITUTE);
            }
        }
        break;
    case 'PUT':
        $data = json_decode(file_get_contents("php://input"));
        $proveedor_id = !empty($_GET['id']) ? intval($_GET['id']) : null;

        if ($proveedor_id && !empty($data->id_tipo_documento_identidad) && !empty($data->numero_documento) && !empty($data->nombres_apellidos)) {
            $estado = $data->estado ?? 'Activado';
            if ($proveedor->update(
                $proveedor_id,
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
                echo json_encode(["message" => "Proveedor actualizado."], JSON_INVALID_UTF8_SUBSTITUTE);
            } else {
                http_response_code(503);
                echo json_encode(["message" => "No se pudo actualizar el proveedor."], JSON_INVALID_UTF8_SUBSTITUTE);
            }
        } else {
            http_response_code(400);
            echo json_encode(["message" => "Datos incompletos."], JSON_INVALID_UTF8_SUBSTITUTE);
        }
        break;
    case 'DELETE':
        $proveedor_id = !empty($_GET['id']) ? intval($_GET['id']) : null;
        if ($proveedor_id) {
            if ($proveedor->delete($proveedor_id)) {
                http_response_code(200);
                echo json_encode(["message" => "Proveedor eliminado (desactivado)."], JSON_INVALID_UTF8_SUBSTITUTE);
            } else {
                http_response_code(503);
                echo json_encode(["message" => "No se pudo eliminar el proveedor."], JSON_INVALID_UTF8_SUBSTITUTE);
            }
        } else {
            http_response_code(400);
            echo json_encode(["message" => "ID no proporcionado."], JSON_INVALID_UTF8_SUBSTITUTE);
        }
        break;
    default:
        http_response_code(405);
        echo json_encode(["message" => "Método no permitido."], JSON_INVALID_UTF8_SUBSTITUTE);
        break;
}
?>