<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

include_once __DIR__ . '/../../config/database.php';

if (empty($_GET['id_serie_documento'])) {
    http_response_code(400);
    echo json_encode(["message" => "ID de serie no proporcionado."]);
    exit;
}

$id_serie_documento = intval($_GET['id_serie_documento']);

try {
    $pdo = new PDO(DB_DSN, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $stmt = $pdo->prepare("SELECT IFNULL(MAX(CAST(numero_documento AS UNSIGNED)), 0) + 1 AS next_num FROM ventas WHERE id_serie_documento = ?");
    $stmt->execute([$id_serie_documento]);

    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $next_num = $result['next_num'];

    http_response_code(200);
    echo json_encode(["next_correlativo" => $next_num]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["message" => "Error en la base de datos: " . $e->getMessage()]);
}
?>
