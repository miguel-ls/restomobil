<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

include_once __DIR__ . '/../../config/database.php';

try {
    $pdo = new PDO(DB_DSN, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

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
        exit;
    }

    // --- Obtener todas las ventas (lista) ---
    $query = "
        SELECT
            v.id,
            v.fecha_emision,
            c.nombres_apellidos AS nombre_cliente,
            tdv.nombre AS tipo_documento,
            sd.serie,
            v.numero_documento,
            v.total,
            v.estado
        FROM
            ventas v
        LEFT JOIN
            clientes c ON v.id_cliente = c.id
        LEFT JOIN
            tipo_documento_venta tdv ON v.id_tipo_documento_venta = tdv.id
        LEFT JOIN
            series_documentos sd ON v.id_serie_documento = sd.id
    ";

    $where_clauses = [];
    $params = [];

    // Lógica de filtros (ejemplo)
    if (!empty($_GET['fecha_inicio'])) {
        $where_clauses[] = "v.fecha_emision >= :fecha_inicio";
        $params[':fecha_inicio'] = $_GET['fecha_inicio'];
    }
    if (!empty($_GET['fecha_fin'])) {
        // Añadir 1 día para incluir todo el día de la fecha fin
        $fecha_fin = date('Y-m-d', strtotime($_GET['fecha_fin'] . ' +1 day'));
        $where_clauses[] = "v.fecha_emision < :fecha_fin";
        $params[':fecha_fin'] = $fecha_fin;
    }
    if (!empty($_GET['search'])) {
        $where_clauses[] = "(c.nombres_apellidos LIKE :search OR v.numero_documento LIKE :search OR sd.serie LIKE :search)";
        $params[':search'] = '%' . $_GET['search'] . '%';
    }
    if (!empty($_GET['estado']) && $_GET['estado'] !== 'Todos') {
        $where_clauses[] = "v.estado = :estado";
        $params[':estado'] = $_GET['estado'];
    }

    if (!empty($where_clauses)) {
        $query .= " WHERE " . implode(' AND ', $where_clauses);
    }

    $query .= " ORDER BY v.fecha_emision DESC";

    // Lógica de paginación (a implementar si es necesario)
    // $query .= " LIMIT :offset, :limit";

    $stmt = $pdo->prepare($query);
    $stmt->execute($params);

    $ventas = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if ($ventas) {
        http_response_code(200);
        echo json_encode(["records" => $ventas]);
    } else {
        http_response_code(404);
        echo json_encode(["message" => "No se encontraron ventas."]);
    }

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["message" => "Error en la base de datos: " . $e->getMessage()]);
}
?>
