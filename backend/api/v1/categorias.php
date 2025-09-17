<?php
// Headers para permitir el acceso desde cualquier origen (CORS) y definir el tipo de contenido.
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

// Incluir archivos de la base de datos y del modelo de categoría.
include_once __DIR__ . '/../../core/Database.php';
include_once __DIR__ . '/../../models/Category.php';

$category = new Category();
$request_method = $_SERVER["REQUEST_METHOD"];

switch ($request_method) {
    case 'GET':
        if (!empty($_GET["id"])) {
            // Obtener una sola categoría
            $category_id = intval($_GET["id"]);
            $category_data = $category->readOne($category_id);
            if ($category_data) {
                http_response_code(200);
                echo json_encode($category_data);
            } else {
                http_response_code(404);
                echo json_encode(array("message" => "Categoría no encontrada."));
            }
        } else {
            // Obtener todas las categorías
            handleGetAllCategories($category);
        }
        break;

    case 'POST':
        $data = json_decode(file_get_contents("php://input"));
        if (!empty($data->nombre)) {
            $stmt = $category->create($data->nombre, $data->descripcion);
            if ($stmt) {
                $new_category = $stmt->fetch(PDO::FETCH_ASSOC);
                http_response_code(201); // Created
                echo json_encode(array("message" => "Categoría creada.", "id" => $new_category['id']));
            } else {
                http_response_code(503); // Service Unavailable
                echo json_encode(array("message" => "No se pudo crear la categoría."));
            }
        } else {
            http_response_code(400); // Bad Request
            echo json_encode(array("message" => "No se pudo crear la categoría. Datos incompletos."));
        }
        break;

    case 'PUT':
        $data = json_decode(file_get_contents("php://input"));
        if (!empty($data->id) && !empty($data->nombre)) {
            if ($category->update($data->id, $data->nombre, $data->descripcion)) {
                http_response_code(200);
                echo json_encode(array("message" => "Categoría actualizada."));
            } else {
                http_response_code(503);
                echo json_encode(array("message" => "No se pudo actualizar la categoría."));
            }
        } else {
            http_response_code(400);
            echo json_encode(array("message" => "No se pudo actualizar la categoría. Datos incompletos."));
        }
        break;

    case 'DELETE':
        if (!empty($_GET["id"])) {
            $category_id = intval($_GET["id"]);
            if ($category->delete($category_id)) {
                http_response_code(200);
                echo json_encode(array("message" => "Categoría eliminada."));
            } else {
                http_response_code(503);
                echo json_encode(array("message" => "No se pudo eliminar la categoría."));
            }
        } else {
            http_response_code(400);
            echo json_encode(array("message" => "No se proporcionó un ID para eliminar."));
        }
        break;

    default:
        // Método no válido
        http_response_code(405); // Method Not Allowed
        echo json_encode(array("message" => "Método no permitido."));
        break;
}

function handleGetAllCategories($category) {
    $stmt = $category->readAll();
    $num = $stmt->rowCount();

    if ($num > 0) {
        $categories_arr = array();
        $categories_arr["records"] = array();

        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            extract($row);
            $category_item = array(
                "id" => $id,
                "nombre" => $nombre,
                "descripcion" => html_entity_decode($descripcion)
            );
            array_push($categories_arr["records"], $category_item);
        }

        http_response_code(200);
        echo json_encode($categories_arr);
    } else {
        http_response_code(200); // OK
        echo json_encode(array("records" => []));
    }
}
?>
