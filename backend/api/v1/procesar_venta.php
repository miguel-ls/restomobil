<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

// Incluir archivos de configuración y base de datos
include_once __DIR__ . '/../../config/database.php';

// Validar que el método de la solicitud sea POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405); // Method Not Allowed
    echo json_encode(["message" => "Método no permitido."]);
    exit;
}

// Obtener el ID del pedido desde los datos POST
$id_pedido = isset($_POST['id_pedido']) ? intval($_POST['id_pedido']) : 0;
if (empty($id_pedido)) {
    http_response_code(400); // Bad Request
    echo json_encode(["message" => "ID de pedido no proporcionado."]);
    exit;
}

$pdo = null;
try {
    // Conexión a la base de datos
    $pdo = new PDO(DB_DSN, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Iniciar transacción
    $pdo->beginTransaction();

    // 1. Obtener datos del pedido y verificar su estado
    $stmt = $pdo->prepare("SELECT * FROM pedidos WHERE id = ?");
    $stmt->execute([$id_pedido]);
    $pedido = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$pedido) {
        throw new Exception("Pedido no encontrado.");
    }

    if ($pedido['estado'] !== 'completado') {
        throw new Exception("El pedido debe estar en estado 'completado' para poder ser pagado. Estado actual: " . $pedido['estado']);
    }

    // Asumimos que el id_serie_documento viene del formulario del pedido.
    // Si no es así, habría que añadir lógica para seleccionarlo.
    // Por ahora, lo tomamos del pedido, asumiendo que se guardó allí.
    // El requerimiento dice que el combo de serie se añade en la VENTA.
    // Esto implica que el usuario lo selecciona en el momento del pago.
    // Lo recibiremos por POST.
    $id_serie_documento = isset($_POST['id_serie_documento']) ? intval($_POST['id_serie_documento']) : 0;
    if (empty($id_serie_documento)) {
        throw new Exception("Debe seleccionar una serie para el documento de venta.");
    }

    $id_tipo_documento_venta = isset($_POST['id_tipo_documento_venta']) ? intval($_POST['id_tipo_documento_venta']) : 0;
    if (empty($id_tipo_documento_venta)) {
        throw new Exception("Debe seleccionar un tipo de documento para la venta.");
    }

    // 2. Obtener el siguiente número de documento para la serie seleccionada
    $stmt = $pdo->prepare("SELECT IFNULL(MAX(CAST(numero_documento AS UNSIGNED)), 0) + 1 AS next_num FROM ventas WHERE id_serie_documento = ?");
    $stmt->execute([$id_serie_documento]);
    $next_num_row = $stmt->fetch(PDO::FETCH_ASSOC);
    $numero_documento = $next_num_row['next_num'];

    // 3. Crear la cabecera de la venta
    $stmt = $pdo->prepare("
        INSERT INTO ventas (id_pedido, id_cliente, id_usuario_cajero, id_tipo_documento_venta, id_serie_documento, numero_documento, total, fecha_emision)
        VALUES (:id_pedido, :id_cliente, :id_usuario_cajero, :id_tipo_documento_venta, :id_serie_documento, :numero_documento, :total, NOW())
    ");

    session_start();
    $id_usuario_cajero = $_SESSION['user_id'] ?? 0; // Asumimos que el ID del usuario logueado está en la sesión

    $stmt->execute([
        ':id_pedido' => $id_pedido,
        ':id_cliente' => $pedido['id_cliente'],
        ':id_usuario_cajero' => $id_usuario_cajero,
        ':id_tipo_documento_venta' => $id_tipo_documento_venta,
        ':id_serie_documento' => $id_serie_documento,
        ':numero_documento' => str_pad($numero_documento, 8, '0', STR_PAD_LEFT), // Formatear a 8 dígitos
        ':total' => $pedido['total']
    ]);
    $id_venta = $pdo->lastInsertId();

    // 4. Obtener el detalle del pedido
    $stmt = $pdo->prepare("SELECT * FROM detalle_pedidos WHERE id_pedido = ?");
    $stmt->execute([$id_pedido]);
    $detalle_pedido = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // 5. Insertar el detalle de la venta
    $stmt_detalle = $pdo->prepare("
        INSERT INTO venta_detalle (id_venta, id_producto, cantidad, precio_unitario)
        VALUES (:id_venta, :id_producto, :cantidad, :precio_unitario)
    ");
    foreach ($detalle_pedido as $item) {
        $stmt_detalle->execute([
            ':id_venta' => $id_venta,
            ':id_producto' => $item['id_producto'],
            ':cantidad' => $item['cantidad'],
            ':precio_unitario' => $item['precio_unitario']
        ]);
    }

    // 6. Actualizar el estado del pedido a 'pagado'
    $stmt = $pdo->prepare("UPDATE pedidos SET estado = 'pagado' WHERE id = ?");
    $stmt->execute([$id_pedido]);

    // Si todo fue bien, confirmar la transacción
    $pdo->commit();

    http_response_code(201); // Created
    echo json_encode([
        "message" => "Venta generada con éxito.",
        "id_venta" => $id_venta,
        "numero_documento" => str_pad($numero_documento, 8, '0', STR_PAD_LEFT)
    ]);

} catch (Exception $e) {
    // Si algo falla, revertir la transacción
    if ($pdo && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    http_response_code(500); // Internal Server Error
    echo json_encode(["message" => "Error al generar la venta: " . $e->getMessage()]);
} finally {
    // Cerrar la conexión
    $pdo = null;
}
?>
