<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

include_once __DIR__ . '/../../models/Empresa.php';

$empresa = new Empresa();
$request_method = $_SERVER["REQUEST_METHOD"];

// Define the upload directory
$upload_dir = __DIR__ . '/../../../frontend_web/assets/img/logos/';
if (!is_dir($upload_dir)) {
    mkdir($upload_dir, 0777, true);
}

function handle_file_upload($file, $upload_dir) {
    $logo_url = '';
    if (isset($file) && $file['error'] == UPLOAD_ERR_OK) {
        $file_name = uniqid() . '-' . basename($file["name"]);
        $target_file = $upload_dir . $file_name;
        if (move_uploaded_file($file["tmp_name"], $target_file)) {
            // Return the relative path to be stored in DB
            $logo_url = 'assets/img/logos/' . $file_name;
        }
    }
    return $logo_url;
}

switch ($request_method) {
    case 'GET':
        if (!empty($_GET["id"])) {
            $empresa_id = intval($_GET["id"]);
            $empresa_data = $empresa->readOne($empresa_id);
            if ($empresa_data) {
                http_response_code(200);
                echo json_encode($empresa_data);
            } else {
                http_response_code(404);
                echo json_encode(["message" => "Empresa no encontrada."]);
            }
        } else {
            $stmt = $empresa->readAll();
            $num = $stmt->rowCount();
            if ($num > 0) {
                $empresas_arr = ["records" => []];
                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    array_push($empresas_arr["records"], $row);
                }
                http_response_code(200);
                echo json_encode($empresas_arr);
            } else {
                http_response_code(200);
                echo json_encode(["records" => []]);
            }
        }
        break;
    case 'POST':
        // For POST, we expect multipart/form-data, so we read from $_POST
        $data = $_POST;
        if (!empty($data['ruc']) && !empty($data['nombre_largo'])) {

            $logo_url = handle_file_upload($_FILES['logo'] ?? null, $upload_dir);

            $stmt = $empresa->create(
                $data['nombre_largo'], $data['nombre_corto'] ?? '', $data['ruc'], $data['direccion'] ?? '',
                $data['id_departamento'], $data['id_provincia'], $data['id_distrito'],
                $data['telefonos'] ?? '', $data['email'] ?? '', $data['web'] ?? '', $logo_url, $data['observaciones'] ?? '',
                $data['sunat_envio_estado'] ?? 0, $data['sunat_api_url'] ?? '', $data['sunat_api_key'] ?? ''
            );

            if ($stmt) {
                $new_empresa = $stmt->fetch(PDO::FETCH_ASSOC);
                http_response_code(201);
                echo json_encode(["message" => "Empresa creada.", "id" => $new_empresa['id']]);
            } else {
                http_response_code(503);
                echo json_encode(["message" => "No se pudo crear la empresa."]);
            }
        } else {
            http_response_code(400);
            echo json_encode(["message" => "Datos incompletos."]);
        }
        break;
    case 'PUT': // Note: PUT requests with multipart/form-data are tricky. We'll use POST with a hidden _method field from the frontend.
        http_response_code(405);
        echo json_encode(["message" => "Use POST con _method=PUT para actualizaciones con archivos."]);
        break;

    case 'DELETE':
        $empresa_id = !empty($_GET['id']) ? intval($_GET['id']) : null;
        if ($empresa_id) {
            // Optional: delete logo file from server
            $empresa_data = $empresa->readOne($empresa_id);
            if ($empresa_data && !empty($empresa_data['logo_url'])) {
                $file_path = __DIR__ . '/../../../frontend_web/' . $empresa_data['logo_url'];
                if (file_exists($file_path)) {
                    unlink($file_path);
                }
            }

            if ($empresa->delete($empresa_id)) {
                http_response_code(200);
                echo json_encode(["message" => "Empresa eliminada."]);
            } else {
                http_response_code(503);
                echo json_encode(["message" => "No se pudo eliminar la empresa."]);
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

// Handling PUT with multipart/form-data via POST override
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['_method']) && strtoupper($_POST['_method']) === 'PUT') {
    $data = $_POST;
    $empresa_id = !empty($data['id']) ? intval($data['id']) : null;

    if ($empresa_id && !empty($data['ruc']) && !empty($data['nombre_largo'])) {

        $current_empresa = $empresa->readOne($empresa_id);
        $logo_url = $current_empresa['logo_url'];

        // Check if a new logo is uploaded
        if (isset($_FILES['logo']) && $_FILES['logo']['error'] == UPLOAD_ERR_OK) {
            // Delete old logo if it exists
            if (!empty($logo_url) && file_exists($upload_dir . basename($logo_url))) {
                unlink($upload_dir . basename($logo_url));
            }
            $logo_url = handle_file_upload($_FILES['logo'], $upload_dir);
        } elseif (isset($data['remove_logo']) && $data['remove_logo'] == '1') {
             if (!empty($logo_url) && file_exists($upload_dir . basename($logo_url))) {
                unlink($upload_dir . basename($logo_url));
            }
            $logo_url = '';
        }

        $estado = isset($data['estado']) ? (int)$data['estado'] : 0;
        $sunat_estado = isset($data['sunat_envio_estado']) ? (int)$data['sunat_envio_estado'] : 0;

        if ($empresa->update(
            $empresa_id, $data['nombre_largo'], $data['nombre_corto'] ?? '', $data['ruc'], $data['direccion'] ?? '',
            $data['id_departamento'], $data['id_provincia'], $data['id_distrito'],
            $data['telefonos'] ?? '', $data['email'] ?? '', $data['web'] ?? '', $logo_url, $data['observaciones'] ?? '',
            $estado, $sunat_estado, $data['sunat_api_url'] ?? '', $data['sunat_api_key'] ?? ''
        )) {
            http_response_code(200);
            echo json_encode(["message" => "Empresa actualizada."]);
        } else {
            http_response_code(503);
            echo json_encode(["message" => "No se pudo actualizar la empresa."]);
        }
    } else {
        http_response_code(400);
        echo json_encode(["message" => "Datos incompletos para actualizar."]);
    }
}
?>
