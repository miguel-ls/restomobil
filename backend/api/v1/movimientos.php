<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

require_once __DIR__ . '/../../core/Database.php';
require_once __DIR__ . '/../../models/Movimiento.php';

try {
    $movimiento = new Movimiento();
    $request_method = $_SERVER["REQUEST_METHOD"];
    $data = json_decode(file_get_contents("php://input"), true);

    switch ($request_method) {
        case 'GET':
            if (!empty($_GET["id"])) {
                $id = intval($_GET["id"]);
                $resultado = $movimiento->getById($id);
                if ($resultado) {
                    http_response_code(200);
                    echo json_encode($resultado);
                } else {
                    http_response_code(404);
                    echo json_encode(array("message" => "Movimiento no encontrado."));
                }
            } else {
                $filter = isset($_GET['filter']) ? $_GET['filter'] : '';
                $page = isset($_GET['page']) ? intval($_GET['page']) : 1;
                $limit = 10;
                $offset = ($page - 1) * $limit;

                $movimientos = $movimiento->getAll($filter, $limit, $offset);
                $total_records = $movimiento->count($filter);

                $response = [
                    "records" => $movimientos,
                    "pagination" => [
                        "page" => $page,
                        "total_records" => (int)$total_records,
                        "total_pages" => ceil($total_records / $limit)
                    ]
                ];
                http_response_code(200);
                echo json_encode($response);
            }
            break;

        case 'POST':
            if (!empty($data)) {
                $resultado = $movimiento->create($data);
                if ($resultado && isset($resultado['id'])) {
                    http_response_code(201);
                    echo json_encode(array("message" => "Movimiento creado.", "id" => $resultado['id']));
                } else {
                    http_response_code(503);
                    echo json_encode(array("message" => "No se pudo crear el movimiento."));
                }
            } else {
                http_response_code(400);
                echo json_encode(array("message" => "Datos incompletos."));
            }
            break;

        case 'PUT':
            if (!empty($_GET["id"]) && !empty($data)) {
                $id = intval($_GET["id"]);
                if ($movimiento->update($id, $data)) {
                    http_response_code(200);
                    echo json_encode(array("message" => "Movimiento actualizado."));
                } else {
                    http_response_code(503);
                    echo json_encode(array("message" => "No se pudo actualizar el movimiento."));
                }
            } else {
                http_response_code(400);
                echo json_encode(array("message" => "Datos incompletos o ID no proporcionado."));
            }
            break;

        case 'DELETE':
            if (!empty($_GET["id"])) {
                $id = intval($_GET["id"]);
                if ($movimiento->delete($id)) {
                    http_response_code(200);
                    echo json_encode(array("message" => "Movimiento anulado."));
                } else {
                    http_response_code(503);
                    echo json_encode(array("message" => "No se pudo anular el movimiento."));
                }
            } else {
                http_response_code(400);
                echo json_encode(array("message" => "No se proporcionó un ID."));
            }
            break;

        default:
            http_response_code(405);
            echo json_encode(array("message" => "Método no permitido."));
            break;
    }
} catch (Throwable $e) {
    // Aunque no ha funcionado antes, dejamos un manejador de errores genérico.
    error_log("Error no esperado en movimientos.php: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(["message" => "Ocurrió un error inesperado en el servidor."]);
}
?>