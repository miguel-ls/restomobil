<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}
$page_title = 'Tipo de Movimiento';
require_once 'templates/header.php';
?>

<div class="dashboard-container">
    <?php require_once 'templates/sidebar.php'; ?>

    <main class="main-content">
        <div class="container">
            <h1>Tipo de Movimiento</h1>
            <p>Página en construcción.</p>
        </div>
    </main>
</div>