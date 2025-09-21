<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

include_once __DIR__ . '/../../models/Order.php';

$order = new Order();
$request_method = $_SERVER["REQUEST_METHOD"];

switch ($request_method) {
    case 'GET':
        if (!empty($_GET["id"])) {
            $order_id = intval($_GET["id"]);
            $order_data = $order->readOne($order_id);
            if ($order_data) {
                http_response_code(200);
                echo json_encode($order_data);
            } else {
                http_response_code(404);
                echo json_encode(["message" => "Pedido no encontrado."]);
            }
        } else {
            handleGetAllOrders($order);
        }
        break;

    case 'POST':
        $data = json_decode(file_get_contents("php://input"));
        if (!empty($data->id_mesa) && !empty($data->id_usuario_mozo) && !empty($data->items) && is_array($data->items)) {
            $stmt = $order->create($data->id_mesa, $data->id_usuario_mozo, $data->items);
            if ($stmt) {
                $new_order = $stmt->fetch(PDO::FETCH_ASSOC);
                http_response_code(201);
                echo json_encode(["message" => "Pedido creado exitosamente.", "id" => $new_order['id']]);
            } else {
                http_response_code(503);
                echo json_encode(["message" => "No se pudo crear el pedido."]);
            }
        } else {
            http_response_code(400);
            echo json_encode(["message" => "Datos incompletos para crear el pedido."]);
        }
        break;

    case 'PUT':
        $data = json_decode(file_get_contents("php://input"));
        $order_id = !empty($_GET['id']) ? intval($_GET['id']) : null;
        if ($order_id && !empty($data->id_mesa) && !empty($data->id_usuario_mozo) && isset($data->estado) && isset($data->items) && is_array($data->items)) {
            if ($order->update($order_id, $data->id_mesa, $data->id_usuario_mozo, $data->estado, $data->items)) {
                http_response_code(200);
                echo json_encode(["message" => "Pedido actualizado exitosamente."]);
            } else {
                http_response_code(503);
                echo json_encode(["message" => "No se pudo actualizar el pedido."]);
            }
        } else {
            http_response_code(400);
            echo json_encode(["message" => "Datos incompletos para actualizar el pedido."]);
        }
        break;

    default:
        http_response_code(405);
        echo json_encode(["message" => "Método no permitido."]);
        break;
}

function handleGetAllOrders($order) {
    // El parámetro 'estado' puede ser una cadena de estados separados por comas (ej. "completado,pagado").
    // El procedimiento almacenado sp_getOrdersByStatus utiliza FIND_IN_SET de MySQL,
    // que está diseñado para buscar una cadena dentro de un conjunto de cadenas separadas por comas.
    // Por lo tanto, no es necesario explotar la cadena en un array en PHP.
    if (!empty($_GET["estado"])) {
        $status = htmlspecialchars(strip_tags($_GET["estado"]));
        $stmt = $order->readByStatus($status);
    } else {
        $stmt = $order->readAll();
    }

    $num = $stmt->rowCount();

    if ($num > 0) {
        $orders_arr = ["records" => []];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            array_push($orders_arr["records"], $row);
        }
        http_response_code(200);
        echo json_encode($orders_arr);
    } else {
        http_response_code(200);
        echo json_encode(["records" => []]);
    }
}
?>
