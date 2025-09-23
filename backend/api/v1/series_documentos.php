<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

include_once __DIR__ . '/../../models/SerieDocumento.php';

$serieDocumento = new SerieDocumento();
$request_method = $_SERVER["REQUEST_METHOD"];

switch ($request_method) {
    case 'GET':
        if (!empty($_GET["id"])) {
            $serie_id = intval($_GET["id"]);
            $serie_data = $serieDocumento->readOne($serie_id);
            if ($serie_data) {
                http_response_code(200);
                echo json_encode($serie_data);
            } else {
                http_response_code(404);
                echo json_encode(["message" => "Serie no encontrada."]);
            }
        } else if (!empty($_GET["id_tipo_documento"])) {
            // Listar series por tipo de documento
            $id_tipo_documento = intval($_GET["id_tipo_documento"]);
            $stmt = $serieDocumento->readByTipoDocumento($id_tipo_documento);
            $num = $stmt->rowCount();
            if ($num > 0) {
                $series_arr = ["records" => []];
                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    array_push($series_arr["records"], $row);
                }
                http_response_code(200);
                echo json_encode($series_arr);
            } else {
                http_response_code(200);
                echo json_encode(["records" => []]);
            }
        } else {
            // Listar todas las series (comportamiento original)
            $stmt = $serieDocumento->readAll();
            $num = $stmt->rowCount();
            if ($num > 0) {
                $series_arr = ["records" => []];
                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    $row['estado'] = (bool)$row['estado'];
                    array_push($series_arr["records"], $row);
                }
                http_response_code(200);
                echo json_encode($series_arr);
            } else {
                http_response_code(200);
                echo json_encode(["records" => []]);
            }
        }
        break;
    case 'POST':
        $data = json_decode(file_get_contents("php://input"));
        if (!empty($data->id_tipo_documento) && !empty($data->serie)) {
            $stmt = $serieDocumento->create($data->id_tipo_documento, $data->serie);
            if ($stmt) {
                $new_serie = $stmt->fetch(PDO::FETCH_ASSOC);
                http_response_code(201);
                echo json_encode(["message" => "Serie creada.", "id" => $new_serie['id']]);
            } else {
                http_response_code(503);
                echo json_encode(["message" => "No se pudo crear la serie."]);
            }
        } else {
            http_response_code(400);
            echo json_encode(["message" => "Datos incompletos."]);
        }
        break;
    case 'PUT':
        $data = json_decode(file_get_contents("php://input"));
        $serie_id = !empty($_GET['id']) ? intval($_GET['id']) : null;
        if ($serie_id && !empty($data->id_tipo_documento) && !empty($data->serie)) {
            $estado = isset($data->estado) ? (bool)$data->estado : true;
            if ($serieDocumento->update($serie_id, $data->id_tipo_documento, $data->serie, $estado)) {
                http_response_code(200);
                echo json_encode(["message" => "Serie actualizada."]);
            } else {
                http_response_code(503);
                echo json_encode(["message" => "No se pudo actualizar la serie."]);
            }
        } else {
            http_response_code(400);
            echo json_encode(["message" => "Datos incompletos."]);
        }
        break;
    case 'DELETE':
        $serie_id = !empty($_GET['id']) ? intval($_GET['id']) : null;
        if ($serie_id) {
            if ($serieDocumento->delete($serie_id)) {
                http_response_code(200);
                echo json_encode(["message" => "Serie eliminada."]);
            } else {
                http_response_code(503);
                echo json_encode(["message" => "No se pudo eliminar la serie."]);
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
