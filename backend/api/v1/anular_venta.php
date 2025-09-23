<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

include_once __DIR__ . '/../../config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(["message" => "Método no permitido."]);
    exit;
}

$data = json_decode(file_get_contents("php://input"));

if (empty($data->id_venta)) {
    http_response_code(400);
    echo json_encode(["message" => "ID de venta no proporcionado."]);
    exit;
}

$id_venta = intval($data->id_venta);

try {
    $pdo = new PDO(DB_DSN, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $stmt = $pdo->prepare("UPDATE ventas SET estado = 'anulada' WHERE id = :id_venta AND estado = 'emitida'");
    $stmt->bindParam(':id_venta', $id_venta, PDO::PARAM_INT);

    $stmt->execute();

    if ($stmt->rowCount() > 0) {
        http_response_code(200);
        echo json_encode(["message" => "Venta anulada con éxito."]);
    } else {
        // Check if the sale exists and was already voided or not in 'emitida' state
        $checkStmt = $pdo->prepare("SELECT estado FROM ventas WHERE id = :id_venta");
        $checkStmt->bindParam(':id_venta', $id_venta, PDO::PARAM_INT);
        $checkStmt->execute();
        $result = $checkStmt->fetch(PDO::FETCH_ASSOC);

        if ($result) {
            http_response_code(409); // Conflict
            echo json_encode(["message" => "La venta no se pudo anular. Puede que ya esté anulada o en un estado no válido. Estado actual: " . $result['estado']]);
        } else {
            http_response_code(404); // Not Found
            echo json_encode(["message" => "Venta no encontrada."]);
        }
    }

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["message" => "Error al anular la venta: " . $e->getMessage()]);
} finally {
    $pdo = null;
}
?>
