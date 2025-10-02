<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

include_once __DIR__ . '/../../config/database.php';
include_once __DIR__ . '/../../models/Venta.php';

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

try {
    $database = new Database();
    $db = $database->getConnection();
} catch (PDOException $e) {
    http_response_code(503); // Service Unavailable
    echo json_encode(["message" => "No se puede conectar a la base de datos: " . $e->getMessage()]);
    exit();
}
$venta = new Venta($db);

session_start();
$id_usuario_cajero = $_SESSION['user_id'] ?? 0; // Asumimos que el ID del usuario está en la sesión
if (empty($id_usuario_cajero)) {
    http_response_code(401); // Unauthorized
    echo json_encode(["message" => "No autorizado. Inicie sesión como cajero."]);
    exit;
}

try {
    // CORRECCIÓN: Obtener el id_cliente desde el pedido original en la base de datos.
    $stmt = $db->prepare("SELECT id_cliente FROM pedidos WHERE id = :id_pedido");
    $stmt->bindParam(':id_pedido', $data->id_pedido, PDO::PARAM_INT);
    $stmt->execute();
    $pedido = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$pedido) {
        throw new Exception("Pedido no encontrado para obtener el cliente.");
    }
    $id_cliente = $pedido['id_cliente'] ?? null;

    // Llamar al procedimiento almacenado para crear la venta
    $result = $venta->crearVentaDesdePedido(
        $data->id_pedido,
        $id_usuario_cajero,
        $data->id_tipo_documento_venta,
        $data->id_serie_documento,
        $id_cliente // Usar el id_cliente obtenido de la BD
    );

    if ($result && isset($result['id_venta'])) {
        http_response_code(201); // Created
        echo json_encode([
            "message" => "Venta generada con éxito.",
            "id_venta" => $result['id_venta'],
            "numero_documento" => $result['numero_documento']
        ]);
    } else {
        throw new Exception($result['error'] ?? "No se pudo crear la venta.");
    }
} catch (Exception $e) {
    http_response_code(500); // Internal Server Error
    echo json_encode(["message" => "Error al generar la venta: " . $e->getMessage()]);
}
?>