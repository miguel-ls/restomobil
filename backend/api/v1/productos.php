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
            // Obtener todos los productos con filtros opcionales
            $filters = [];
            if (!empty($_GET['id'])) $filters['id'] = $_GET['id'];
            if (!empty($_GET['nombre'])) $filters['nombre'] = $_GET['nombre'];
            if (!empty($_GET['descripcion'])) $filters['descripcion'] = $_GET['descripcion'];
            if (!empty($_GET['precio'])) $filters['precio'] = $_GET['precio'];
            if (!empty($_GET['categoria_nombre'])) $filters['categoria_nombre'] = $_GET['categoria_nombre'];
            if (!empty($_GET['estado'])) $filters['estado'] = $_GET['estado'];

            handleGetAllProducts($product, $filters);
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

function handleGetAllProducts($product, $filters = []) {
    $page = isset($_GET['page']) ? intval($_GET['page']) : 1;
    $page_size = 12; // 12 filas por página

    // Primero, obtenemos los registros de la página actual
    $stmt = $product->readAll($filters, $page, $page_size);
    $records = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $stmt->closeCursor(); // Importante: cerrar el cursor antes de la siguiente llamada

    // Luego, obtenemos el conteo total de registros para la paginación
    $total_records = $product->countAll($filters);
    $total_pages = ceil($total_records / $page_size);

    $num = count($records);

    if ($num > 0) {
        // Asegurarse de que la descripción se decodifique correctamente
        $decoded_records = array_map(function($row) {
            $row['descripcion'] = html_entity_decode($row['descripcion']);
            return $row;
        }, $records);

        $products_arr = [
            "records" => $decoded_records,
            "pagination" => [
                "page" => $page,
                "total_pages" => $total_pages,
                "total_records" => $total_records
            ]
        ];

        http_response_code(200);
        echo json_encode($products_arr);
    } else {
        http_response_code(404);
        echo json_encode(["message" => "No se encontraron productos."]);
    }
}
?>
