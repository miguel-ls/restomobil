<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}
$page_title = 'Reporte de Kardex';
require_once 'templates/header.php';
?>

<div class="dashboard-container">
    <?php require_once 'templates/sidebar.php'; ?>

    <main class="main-content">
        <div class="container">
            <h1>Reporte de Kardex</h1>
            <p>Página en construcción.</p>
        </div>
    </main>
</div>