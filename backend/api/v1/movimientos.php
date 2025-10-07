<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

require_once __DIR__ . '/../../models/Movimiento.php';

try {
    $movimiento = new Movimiento();
    $request_method = $_SERVER["REQUEST_METHOD"];

    switch ($request_method) {
        case 'GET':
            if (isset($_GET['id'])) {
                $id = intval($_GET['id']);
                $movimiento_data = $movimiento->getById($id);
                if ($movimiento_data) {
                    http_response_code(200);
                    echo json_encode($movimiento_data);
                } else {
                    http_response_code(404);
                    echo json_encode(["message" => "Movimiento no encontrado."]);
                }
            } else {
                // Capturar todos los filtros de la URL
                $filter = $_GET['filter'] ?? '';
                $tipo_movimiento = $_GET['tipo_movimiento'] ?? null;
                $tipo_entidad = $_GET['tipo_entidad'] ?? null;
                $id_almacen = isset($_GET['id_almacen']) && $_GET['id_almacen'] !== '' ? intval($_GET['id_almacen']) : null;
                $anio = isset($_GET['anio']) && $_GET['anio'] !== '' ? $_GET['anio'] : null;
                $mes = isset($_GET['mes']) && $_GET['mes'] !== '' ? $_GET['mes'] : null;

                $page = isset($_GET['page']) ? intval($_GET['page']) : 1;
                $limit = 10;
                $offset = ($page - 1) * $limit;

                // Pasar todos los filtros al modelo
                $movimientos = $movimiento->getAll($filter, $tipo_movimiento, $tipo_entidad, $id_almacen, $anio, $mes, $limit, $offset);
                $total_records = $movimiento->count($filter, $tipo_movimiento, $tipo_entidad, $id_almacen, $anio, $mes);

                $response = [
                    "records" => $movimientos,
                    "pagination" => [
                        "page" => $page,
                        "total_records" => (int)$total_records,
                        "total_pages" => $total_records > 0 ? ceil($total_records / $limit) : 0
                    ]
                ];

                http_response_code(200);
                echo json_encode($response, JSON_INVALID_UTF8_SUBSTITUTE);
            }
            break;

        case 'POST':
            $data = json_decode(file_get_contents("php://input"), true);
            if (empty($data) || empty($data['detalle'])) {
                http_response_code(400);
                echo json_encode(["message" => "Datos incompletos o detalle de movimiento vacío."]);
                exit;
            }
            $resultado = $movimiento->create($data);
            if ($resultado && isset($resultado['id'])) {
                http_response_code(201);
                echo json_encode(["message" => "Movimiento creado con éxito.", "id" => $resultado['id']]);
            } else {
                http_response_code(503);
                echo json_encode(["message" => "No se pudo crear el movimiento."]);
            }
            break;

        case 'PUT':
            $id = isset($_GET['id']) ? intval($_GET['id']) : 0;
            if ($id === 0) {
                http_response_code(400);
                echo json_encode(["message" => "ID de movimiento no proporcionado."]);
                exit;
            }
            $data = json_decode(file_get_contents("php://input"), true);
            if (empty($data)) {
                 http_response_code(400);
                echo json_encode(["message" => "Datos incompletos."]);
                exit;
            }
            if ($movimiento->update($id, $data)) {
                http_response_code(200);
                echo json_encode(["message" => "Movimiento actualizado con éxito."]);
            } else {
                http_response_code(503);
                echo json_encode(["message" => "No se pudo actualizar el movimiento."]);
            }
            break;

        case 'DELETE':
            $id = isset($_GET['id']) ? intval($_GET['id']) : 0;
             if ($id === 0) {
                http_response_code(400);
                echo json_encode(["message" => "ID de movimiento no proporcionado."]);
                exit;
            }
            if ($movimiento->delete($id)) {
                http_response_code(200);
                echo json_encode(["message" => "Movimiento eliminado con éxito."]);
            } else {
                http_response_code(503);
                echo json_encode(["message" => "No se pudo eliminar el movimiento."]);
            }
            break;

        default:
            http_response_code(405);
            echo json_encode(["message" => "Método no permitido."]);
            break;
    }

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["message" => "Error en el servidor al procesar la solicitud.", "error" => $e->getMessage()]);
}
?>