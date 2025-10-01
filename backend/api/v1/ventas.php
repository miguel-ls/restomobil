<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, PUT, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

// Manejar las solicitudes pre-vuelo para CORS
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

include_once __DIR__ . '/../../config/database.php';
include_once __DIR__ . '/../../models/Venta.php';

try {
    $pdo = new PDO(DB_DSN, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    http_response_code(503);
    echo json_encode(["message" => "No se puede conectar a la base de datos."]);
    exit();
}

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        handle_get($pdo);
        break;
    case 'PUT':
        handle_put($pdo);
        break;
    default:
        header('HTTP/1.1 405 Method Not Allowed');
        echo json_encode(["message" => "Método no permitido."]);
        break;
}

function handle_get($pdo) {
    if (!empty($_GET['id'])) {
        // --- Obtener una sola venta con su detalle ---
        $id_venta = intval($_GET['id']);
        $query = "
            SELECT
                v.id, v.fecha_emision, v.id_cliente, v.id_tipo_documento_venta,
                v.id_serie_documento, v.numero_documento, v.total, v.estado,
                c.nombres_apellidos AS nombre_cliente,
                c.numero_documento AS ruc_cliente,
                c.id_tipo_documento_identidad AS id_tipo_documento_identidad_cliente,
                c.direccion AS direccion_cliente,
                tdv.nombre AS tipo_documento,
                sd.serie
            FROM ventas v
            LEFT JOIN clientes c ON v.id_cliente = c.id
            LEFT JOIN tipo_documento_venta tdv ON v.id_tipo_documento_venta = tdv.id
            LEFT JOIN series_documentos sd ON v.id_serie_documento = sd.id
            WHERE v.id = ?
        ";
        $stmt = $pdo->prepare($query);
        $stmt->execute([$id_venta]);
        $venta = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($venta) {
            $query_items = "
                SELECT vd.id_producto, p.nombre AS nombre_producto, vd.cantidad, vd.precio_unitario, vd.subtotal
                FROM venta_detalle vd
                JOIN productos p ON vd.id_producto = p.id
                WHERE vd.id_venta = ?
            ";
            $stmt_items = $pdo->prepare($query_items);
            $stmt_items->execute([$id_venta]);
            $venta['items'] = $stmt_items->fetchAll(PDO::FETCH_ASSOC);

            http_response_code(200);
            echo json_encode($venta);
        } else {
            http_response_code(404);
            echo json_encode(["message" => "Venta no encontrada."]);
        }
    } else {
        // --- Lógica de Paginación y Filtros para la lista de ventas ---
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
        $offset = ($page - 1) * $limit;

        $base_query = " FROM ventas v LEFT JOIN clientes c ON v.id_cliente = c.id LEFT JOIN tipo_documento_venta tdv ON v.id_tipo_documento_venta = tdv.id LEFT JOIN series_documentos sd ON v.id_serie_documento = sd.id ";
        $where_clauses = [];
        $params = [];

        if (!empty($_GET['fecha_inicio'])) { $where_clauses[] = "v.fecha_emision >= :fecha_inicio"; $params[':fecha_inicio'] = $_GET['fecha_inicio']; }
        if (!empty($_GET['fecha_fin'])) { $fecha_fin = date('Y-m-d', strtotime($_GET['fecha_fin'] . ' +1 day')); $where_clauses[] = "v.fecha_emision < :fecha_fin"; $params[':fecha_fin'] = $fecha_fin; }
        if (!empty($_GET['search'])) { $where_clauses[] = "(c.nombres_apellidos LIKE :search OR v.numero_documento LIKE :search OR sd.serie LIKE :search)"; $params[':search'] = '%' . $_GET['search'] . '%'; }
        if (!empty($_GET['estado']) && $_GET['estado'] !== 'Todos') { $where_clauses[] = "v.estado = :estado"; $params[':estado'] = $_GET['estado']; }
        if (!empty($_GET['id_tipo_documento'])) { $where_clauses[] = "v.id_tipo_documento_venta = :id_tipo_documento"; $params[':id_tipo_documento'] = $_GET['id_tipo_documento']; }

        $where_sql = !empty($where_clauses) ? " WHERE " . implode(' AND ', $where_clauses) : "";

        $count_query = "SELECT COUNT(v.id) as total " . $base_query . $where_sql;
        $stmt_count = $pdo->prepare($count_query);
        $stmt_count->execute($params);
        $total_records = (int)$stmt_count->fetch(PDO::FETCH_ASSOC)['total'];
        $total_pages = ceil($total_records / $limit);

        $query = " SELECT v.id, v.fecha_emision, c.nombres_apellidos AS nombre_cliente, tdv.nombre AS tipo_documento, sd.serie, v.numero_documento, v.total, v.estado " . $base_query . $where_sql . " ORDER BY v.fecha_emision DESC LIMIT :limit OFFSET :offset ";
        $stmt = $pdo->prepare($query);
        foreach ($params as $key => &$val) { $stmt->bindParam($key, $val); }
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        $ventas = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $response = [ "records" => $ventas, "pagination" => [ "total_records" => $total_records, "total_pages" => $total_pages, "current_page" => $page, "limit" => $limit ] ];

        if ($total_records === 0) {
            $response["message"] = "No se encontraron ventas con los filtros aplicados.";
        }

        http_response_code(200);
        echo json_encode($response);
    }
}

function handle_put($pdo) {
    $data = json_decode(file_get_contents("php://input"));

    if (empty($data->id) || empty($data->fecha_emision) || empty($data->nombre_cliente)) {
        http_response_code(400);
        echo json_encode(["success" => false, "message" => "Datos incompletos."]);
        return;
    }

    $venta = new Venta($pdo);
    try {
        $result = $venta->update($data);
        if ($result) {
            http_response_code(200);
            echo json_encode(["success" => true, "message" => "Venta actualizada correctamente."]);
        } else {
            http_response_code(500);
            echo json_encode(["success" => false, "message" => "No se pudo actualizar la venta."]);
        }
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(["success" => false, "message" => "Error: " . $e->getMessage()]);
    }
}
?>