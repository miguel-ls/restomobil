<?php
// Headers
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json; charset=UTF-8');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

// Incluir archivos de inicialización
include_once '../../config/database.php';
include_once '../../models/TipoDocumentoComprobante.php';

// Instanciar la base de datos y el objeto
$database = new Database();
$db = $database->getConnection();
$tipo_documento = new TipoDocumentoComprobante($db);

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        if (isset($_GET['id'])) {
            $tipo_documento->id = $_GET['id'];
            if ($tipo_documento->readOne()) {
                $item = array(
                    "id" => $tipo_documento->id,
                    "codigo_sunat" => $tipo_documento->codigo_sunat,
                    "descripcion" => $tipo_documento->descripcion,
                    "estado" => $tipo_documento->estado
                );
                echo json_encode($item);
            } else {
                http_response_code(404);
                echo json_encode(array("mensaje" => "Tipo de documento no encontrado."));
            }
        } else {
            $keywords = isset($_GET['s']) ? $_GET['s'] : '';
            $stmt = $tipo_documento->read($keywords);
            $num = $stmt->rowCount();

            if ($num > 0) {
                $items_arr = array();
                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    extract($row);
                    $item = array(
                        'id' => $id,
                        'codigo_sunat' => $codigo_sunat,
                        'descripcion' => $descripcion,
                        'estado' => $estado
                    );
                    array_push($items_arr, $item);
                }
                echo json_encode($items_arr);
            } else {
                echo json_encode(array('mensaje' => 'No se encontraron tipos de documento.'));
            }
        }
        break;

    case 'POST':
        $data = json_decode(file_get_contents("php://input"));
        if (!empty($data->descripcion)) {
            $tipo_documento->codigo_sunat = $data->codigo_sunat ?? '';
            $tipo_documento->descripcion = $data->descripcion;
            $tipo_documento->estado = $data->estado ?? 1;

            if ($tipo_documento->create()) {
                http_response_code(201);
                echo json_encode(array("mensaje" => "Tipo de documento creado exitosamente.", "id" => $tipo_documento->id));
            } else {
                http_response_code(503);
                echo json_encode(array("mensaje" => "No se pudo crear el tipo de documento."));
            }
        } else {
            http_response_code(400);
            echo json_encode(array("mensaje" => "Datos incompletos."));
        }
        break;

    case 'PUT':
        $data = json_decode(file_get_contents("php://input"));
        if (!empty($data->id) && !empty($data->descripcion) && isset($data->estado)) {
            $tipo_documento->id = $data->id;
            $tipo_documento->codigo_sunat = $data->codigo_sunat ?? '';
            $tipo_documento->descripcion = $data->descripcion;
            $tipo_documento->estado = $data->estado;

            if ($tipo_documento->update()) {
                echo json_encode(array("mensaje" => "Tipo de documento actualizado exitosamente."));
            } else {
                http_response_code(503);
                echo json_encode(array("mensaje" => "No se pudo actualizar el tipo de documento."));
            }
        } else {
            http_response_code(400);
            echo json_encode(array("mensaje" => "Datos incompletos."));
        }
        break;

    case 'DELETE':
        $data = json_decode(file_get_contents("php://input"));
        if (!empty($data->id)) {
            $tipo_documento->id = $data->id;
            if ($tipo_documento->delete()) {
                echo json_encode(array("mensaje" => "Tipo de documento eliminado exitosamente."));
            } else {
                http_response_code(503);
                echo json_encode(array("mensaje" => "No se pudo eliminar el tipo de documento."));
            }
        } else {
            http_response_code(400);
            echo json_encode(array("mensaje" => "ID no proporcionado."));
        }
        break;

    default:
        http_response_code(405);
        echo json_encode(array("mensaje" => "Método no permitido."));
        break;
}
?>
