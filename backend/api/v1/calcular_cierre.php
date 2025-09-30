<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");

require_once '../../includes/db_connect.php';

$fecha = $_GET['fecha'] ?? null;

if (!$fecha) {
    http_response_code(400);
    echo json_encode(['error' => 'La fecha es requerida.']);
    exit;
}

try {
    // Calcular el total de movimientos de caja para la fecha dada
    $stmt_movimientos = $pdo->prepare("
        SELECT
            SUM(CASE WHEN tipo_movimiento = 'ENTRADA' THEN importe ELSE 0 END) -
            SUM(CASE WHEN tipo_movimiento = 'SALIDA' THEN importe ELSE 0 END) as total_movimientos
        FROM movimientos_caja
        WHERE DATE(fecha) = :fecha
    ");
    $stmt_movimientos->execute(['fecha' => $fecha]);
    $total_movimientos = $stmt_movimientos->fetchColumn();
    if ($total_movimientos === false) {
        $total_movimientos = 0;
    }

    // Calcular el total de ventas emitidas para la fecha dada
    $stmt_ventas = $pdo->prepare("
        SELECT SUM(total) as total_ventas
        FROM ventas
        WHERE DATE(fecha_emision) = :fecha AND estado = 'Emitida'
    ");
    $stmt_ventas->execute(['fecha' => $fecha]);
    $total_ventas = $stmt_ventas->fetchColumn();
    if ($total_ventas === false) {
        $total_ventas = 0;
    }

    // Calcular el total de cierre
    $total_cierre = $total_movimientos + $total_ventas;

    echo json_encode(['total_cierre' => $total_cierre]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Error en la base de datos: ' . $e->getMessage()]);
}
?>