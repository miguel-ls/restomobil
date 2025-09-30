<?php
// --- Manejador de Errores Global ---
// Se asegura de que cualquier error no capturado devuelva una respuesta JSON válida
// en lugar de una página de error HTML, solucionando el error "Unexpected token '<'".
function json_exception_handler($exception) {
    error_log("Error no capturado en reportes_ventas_handler.php: " . $exception->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Ocurrió un error inesperado en el servidor. Por favor, contacte a soporte.'
    ]);
    exit();
}
set_exception_handler('json_exception_handler');

// Incluir la configuración de la base de datos y las funciones comunes
// Ajustamos las rutas para que sean correctas desde __DIR__
require_once __DIR__ . '/../../core/Database.php'; // Incluir la clase Database
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/app_config.php';
require_once __DIR__ . '/../../models/ReportesModel.php';

// Configurar cabeceras para respuesta JSON
header('Content-Type: application/json');

// Función para enviar respuestas de error
function send_error($message, $code = 400) {
    http_response_code($code);
    echo json_encode(['success' => false, 'error' => $message]);
    exit();
}

// Función para enviar respuestas exitosas
function send_success($data) {
    // Para operaciones que no devuelven datos (como eliminar), podemos enviar un mensaje de éxito.
    if (is_array($data) && !isset($data['success'])) {
        $response = ['success' => true] + $data;
    } else if (is_bool($data) && $data === true) {
        $response = ['success' => true, 'message' => 'Operación realizada con éxito.'];
    }
    else {
        $response = $data; // Asumimos que ya tiene la estructura correcta
    }
    echo json_encode($response);
    exit();
}

// --- Diccionario de Columnas para Reportes de Ventas ---
function get_sales_dictionary() {
    // Diccionario corregido según el esquema de la base de datos `esquema_ventas.sql`
    return [
        ['key' => 'v.id', 'friendly_name' => 'ID Venta', 'type' => 'number'],
        ['key' => 'v.fecha_emision', 'friendly_name' => 'Fecha de Emisión', 'type' => 'date'],
        ['key' => 'v.total', 'friendly_name' => 'Total Venta', 'type' => 'number'],
        ['key' => 'v.estado', 'friendly_name' => 'Estado Venta', 'type' => 'string'],
        ['key' => 'c.nombre_razon_social', 'friendly_name' => 'Cliente', 'type' => 'string'],
        ['key' => 'c.numero_documento', 'friendly_name' => 'Documento Cliente', 'type' => 'string'],
        ['key' => 'u.nombre_usuario', 'friendly_name' => 'Cajero', 'type' => 'string'],
        ['key' => 'tdv.nombre', 'friendly_name' => 'Tipo Documento', 'type' => 'string'],
        ['key' => 'sd.serie', 'friendly_name' => 'Serie Documento', 'type' => 'string'],
        ['key' => 'v.numero_documento', 'friendly_name' => 'Número Documento', 'type' => 'string'],
        // Las columnas total_neto y total_igv no existen en la tabla ventas, se eliminan.
    ];
}


// --- Lógica Principal ---
try {
    // Obtener la instancia de PDO usando el método estático correcto
    $db = Database::getInstance();
    $reportesModel = new ReportesModel($db);
} catch (Exception $e) {
    send_error('Error de conexión con la base de datos: ' . $e->getMessage(), 500);
}

$action = $_GET['action'] ?? '';
$request_method = $_SERVER['REQUEST_METHOD'];

// --- Enrutador de Acciones ---
switch ($action) {
    case 'get_dictionary':
        if ($request_method === 'GET') {
            // Se envuelve el resultado en un array asociativo para una respuesta JSON consistente
            send_success(['columns' => get_sales_dictionary()]);
        } else {
            send_error('Método no permitido para esta acción.', 405);
        }
        break;

    case 'get_report':
        if ($request_method === 'POST') {
            try {
                $json_data = file_get_contents('php://input');
                $data = json_decode($json_data, true);

                if (json_last_error() !== JSON_ERROR_NONE) {
                    send_error('JSON inválido.');
                }

                $columns = $data['columns'] ?? [];
                $filters = $data['filters'] ?? [];

                if (empty($columns)) {
                    send_error('Debe seleccionar al menos una columna.');
                }

                $dictionary = get_sales_dictionary();
                $reportData = $reportesModel->getReportData($columns, $filters, $dictionary);

                send_success(['data' => $reportData]);

            } catch (PDOException $e) {
                // Captura errores específicos de la base de datos (ej. SQL inválido)
                error_log('Error de base de datos en get_report: ' . $e->getMessage());
                send_error('Ocurrió un error al consultar la base de datos. Revise los filtros y columnas.', 500);
            } catch (Exception $e) {
                // Captura otros errores inesperados
                error_log('Error inesperado en get_report: ' . $e->getMessage());
                send_error('Ocurrió un error inesperado en el servidor.', 500);
            }
        } else {
            send_error('Método no permitido.', 405);
        }
        break;

    case 'get_templates':
        if ($request_method === 'GET') {
            $templates = $reportesModel->getTemplates();
            send_success(['templates' => $templates]);
        } else {
            send_error('Método no permitido.', 405);
        }
        break;

    case 'get_template_details':
        if ($request_method === 'GET') {
            $id = $_GET['id'] ?? 0;
            if (!$id) send_error('ID de plantilla no proporcionado.');

            $template = $reportesModel->getTemplateById((int)$id);
            if (!$template) send_error('Plantilla no encontrada.', 404);

            send_success($template);
        } else {
            send_error('Método no permitido.', 405);
        }
        break;

    case 'save_template':
        if ($request_method === 'POST') {
            $json_data = file_get_contents('php://input');
            $data = json_decode($json_data, true);

            $name = $data['name'] ?? '';
            $columns = $data['columns'] ?? [];

            if (empty($name) || empty($columns)) {
                send_error('El nombre y las columnas son obligatorios.');
            }

            $newId = $reportesModel->saveTemplate($name, $columns);
            send_success(['message' => 'Plantilla guardada con éxito.', 'new_id' => $newId]);
        } else {
            send_error('Método no permitido.', 405);
        }
        break;

    case 'delete_template':
        if ($request_method === 'POST') {
            $json_data = file_get_contents('php://input');
            $data = json_decode($json_data, true);
            $id = $data['id'] ?? 0;

            if (!$id) send_error('ID de plantilla no proporcionado.');

            if ($reportesModel->deleteTemplate((int)$id)) {
                send_success(['success' => true, 'message' => 'Plantilla eliminada con éxito.']);
            } else {
                send_error('No se pudo eliminar la plantilla.', 500);
            }
        } else {
            send_error('Método no permitido.', 405);
        }
        break;

    default:
        send_error('Acción no válida o no especificada.');
        break;
}
?>