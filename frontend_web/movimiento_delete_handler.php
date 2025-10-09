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
    // El método delete() en el modelo llama a sp_delete_movimiento que hace un DELETE físico.
    if ($movimiento->delete($id)) {
        header('Location: movimientos.php?success=Movimiento+eliminado+permanentemente');
    } else {
        throw new Exception('No se pudo eliminar el movimiento.');
    }
} catch (Exception $e) {
    header('Location: movimientos.php?error=Error+al+eliminar+el+movimiento:+' . urlencode($e->getMessage()));
}
exit();
?>