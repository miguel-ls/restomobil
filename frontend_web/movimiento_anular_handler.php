<?php
session_start();
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('Location: index.php?error=Por+favor+inicie+sesión');
    exit();
}

include_once __DIR__ . '/../backend/config/database.php';
include_once __DIR__ . '/../backend/models/Movimiento.php';

if (!isset($_GET['id']) || empty($_GET['id'])) {
    header('Location: movimientos.php?error=ID+de+movimiento+no+proporcionado');
    exit();
}

$id = intval($_GET['id']);

try {
    $movimiento = new Movimiento();
    if ($movimiento->anular($id)) {
        header('Location: movimientos.php?success=Movimiento+anulado+correctamente');
    } else {
        throw new Exception('No se pudo anular el movimiento.');
    }
} catch (Exception $e) {
    header('Location: movimientos.php?error=Error+al+anular+el+movimiento:+' . urlencode($e->getMessage()));
}
exit();
?>