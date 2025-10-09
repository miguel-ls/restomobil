<?php

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

include_once __DIR__ . '/../../config/database.php';
include_once __DIR__ . '/../../models/Venta.php';
include_once __DIR__ . '/../../models/AperturaCierre.php';
include_once __DIR__ . '/../../models/Movimiento.php'; // Incluir el nuevo modelo

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(["message" => "Método no permitido."]);
    exit;
}

$data = json_decode(file_get_contents("php://input"));

if (
    empty($data->id_pedido) ||
    empty($data->id_tipo_documento_venta) ||
    empty($data->id_serie_documento)
) {
    http_response_code(400);
    echo json_encode(["message" => "Datos incompletos. Se requiere id_pedido, id_tipo_documento_venta y id_serie_documento."]);
    exit;
}

$db_connection = null;
try {
    $db_connection = new PDO(DB_DSN, DB_USER, DB_PASS);
    $db_connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    http_response_code(503); // Service Unavailable
    echo json_encode(["message" => "No se puede conectar a la base de datos: " . $e->getMessage()]);
    exit();
}

// Instanciar modelos
$venta = new Venta($db_connection);
$movimiento = new Movimiento(); // Usa el Singleton de Database
$apertura_cierre = new AperturaCierre();

session_start();
$id_usuario_cajero = $_SESSION['user_id'] ?? 0;
if (empty($id_usuario_cajero)) {
    http_response_code(401); // Unauthorized
    echo json_encode(["message" => "No autorizado. Inicie sesión como cajero."]);
    exit;
}

try {
    // 1. Verificar si ya existe una venta para este pedido
    if ($venta->ventaExistePorPedido($data->id_pedido)) {
        http_response_code(409); // Conflict
        echo json_encode(["message" => "Este pedido ya tiene una venta generada. No se puede procesar nuevamente."]);
        exit;
    }

    // 2. Verificar si ya existe un movimiento de salida para este pedido
    if ($movimiento->verificarMovimientoPorPedido($data->id_pedido)) {
        http_response_code(409); // Conflict
        echo json_encode(["message" => "Este pedido ya tiene un movimiento de salida. No se puede procesar nuevamente."]);
        exit;
    }

    // 3. Validación de apertura de caja
    $fecha_actual = date('Y-m-d');
    if (!$apertura_cierre->verificarAperturaActiva($fecha_actual)) {
        http_response_code(403); // Forbidden
        echo json_encode(["message" => "Operación no permitida. No hay una caja abierta para la fecha actual."]);
        exit;
    }

    // Iniciar transacción
    $db_connection->beginTransaction();

    // 4. Obtener el id_cliente desde el pedido original en la base de datos.
    $stmt = $db_connection->prepare("SELECT id_cliente FROM pedidos WHERE id = :id_pedido");
    $stmt->bindParam(':id_pedido', $data->id_pedido, PDO::PARAM_INT);
    $stmt->execute();
    $pedido = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$pedido) {
        throw new Exception("Pedido no encontrado para obtener el cliente.");
    }
    $id_cliente = $pedido['id_cliente'] ?? null;

    // 5. Crear la venta
    $result_venta = $venta->crearVentaDesdePedido(
        $data->id_pedido,
        $id_usuario_cajero,
        $data->id_tipo_documento_venta,
        $data->id_serie_documento,
        $id_cliente
    );

    if (!$result_venta || !isset($result_venta['id_venta'])) {
        throw new Exception($result_venta['error'] ?? "No se pudo crear la venta.");
    }

    // 6. Crear el movimiento de salida
    $result_movimiento = $movimiento->crearMovimientoSalidaPorVenta($data->id_pedido);

    if (!$result_movimiento || !isset($result_movimiento['id_movimiento'])) {
        throw new Exception("No se pudo crear el movimiento de salida.");
    }

    // Si todo fue bien, confirmar la transacción
    $db_connection->commit();

    http_response_code(201); // Created
    echo json_encode([
        "message" => "Venta y movimiento de salida generados con éxito.",
        "id_venta" => $result_venta['id_venta'],
        "numero_documento" => $result_venta['numero_documento'],
        "id_movimiento" => $result_movimiento['id_movimiento']
    ]);

} catch (Exception $e) {
    // Si algo falló, revertir la transacción
    if ($db_connection->inTransaction()) {
        $db_connection->rollBack();
    }
    http_response_code(500); // Internal Server Error
    echo json_encode(["message" => "Error al procesar la operación: " . $e->getMessage()]);
}
?>