<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

include_once __DIR__ . '/../../config/app_config.php';
include_once __DIR__ . '/../../core/Database.php';
include_once __DIR__ . '/../../models/Reservation.php';

$reservation = new Reservation();
$request_method = $_SERVER["REQUEST_METHOD"];

switch ($request_method) {
    case 'GET':
        if (!empty($_GET["id"])) {
            $reservation->id = intval($_GET["id"]);
            $stmt = $reservation->readOne();
            $num = $stmt->rowCount();
            if ($num > 0) {
                $row = $stmt->fetch(PDO::FETCH_ASSOC);
                http_response_code(200);
                echo json_encode($row);
            } else {
                http_response_code(404);
                echo json_encode(["message" => "Reserva no encontrada."]);
            }
        } else {
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
        break;

    case 'POST':
        $data = json_decode(file_get_contents("php://input"));
        if (
            !empty($data->id_mesa) &&
            !empty($data->nombre_cliente) &&
            !empty($data->fecha_reserva) &&
            !empty($data->cantidad_personas)
        ) {
            $reservation->id_mesa = $data->id_mesa;
            $reservation->nombre_cliente = $data->nombre_cliente;
            $reservation->telefono_cliente = $data->telefono_cliente ?? '';
            $reservation->email_cliente = $data->email_cliente ?? '';
            $reservation->fecha_reserva = $data->fecha_reserva;
            $reservation->cantidad_personas = $data->cantidad_personas;
            $reservation->estado = $data->estado ?? 'confirmada';
            $reservation->observaciones = $data->observaciones ?? '';

            if ($reservation->create()) {
                http_response_code(201);
                echo json_encode(["message" => "Reserva creada."]);
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
        if (
            !empty($data->id) &&
            !empty($data->id_mesa) &&
            !empty($data->nombre_cliente) &&
            !empty($data->fecha_reserva) &&
            !empty($data->cantidad_personas)
        ) {
            $reservation->id = $data->id;
            $reservation->id_mesa = $data->id_mesa;
            $reservation->nombre_cliente = $data->nombre_cliente;
            $reservation->telefono_cliente = $data->telefono_cliente ?? '';
            $reservation->email_cliente = $data->email_cliente ?? '';
            $reservation->fecha_reserva = $data->fecha_reserva;
            $reservation->cantidad_personas = $data->cantidad_personas;
            $reservation->estado = $data->estado ?? 'confirmada';
            $reservation->observaciones = $data->observaciones ?? '';

            if ($reservation->update()) {
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
        if (!empty($_GET["id"])) {
            $reservation->id = intval($_GET["id"]);
            if ($reservation->delete()) {
                http_response_code(200);
                echo json_encode(["message" => "Reserva eliminada."]);
            } else {
                http_response_code(503);
                echo json_encode(["message" => "No se pudo eliminar la reserva."]);
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
