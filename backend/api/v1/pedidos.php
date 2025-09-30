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
        if (!empty($data->id_mesa) && !empty($data->id_usuario_mozo) && !empty($data->items) && is_array($data->items) && !empty($data->estado)) {
            $id_cliente = !empty($data->id_cliente) ? $data->id_cliente : null;
            $id_tipo_documento_venta = !empty($data->id_tipo_documento_venta) ? $data->id_tipo_documento_venta : null;
            $id_serie_documento = !empty($data->id_serie_documento) ? $data->id_serie_documento : null;
            $numero_documento = !empty($data->numero_documento) ? $data->numero_documento : null;
            $stmt = $order->create($data->id_mesa, $data->id_usuario_mozo, $data->items, $data->estado, $id_cliente, $id_tipo_documento_venta, $id_serie_documento, $numero_documento);
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
            $id_cliente = !empty($data->id_cliente) ? $data->id_cliente : null;
            $id_tipo_documento_venta = !empty($data->id_tipo_documento_venta) ? $data->id_tipo_documento_venta : null;
            $id_serie_documento = !empty($data->id_serie_documento) ? $data->id_serie_documento : null;
            $numero_documento = !empty($data->numero_documento) ? $data->numero_documento : null;
            if ($order->update($order_id, $data->id_mesa, $data->id_usuario_mozo, $data->estado, $data->items, $id_cliente, $id_tipo_documento_venta, $id_serie_documento, $numero_documento)) {
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
    if (!empty($_GET["estado"])) {
        $status = htmlspecialchars(strip_tags($_GET["estado"]));
        $stmt = $order->readByStatus($status);
    } else {
        $stmt = $order->readAll();
    }

    $all_records = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $filtered_records = $all_records;

    // Filtrado por fecha en PHP
    if (!empty($_GET['start_date']) && !empty($_GET['end_date'])) {
        $start_date = new DateTime($_GET['start_date']);
        $end_date = new DateTime($_GET['end_date']);
        // La hora se establece al final del día para incluir todos los pedidos de la fecha de fin
        $end_date->setTime(23, 59, 59);

        $filtered_records = array_filter($all_records, function($record) use ($start_date, $end_date) {
            $record_date = new DateTime($record['fecha_creacion']);
            return $record_date >= $start_date && $record_date <= $end_date;
        });
    }

    if (count($filtered_records) > 0) {
        http_response_code(200);
        // Re-indexar el array para asegurar que sea un array JSON y no un objeto
        echo json_encode(["records" => array_values($filtered_records)]);
    } else {
        http_response_code(200);
        echo json_encode(["records" => []]);
    }
}
?>
