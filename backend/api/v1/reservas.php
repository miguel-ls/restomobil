<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

include_once __DIR__ . '/../../models/Reservation.php';

$reservation = new Reservation();
$request_method = $_SERVER["REQUEST_METHOD"];

switch ($request_method) {
    case 'GET':
        if (!empty($_GET["id"])) {
            $reservation_id = intval($_GET["id"]);
            $reservation_data = $reservation->readOne($reservation_id);
            if ($reservation_data) {
                http_response_code(200);
                echo json_encode($reservation_data);
            } else {
                http_response_code(404);
                echo json_encode(["message" => "Reserva no encontrada."]);
            }
        } else {
            handleGetAllReservations($reservation);
        }
        break;

    case 'POST':
        $data = json_decode(file_get_contents("php://input"));
        if (!empty($data->id_mesa) && !empty($data->nombre_cliente) && !empty($data->fecha_reserva)) {
            $stmt = $reservation->create($data->id_mesa, $data->nombre_cliente, $data->telefono_cliente, $data->email_cliente, $data->fecha_reserva, $data->cantidad_personas, $data->observaciones);
            if ($stmt) {
                $new_reservation = $stmt->fetch(PDO::FETCH_ASSOC);
                http_response_code(201);
                echo json_encode(["message" => "Reserva creada.", "id" => $new_reservation['id']]);
            } else {
                http_response_code(503);
                echo json_encode(["message" => "No se pudo crear la reserva."]);
            }
        } else {
            http_response_code(400);
            echo json_encode(["message" => "Datos incompletos para crear la reserva."]);
        }
        break;

    case 'PUT':
        $data = json_decode(file_get_contents("php://input"));
        $reservation_id = !empty($_GET['id']) ? intval($_GET['id']) : null;
        if ($reservation_id && !empty($data->id_mesa) && !empty($data->nombre_cliente) && !empty($data->fecha_reserva)) {
             if ($reservation->update($reservation_id, $data->id_mesa, $data->nombre_cliente, $data->telefono_cliente, $data->email_cliente, $data->fecha_reserva, $data->cantidad_personas, $data->estado, $data->observaciones)) {
                http_response_code(200);
                echo json_encode(["message" => "Reserva actualizada."]);
            } else {
                http_response_code(503);
                echo json_encode(["message" => "No se pudo actualizar la reserva."]);
            }
        } else {
            http_response_code(400);
            echo json_encode(["message" => "Datos incompletos para actualizar la reserva."]);
        }
        break;

    case 'DELETE':
        $reservation_id = !empty($_GET['id']) ? intval($_GET['id']) : null;
        if ($reservation_id) {
            if ($reservation->cancel($reservation_id)) {
                http_response_code(200);
                echo json_encode(["message" => "Reserva cancelada."]);
            } else {
                http_response_code(503);
                echo json_encode(["message" => "No se pudo cancelar la reserva."]);
            }
        } else {
            http_response_code(400);
            echo json_encode(["message" => "ID de reserva no proporcionado."]);
        }
        break;

    default:
        http_response_code(405);
        echo json_encode(["message" => "Método no permitido."]);
        break;
}

function handleGetAllReservations($reservation) {
    $stmt = $reservation->readAll();
    $num = $stmt->rowCount();
    if ($num > 0) {
        $reservations_arr = ["records" => []];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            array_push($reservations_arr["records"], $row);
        }
        http_response_code(200);
        echo json_encode($reservations_arr);
    } else {
        http_response_code(200);
        echo json_encode(["records" => []]);
    }
}
?>
