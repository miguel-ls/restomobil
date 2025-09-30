<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");

require_once __DIR__ . '/../../config/database.php';

$fecha = $_GET['fecha'] ?? null;

if (!$fecha) {
    http_response_code(400);
    echo json_encode(['error' => 'La fecha es requerida.']);
    exit;
}

try {
    $pdo = new PDO(DB_DSN, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Llamar al procedimiento almacenado
    $stmt = $pdo->prepare("CALL sp_calcular_cierre(:fecha)");
    $stmt->execute(['fecha' => $fecha]);

    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    // El procedimiento almacenado devuelve una columna llamada 'total_cierre'
    $total_cierre = $result['total_cierre'] ?? 0;

    echo json_encode(['total_cierre' => (float)$total_cierre]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Error en la base de datos: ' . $e->getMessage()]);
}
?>