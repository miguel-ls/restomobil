<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

include_once __DIR__ . '/../../core/Database.php';
include_once __DIR__ . '/../../models/Reservation.php';

$reservation = new Reservation();

$stmt = $reservation->readAll();
$num = $stmt->rowCount();

if ($num > 0) {
    $reservations_arr = ["records" => []];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        extract($row);
        $reservation_item = [
            "id" => $id,
            "id_mesa" => $id_mesa,
            "numero_mesa" => $numero_mesa,
            "nombre_cliente" => $nombre_cliente,
            "telefono_cliente" => $telefono_cliente,
            "email_cliente" => $email_cliente,
            "fecha_reserva" => $fecha_reserva,
            "cantidad_personas" => $cantidad_personas,
            "estado" => $estado,
            "observaciones" => $observaciones
        ];
        array_push($reservations_arr["records"], $reservation_item);
    }
    http_response_code(200);
    echo json_encode($reservations_arr);
} else {
    http_response_code(404);
    echo json_encode(["message" => "No se encontraron reservas."]);
}
?>
