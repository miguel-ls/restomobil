<?php
session_start();

// Proteger la página. Si el usuario no está logueado, redirigir a index.php
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('Location: index.php?error=Por+favor+inicie+sesión');
    exit();
}

$page_title = 'Movimientos';
include_once 'templates/header.php';
?>

<div class="dashboard-container">
    <?php include_once 'templates/sidebar.php'; ?>

    <main class="main-content">
        <div class="container">
            <div class="page-header">
                <h1>Gestión de Movimientos</h1>
            </div>
            <p>Esta página está en construcción.</p>
        </div>
    </main>
</div>