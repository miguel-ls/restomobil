<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, PUT, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

include_once __DIR__ . '/../../config/database.php';
include_once __DIR__ . '/../../models/Venta.php';

$database = new Database();
$db = $database->getConnection();
$venta = new Venta($db);
$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        // --- Lógica de Paginación y Filtros para la lista de ventas ---
        $filtros = [
            'fecha_inicio' => $_GET['fecha_inicio'] ?? null,
            'fecha_fin' => $_GET['fecha_fin'] ?? null,
            'estado' => $_GET['estado'] ?? 'Todos',
            'id_tipo_documento' => isset($_GET['id_tipo_documento']) ? (int)$_GET['id_tipo_documento'] : null,
            'search' => $_GET['search'] ?? null,
            'page' => isset($_GET['page']) ? (int)$_GET['page'] : 1,
            'limit' => isset($_GET['limit']) ? (int)$_GET['limit'] : 10
        ];

        try {
            $total_records = $venta->contarTodo($filtros);
            $ventas_arr = ($total_records > 0) ? $venta->leerTodo($filtros)->fetchAll(PDO::FETCH_ASSOC) : [];

            $response = [
                "records" => $ventas_arr,
                "pagination" => [
                    "total_records" => $total_records,
                    "total_pages" => ceil($total_records / $filtros['limit']),
                    "current_page" => $filtros['page'],
                    "limit" => $filtros['limit']
                ]
            ];

            if ($total_records === 0) {
                $response["message"] = "No se encontraron ventas con los filtros aplicados.";
            }

            http_response_code(200);
            echo json_encode($response);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(["message" => "Error al leer las ventas: " . $e->getMessage()]);
        }
        break;

    case 'PUT':
        $data = json_decode(file_get_contents("php://input"));

        if (empty($data->id) || empty($data->fecha_emision)) {
            http_response_code(400);
            echo json_encode(["success" => false, "message" => "ID de venta y fecha de emisión son requeridos."]);
            break;
        }

        try {
            if ($venta->update($data->id, $data->fecha_emision)) {
                http_response_code(200);
                echo json_encode(["success" => true, "message" => "Venta actualizada correctamente."]);
            } else {
                throw new Exception("No se pudo actualizar la venta.");
            }
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(["success" => false, "message" => "Error al actualizar la venta: " . $e->getMessage()]);
        }
        break;

    default:
        header('HTTP/1.1 405 Method Not Allowed');
        echo json_encode(["message" => "Método no permitido."]);
        break;
}
?>