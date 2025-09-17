<?php
session_start();
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('Location: index.php');
    exit();
}

$page_title = 'Gestión de Reservas';
include_once 'templates/header.php';
?>

<div class="dashboard-container">
    <?php include_once 'templates/sidebar.php'; ?>
    <main class="main-content">
        <div class="container">
            <div class="page-header">
                <h1>Gestión de Reservas</h1>
            </div>
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Página en Construcción</h5>
                    <p class="card-text">La funcionalidad para gestionar reservas se encuentra actualmente en desarrollo y estará disponible próximamente.</p>
                    <a href="dashboard.php" class="btn">Volver al Inicio</a>
                </div>
            </div>
        </div>
    </main>
</div>

<?php include_once 'templates/footer.php'; ?>
