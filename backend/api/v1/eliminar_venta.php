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
$pdo = null;

try {
    $pdo = new PDO(DB_DSN, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Iniciar transacción
    $pdo->beginTransaction();

    // 1. Eliminar los detalles de la venta
    $stmt_detalle = $pdo->prepare("DELETE FROM venta_detalle WHERE id_venta = :id_venta");
    $stmt_detalle->bindParam(':id_venta', $id_venta, PDO::PARAM_INT);
    $stmt_detalle->execute();

    // 2. Eliminar la cabecera de la venta
    $stmt_venta = $pdo->prepare("DELETE FROM ventas WHERE id = :id_venta");
    $stmt_venta->bindParam(':id_venta', $id_venta, PDO::PARAM_INT);
    $stmt_venta->execute();

    // Verificar si la venta fue eliminada
    if ($stmt_venta->rowCount() > 0) {
        // Confirmar la transacción
        $pdo->commit();
        http_response_code(200);
        echo json_encode(["message" => "Venta eliminada con éxito."]);
    } else {
        // Si no se eliminó ninguna fila, la venta no existía
        $pdo->rollBack();
        http_response_code(404);
        echo json_encode(["message" => "Venta no encontrada."]);
    }

} catch (Exception $e) {
    // Si algo falla, revertir la transacción
    if ($pdo && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    http_response_code(500);
    echo json_encode(["message" => "Error al eliminar la venta: " . $e->getMessage()]);
} finally {
    $pdo = null;
}
?>
