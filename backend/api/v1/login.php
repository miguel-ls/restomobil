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

// Obtener los datos enviados
// Primero intenta decodificar el cuerpo JSON
$data = json_decode(file_get_contents("php://input"));

// Si el cuerpo JSON está vacío o no es un objeto, intenta obtener los datos de $_POST
if (!is_object($data) && isset($_POST['username'])) {
    // Convertir el array $_POST a un objeto para mantener una estructura de datos consistente
    $data = (object)$_POST;
}

// Validar que se recibieron los datos necesarios y limpiarlos
if (empty($data->username) || empty($data->password)) {
    http_response_code(400); // Bad Request
    echo json_encode(array("message" => "No se pudo iniciar sesión. Faltan el nombre de usuario o la contraseña."));
    exit();
}

// Limpiar los datos de entrada para eliminar espacios en blanco accidentales
$data->username = trim($data->username);
$data->password = trim($data->password);

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
