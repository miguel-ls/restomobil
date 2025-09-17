<?php
session_start();

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('Location: index.php?error=Por+favor+inicie+sesión');
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

            <div class="placeholder-content" style="text-align: center; padding: 50px; background-color: #fff; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); margin-top: 20px;">
                <h2>Módulo en Construcción</h2>
                <p>La funcionalidad para gestionar reservas estará disponible próximamente.</p>
            </div>
        </div>
    </main>
</div>

<?php
include_once 'templates/footer.php';
?>
