<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

include_once __DIR__ . '/../../models/User.php';

// Solo permitir el método POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405); // Method Not Allowed
    echo json_encode(array("message" => "Método no permitido."));
    exit();
}

// --- INICIO CÓDIGO DE DEPURACIÓN ---
$debug_log_file = __DIR__ . '/login_debug.log';
$raw_input = file_get_contents("php://input");
$post_data = print_r($_POST, true);
$timestamp = date('Y-m-d H:i:s');
$log_message = "--- INICIO DEPURACIÓN [$timestamp] ---\n";
$log_message .= "RAW INPUT:\n" . $raw_input . "\n\n";
$log_message .= "DATOS \$_POST:\n" . $post_data . "\n\n";
// --- FIN CÓDIGO DE DEPURACIÓN ---

// Obtener los datos enviados
// Primero intenta decodificar el cuerpo JSON
$data = json_decode($raw_input);

// Si el cuerpo JSON está vacío o no es un objeto, intenta obtener los datos de $_POST
// Esto da flexibilidad para aceptar tanto application/json como application/x-www-form-urlencoded
if (!is_object($data) && isset($_POST['username'])) {
    // Convertir el array $_POST a un objeto para mantener una estructura de datos consistente
    $data = (object)$_POST;
}

// --- INICIO CÓDIGO DE DEPURACIÓN (PARTE 2) ---
$final_data = print_r($data, true);
$log_message .= "DATOS FINALES (\$data):\n" . $final_data . "\n\n";
$log_message .= "--- FIN DEPURACIÓN ---\n\n";
file_put_contents($debug_log_file, $log_message, FILE_APPEND);
// --- FIN CÓDIGO DE DEPURACIÓN (PARTE 2) ---

// Validar que se recibieron los datos necesarios
if (empty($data->username) || empty($data->password)) {
    http_response_code(400); // Bad Request
    echo json_encode(array("message" => "No se pudo iniciar sesión. Faltan el nombre de usuario o la contraseña."));
    exit();
}

$user = new User();

// Buscar usuario por nombre de usuario
if ($user->findByUsername($data->username)) {
    // Verificar la contraseña
    if ($user->verifyPassword($data->password)) {

        // Autenticación exitosa
        // En una implementación real, aquí se generaría un token JWT (JSON Web Token)

        http_response_code(200);
        echo json_encode(array(
            "message" => "Inicio de sesión exitoso.",
            "user" => array(
                "id" => $user->id,
                "username" => $user->username,
                "nombre" => $user->nombre_completo,
                "email" => $user->email,
                "rol" => $user->nombre_rol
            )
        ));

    } else {
        // Contraseña incorrecta
        http_response_code(401); // Unauthorized
        echo json_encode(array("message" => "Credenciales incorrectas."));
    }
} else {
    // Usuario no encontrado
    http_response_code(404); // Not Found
    echo json_encode(array("message" => "Usuario no encontrado."));
}
?>
