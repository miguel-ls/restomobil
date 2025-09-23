<?php
// Headers
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

session_start();

include_once __DIR__ . '/../../config/app_config.php';
include_once __DIR__ . '/../../core/Database.php';
include_once __DIR__ . '/../../models/AperturaCierre.php';

$aperturaCierre = new AperturaCierre();
$request_method = $_SERVER["REQUEST_METHOD"];

switch ($request_method) {
    case 'GET':
        if (!empty($_GET["id"])) {
            $id = intval($_GET["id"]);
            $data = $aperturaCierre->readOne($id);
            if ($data) {
                http_response_code(200);
                echo json_encode($data);
            } else {
                http_response_code(404);
                echo json_encode(["message" => "Registro no encontrado."]);
            }
        } else {
            $filters = [
                'fecha_inicio' => $_GET['fecha_inicio'] ?? null,
                'fecha_fin' => $_GET['fecha_fin'] ?? null,
                'tipo_movimiento' => $_GET['tipo_movimiento'] ?? null,
            ];
            handleGetAll($aperturaCierre, $filters);
        }
        break;

    case 'POST':
        $data = json_decode(file_get_contents("php://input"));
        if (!empty($data->fecha) && !empty($data->tipo_movimiento) && isset($data->importe) && isset($_SESSION['user_id'])) {
            $result = $aperturaCierre->create($data->fecha, $data->tipo_movimiento, $data->importe, $data->descripcion ?? '', $_SESSION['user_id']);
            if (is_numeric($result)) {
                http_response_code(201);
                echo json_encode(["message" => "Registro creado con éxito.", "id" => $result]);
            } else {
                http_response_code(409); // 409 Conflict es más apropiado para una clave única duplicada
                echo json_encode(["message" => $result]);
            }
        } else {
            http_response_code(400);
            echo json_encode(["message" => "Datos incompletos o sesión no iniciada."]);
        }
        break;

    case 'PUT':
        $data = json_decode(file_get_contents("php://input"));
        if (!empty($data->id) && !empty($data->fecha) && !empty($data->tipo_movimiento) && isset($data->importe)) {
            $result = $aperturaCierre->update($data->id, $data->fecha, $data->tipo_movimiento, $data->importe, $data->descripcion ?? '');
            if ($result === true) {
                http_response_code(200);
                echo json_encode(["message" => "Registro actualizado con éxito."]);
            } else {
                http_response_code(409); // 409 Conflict
                echo json_encode(["message" => $result]);
            }
        } else {
            http_response_code(400);
            echo json_encode(["message" => "Datos incompletos."]);
        }
        break;

    case 'DELETE':
        if (!empty($_GET["id"])) {
            $id = intval($_GET["id"]);
            if ($aperturaCierre->delete($id)) {
                http_response_code(200);
                echo json_encode(["message" => "Registro eliminado."]);
            } else {
                http_response_code(503);
                echo json_encode(["message" => "No se pudo eliminar el registro."]);
            }
        } else {
            http_response_code(400);
            echo json_encode(["message" => "ID no proporcionado."]);
        }
        break;

    default:
        http_response_code(405);
        echo json_encode(["message" => "Método no permitido."]);
        break;
}

function handleGetAll($aperturaCierre, $filters) {
    $page = isset($_GET['page']) ? intval($_GET['page']) : 1;
    $page_size = isset($_GET['page_size']) ? intval($_GET['page_size']) : 10;

    $stmt = $aperturaCierre->readAll($filters, $page, $page_size);
    $records = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $stmt->closeCursor();

    $total_records = $aperturaCierre->countAll($filters);
    $total_pages = ($page_size > 0) ? ceil($total_records / $page_size) : 1;

    if (count($records) > 0) {
        http_response_code(200);
        echo json_encode([
            "records" => $records,
            "pagination" => [
                "page" => $page,
                "page_size" => $page_size,
                "total_pages" => $total_pages,
                "total_records" => $total_records
            ]
        ]);
    } else {
        http_response_code(200); // Devolver 200 con un array vacío es una práctica común
        echo json_encode([
            "message" => "No se encontraron registros.",
            "records" => [],
            "pagination" => [
                "page" => $page,
                "page_size" => $page_size,
                "total_pages" => 0,
                "total_records" => 0
            ]
        ]);
    }
}
?>
