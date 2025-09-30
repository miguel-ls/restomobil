<?php
// Headers para permitir el acceso a la API
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

// Incluir el archivo de conexión a la base de datos
include_once __DIR__ . '/../../core/Database.php';

// Verificar que el método de la solicitud sea GET
$request_method = $_SERVER["REQUEST_METHOD"];

if ($request_method == 'GET') {
    // Verificar si se proporcionó el parámetro 'fecha'
    if (isset($_GET['fecha'])) {
        $fecha = $_GET['fecha'];

        // Validar que la fecha tenga el formato YYYY-MM-DD
        if (preg_match("/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/", $fecha)) {
            try {
                // Obtener instancia de la base de datos
                $conn = Database::getInstance();

                // Preparar la llamada al procedimiento almacenado
                $query = "CALL sp_verificar_cierre_por_fecha(:fecha)";
                $stmt = $conn->prepare($query);

                // Vincular el parámetro de fecha
                $stmt->bindParam(':fecha', $fecha, PDO::PARAM_STR);

                // Ejecutar el procedimiento
                $stmt->execute();

                // Obtener el resultado
                $result = $stmt->fetch(PDO::FETCH_ASSOC);

                // Cerrar el cursor para permitir otras consultas
                $stmt->closeCursor();

                if ($result) {
                    // Convertir el resultado a booleano
                    $cierre_existente = (int)$result['cierre_existente'] > 0;
                    http_response_code(200);
                    echo json_encode(["cierre_existente" => $cierre_existente]);
                } else {
                    // Error si el procedimiento no devuelve un resultado
                    http_response_code(500);
                    echo json_encode(["message" => "Error al verificar el cierre de caja."]);
                }
            } catch (PDOException $e) {
                // Error de conexión o de consulta a la base de datos
                http_response_code(503); // Service Unavailable
                echo json_encode(["message" => "Error en la base de datos: " . $e->getMessage()]);
            }
        } else {
            // Error si el formato de la fecha es incorrecto
            http_response_code(400);
            echo json_encode(["message" => "Formato de fecha inválido. Use YYYY-MM-DD."]);
        }
    } else {
        // Error si no se proporciona la fecha
        http_response_code(400);
        echo json_encode(["message" => "El parámetro 'fecha' es requerido."]);
    }
} else {
    // Error si se usa un método HTTP diferente a GET
    http_response_code(405); // Method Not Allowed
    echo json_encode(["message" => "Método no permitido."]);
}
?>