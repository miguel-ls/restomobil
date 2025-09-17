<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

include_once __DIR__ . '/../../models/User.php';

$user = new User();
$request_method = $_SERVER["REQUEST_METHOD"];

switch ($request_method) {
    case 'GET':
        // /usuarios.php?resource=roles
        if (isset($_GET['resource']) && $_GET['resource'] == 'roles') {
            handleGetAllRoles($user);
        }
        // /usuarios.php?id=1
        elseif (!empty($_GET["id"])) {
            $user_id = intval($_GET["id"]);
            $user_data = $user->readOne($user_id);
            if ($user_data) {
                http_response_code(200);
                echo json_encode($user_data);
            } else {
                http_response_code(404);
                echo json_encode(["message" => "Usuario no encontrado."]);
            }
        }
        // /usuarios.php
        else {
            handleGetAllUsers($user);
        }
        break;

    case 'POST':
        $data = json_decode(file_get_contents("php://input"));
        if (!empty($data->username) && !empty($data->nombre_completo) && !empty($data->password) && !empty($data->id_rol)) {
            $stmt = $user->create($data->username, $data->nombre_completo, $data->email, $data->password, $data->id_rol);
            if ($stmt) {
                $new_user = $stmt->fetch(PDO::FETCH_ASSOC);
                http_response_code(201);
                echo json_encode(["message" => "Usuario creado.", "id" => $new_user['id']]);
            } else {
                http_response_code(503);
                echo json_encode(["message" => "No se pudo crear el usuario."]);
            }
        } else {
            http_response_code(400);
            echo json_encode(["message" => "Datos incompletos para crear el usuario."]);
        }
        break;

    case 'PUT':
        $data = json_decode(file_get_contents("php://input"));
        if (!empty($data->id) && !empty($data->nombre_completo) && isset($data->activo)) {
            // La contraseña es opcional en la actualización
            $password = !empty($data->password) ? $data->password : '';
            if ($user->update($data->id, $data->nombre_completo, $data->email, $data->id_rol, $data->activo, $password)) {
                http_response_code(200);
                echo json_encode(["message" => "Usuario actualizado."]);
            } else {
                http_response_code(503);
                echo json_encode(["message" => "No se pudo actualizar el usuario."]);
            }
        } else {
            http_response_code(400);
            echo json_encode(["message" => "Datos incompletos para actualizar el usuario."]);
        }
        break;

    case 'DELETE':
        if (!empty($_GET["id"])) {
            $user_id = intval($_GET["id"]);
            if ($user->delete($user_id)) {
                http_response_code(200);
                echo json_encode(["message" => "Usuario desactivado."]);
            } else {
                http_response_code(503);
                echo json_encode(["message" => "No se pudo desactivar el usuario."]);
            }
        } else {
            http_response_code(400);
            echo json_encode(["message" => "No se proporcionó un ID para eliminar."]);
        }
        break;

    default:
        http_response_code(405);
        echo json_encode(["message" => "Método no permitido."]);
        break;
}

function handleGetAllUsers($user) {
    $stmt = $user->readAll();
    $num = $stmt->rowCount();
    if ($num > 0) {
        $users_arr = ["records" => []];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            extract($row);
            $user_item = [
                "id" => $id,
                "username" => $username,
                "nombre_completo" => $nombre_completo,
                "email" => $email,
                "id_rol" => $id_rol,
                "nombre_rol" => $nombre_rol,
                "activo" => $activo,
            ];
            array_push($users_arr["records"], $user_item);
        }
        http_response_code(200);
        echo json_encode($users_arr);
    } else {
        http_response_code(200); // OK
        echo json_encode(["records" => []]);
    }
}

function handleGetAllRoles($user) {
    $stmt = $user->getAllRoles();
    $num = $stmt->rowCount();
    if ($num > 0) {
        $roles_arr = ["records" => []];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            extract($row);
            $role_item = [
                "id" => $id,
                "nombre_rol" => $nombre_rol,
            ];
            array_push($roles_arr["records"], $role_item);
        }
        http_response_code(200);
        echo json_encode($roles_arr);
    } else {
        http_response_code(200); // OK
        echo json_encode(["records" => []]);
    }
}
?>
