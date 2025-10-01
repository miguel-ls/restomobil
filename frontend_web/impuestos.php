<?php
session_start();
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('Location: index.php');
    exit();
}

$page_title = 'Gestión de Impuestos';
include_once 'templates/header.php';
?>

<div class="dashboard-container">
    <?php include_once 'templates/sidebar.php'; ?>
    <main class="main-content">
        <div class="container">
            <div class="page-header">
                <h1>Impuestos</h1>
                <!-- El contenido de la página se desarrollará aquí más adelante -->
            </div>
            <div class="table-container">
                <!-- Aquí se mostrará la tabla de impuestos en el futuro -->
                <p>Página de impuestos en construcción.</p>
            </div>
        </div>
    </main>
</div>