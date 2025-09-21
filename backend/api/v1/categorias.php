<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

include_once __DIR__ . '/../../models/Category.php';

$category = new Category();
$request_method = $_SERVER["REQUEST_METHOD"];

switch ($request_method) {
    case 'GET':
        if (!empty($_GET["id"])) {
            $category_id = intval($_GET["id"]);
            $category_data = $category->readOne($category_id);
            if ($category_data) {
                http_response_code(200);
                echo json_encode($category_data);
            } else {
                http_response_code(404);
                echo json_encode(["message" => "Categoría no encontrada."]);
            }
        } else {
            $stmt = $category->readAll();
            $num = $stmt->rowCount();
            if ($num > 0) {
                $categories_arr = ["records" => []];
                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    $row['estado'] = (bool)$row['estado'];
                    array_push($categories_arr["records"], $row);
                }
                http_response_code(200);
                echo json_encode($categories_arr);
            } else {
                http_response_code(200);
                echo json_encode(["records" => []]);
            }
        }
        break;
    case 'POST':
        $data = json_decode(file_get_contents("php://input"));
        if (!empty($data->nombre) && !empty($data->tipo_categoria)) {
            $stmt = $category->create($data->nombre, $data->descripcion ?? '', $data->tipo_categoria);
            if ($stmt) {
                $new_category = $stmt->fetch(PDO::FETCH_ASSOC);
                http_response_code(201);
                echo json_encode(["message" => "Categoría creada.", "id" => $new_category['id']]);
            } else {
                http_response_code(503);
                echo json_encode(["message" => "No se pudo crear la categoría."]);
            }
        } else {
            http_response_code(400);
            echo json_encode(["message" => "Datos incompletos."]);
        }
        break;
    case 'PUT':
        $data = json_decode(file_get_contents("php://input"));
        $category_id = !empty($_GET['id']) ? intval($_GET['id']) : null;
        if ($category_id && !empty($data->nombre) && !empty($data->tipo_categoria)) {
            $estado = isset($data->estado) ? (bool)$data->estado : true;
            if ($category->update($category_id, $data->nombre, $data->descripcion ?? '', $data->tipo_categoria, $estado)) {
                http_response_code(200);
                echo json_encode(["message" => "Categoría actualizada."]);
            } else {
                http_response_code(503);
                echo json_encode(["message" => "No se pudo actualizar la categoría."]);
            }
        } else {
            http_response_code(400);
            echo json_encode(["message" => "Datos incompletos."]);
        }
        break;
    case 'DELETE':
        $category_id = !empty($_GET['id']) ? intval($_GET['id']) : null;
        if ($category_id) {
            if ($category->delete($category_id)) {
                http_response_code(200);
                echo json_encode(["message" => "Categoría eliminada."]);
            } else {
                http_response_code(503);
                echo json_encode(["message" => "No se pudo eliminar la categoría."]);
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
