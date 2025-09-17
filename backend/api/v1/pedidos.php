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
            // Obtener un solo pedido con sus detalles
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
            // Obtener todos los pedidos (la lista principal)
            handleGetAllOrders($order);
        }
        break;

    case 'POST':
        $data = json_decode(file_get_contents("php://input"));

        // Validar que los datos necesarios están presentes
        if (!empty($data->id_mesa) && !empty($data->id_usuario_mozo) && !empty($data->items) && is_array($data->items)) {
            $stmt = $order->create($data->id_mesa, $data->id_usuario_mozo, $data->items);
            if ($stmt) {
                $new_order = $stmt->fetch(PDO::FETCH_ASSOC);
                http_response_code(201); // Created
                echo json_encode(["message" => "Pedido creado exitosamente.", "id" => $new_order['id']]);
            } else {
                http_response_code(503); // Service Unavailable
                echo json_encode(["message" => "No se pudo crear el pedido."]);
            }
        } else {
            http_response_code(400); // Bad Request
            echo json_encode(["message" => "Datos incompletos para crear el pedido."]);
        }
        break;

    case 'PUT':
        $data = json_decode(file_get_contents("php://input"));
        $order_id = !empty($_GET['id']) ? intval($_GET['id']) : null;

        if (!$order_id) {
            http_response_code(400);
            echo json_encode(["message" => "ID de pedido no proporcionado."]);
            break;
        }

        // Si se envían items, es una actualización completa del pedido
        if (!empty($data->items) && is_array($data->items)) {
            if (!empty($data->id_mesa) && !empty($data->id_usuario_mozo)) {
                if ($order->update($order_id, $data->id_mesa, $data->id_usuario_mozo, $data->items)) {
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
        }
        // Si solo se envía el estado, es solo una actualización de estado
        elseif (!empty($data->estado)) {
            if ($order->updateStatus($order_id, $data->estado)) {
                http_response_code(200);
                echo json_encode(["message" => "Estado del pedido actualizado."]);
            } else {
                http_response_code(503);
                echo json_encode(["message" => "No se pudo actualizar el estado del pedido."]);
            }
        }
        // Si no, los datos son inválidos
        else {
            http_response_code(400);
            echo json_encode(["message" => "Datos incompletos o inválidos para la actualización."]);
        }
        break;

    // El caso DELETE se implementará en fases futuras
    case 'DELETE':
    default:
        http_response_code(405); // Method Not Allowed
        echo json_encode(["message" => "Método no permitido."]);
        break;
}

function handleGetAllOrders($order) {
    $stmt = $order->readAll();
    $num = $stmt->rowCount();

    if ($num > 0) {
        $orders_arr = ["records" => []];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            // Convertir tipos de datos para JSON
            $row['id'] = intval($row['id']);
            $row['id_mesa'] = intval($row['id_mesa']);
            $row['id_usuario_mozo'] = intval($row['id_usuario_mozo']);
            $row['total'] = floatval($row['total']);

            array_push($orders_arr["records"], $row);
        }
        http_response_code(200);
        echo json_encode($orders_arr);
    } else {
        http_response_code(200); // OK, pero no hay registros
        echo json_encode(["records" => []]);
    }
}
?>
