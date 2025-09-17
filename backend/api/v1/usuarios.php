<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

include_once __DIR__ . '/../../config/app_config.php';
include_once __DIR__ . '/../../models/User.php';

$user = new User();
$request_method = $_SERVER["REQUEST_METHOD"];

function sendUserRecords($stmt) {
    $num = $stmt->rowCount();
    if ($num > 0) {
        $records_arr = ["records" => []];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            array_push($records_arr["records"], $row);
        }
        http_response_code(200);
        echo json_encode($records_arr);
    } else {
        http_response_code(200);
        echo json_encode(["records" => []]);
    }
}

switch ($request_method) {
    case 'GET':
        if (isset($_GET['rol'])) {
            $stmt = $user->getUsersByRole($_GET['rol']);
            sendUserRecords($stmt);
        } elseif (isset($_GET['resource']) && $_GET['resource'] == 'roles') {
            $stmt = $user->getAllRoles();
            sendUserRecords($stmt);
        } elseif (!empty($_GET["id"])) {
            $user_id = intval($_GET["id"]);
            $user_data = $user->readOne($user_id);
            if ($user_data) {
                http_response_code(200);
                echo json_encode($user_data);
            } else {
                http_response_code(404);
                echo json_encode(["message" => "Usuario no encontrado."]);
            }
        } else {
            $stmt = $user->readAll();
            sendUserRecords($stmt);
        }
        break;
    case 'POST':
        $data = json_decode(file_get_contents("php://input"));
        if (!empty($data->username) && !empty($data->nombre_completo) && !empty($data->password) && !empty($data->id_rol)) {
            $stmt = $user->create($data->username, $data->nombre_completo, $data->email, $data->password, $data->id_rol);
            if ($stmt) {
                http_response_code(201);
                echo json_encode(["message" => "Usuario creado."]);
            } else {
                http_response_code(503);
                echo json_encode(["message" => "No se pudo crear el usuario."]);
            }
        } else {
            http_response_code(400);
            echo json_encode(["message" => "Datos incompletos."]);
        }
        break;
    case 'PUT':
        $data = json_decode(file_get_contents("php://input"));
        $user_id = !empty($_GET['id']) ? intval($_GET['id']) : null;
        if ($user_id && !empty($data->nombre_completo) && isset($data->activo)) {
            $password = !empty($data->password) ? $data->password : '';
            if ($user->update($user_id, $data->nombre_completo, $data->email, $data->id_rol, $data->activo, $password)) {
                http_response_code(200);
                echo json_encode(["message" => "Usuario actualizado."]);
            } else {
                http_response_code(503);
                echo json_encode(["message" => "No se pudo actualizar el usuario."]);
            }
        } else {
            http_response_code(400);
            echo json_encode(["message" => "Datos incompletos."]);
        }
        break;
    case 'DELETE':
        $user_id = !empty($_GET['id']) ? intval($_GET['id']) : null;
        if ($user_id) {
            if ($user_id == 1) { // Proteger al super admin
                http_response_code(403);
                echo json_encode(["message" => "No se puede eliminar al administrador principal."]);
                exit;
            }
            if ($user->delete($user_id)) {
                http_response_code(200);
                echo json_encode(["message" => "Usuario desactivado."]);
            } else {
                http_response_code(503);
                echo json_encode(["message" => "No se pudo desactivar al usuario."]);
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
?>
