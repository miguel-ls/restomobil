<?php
// Headers para permitir el acceso desde cualquier origen (CORS) y definir el tipo de contenido.
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

// Incluir archivos de la base de datos y del modelo de producto.
include_once __DIR__ . '/../../config/app_config.php';
include_once __DIR__ . '/../../core/Database.php';
include_once __DIR__ . '/../../models/Product.php';

$product = new Product();
$request_method = $_SERVER["REQUEST_METHOD"];

switch ($request_method) {
    case 'GET':
        if (!empty($_GET["id"])) {
            // Obtener un solo producto
            $product_id = intval($_GET["id"]);
            $product_data = $product->readOne($product_id);
            if ($product_data) {
                http_response_code(200);
                echo json_encode($product_data);
            } else {
                http_response_code(404);
                echo json_encode(array("message" => "Producto no encontrado."));
            }
        } else {
            // Obtener todos los productos con filtro opcional por estado
            $estado = isset($_GET['estado']) ? $_GET['estado'] : null;
            handleGetAllProducts($product, $estado);
        }
        break;

    case 'POST':
        $data = json_decode(file_get_contents("php://input"));
        if (!empty($data->nombre) && !empty($data->precio) && !empty($data->estado)) {
            $stmt = $product->create($data->nombre, $data->descripcion, $data->precio, $data->id_categoria, $data->estado);
            if ($stmt) {
                $new_product = $stmt->fetch(PDO::FETCH_ASSOC);
                http_response_code(201); // Created
                echo json_encode(array("message" => "Producto creado.", "id" => $new_product['id']));
            } else {
                http_response_code(503); // Service Unavailable
                echo json_encode(array("message" => "No se pudo crear el producto."));
            }
        } else {
            http_response_code(400); // Bad Request
            echo json_encode(array("message" => "No se pudo crear el producto. Datos incompletos."));
        }
        break;

    case 'PUT':
        $data = json_decode(file_get_contents("php://input"));
        if (!empty($data->id) && !empty($data->nombre) && !empty($data->precio) && !empty($data->estado)) {
            if ($product->update($data->id, $data->nombre, $data->descripcion, $data->precio, $data->id_categoria, $data->estado)) {
                http_response_code(200);
                echo json_encode(array("message" => "Producto actualizado."));
            } else {
                http_response_code(503);
                echo json_encode(array("message" => "No se pudo actualizar el producto."));
            }
        } else {
            http_response_code(400);
            echo json_encode(array("message" => "No se pudo actualizar el producto. Datos incompletos."));
        }
        break;

    case 'DELETE':
        if (!empty($_GET["id"])) {
            $product_id = intval($_GET["id"]);
            if ($product->delete($product_id)) {
                http_response_code(200);
                echo json_encode(array("message" => "Producto eliminado."));
            } else {
                http_response_code(503);
                echo json_encode(array("message" => "No se pudo eliminar el producto."));
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

function handleGetAllProducts($product, $estado = null) {
    $stmt = $product->readAll($estado);
    $num = $stmt->rowCount();

    if ($num > 0) {
        $products_arr = array();
        $products_arr["records"] = array();

        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            extract($row);
            $product_item = array(
                "id" => $id,
                "nombre" => $nombre,
                "descripcion" => html_entity_decode($descripcion),
                "precio" => $precio,
                "estado" => $estado,
                "categoria_nombre" => $categoria_nombre
            );
            array_push($products_arr["records"], $product_item);
        }

        http_response_code(200);
        echo json_encode($products_arr);
    } else {
        http_response_code(404);
        echo json_encode(array("message" => "No se encontraron productos."));
    }
}
?>
