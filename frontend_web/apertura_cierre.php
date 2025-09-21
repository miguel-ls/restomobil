<?php
session_start();

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('Location: index.php?error=Por+favor+inicie+sesión');
    exit();
}

$page_title = 'Apertura / Cierre de Caja';
include_once 'templates/header.php';
?>

<div class="dashboard-container">
    <?php include_once 'templates/sidebar.php'; ?>

    <main class="main-content">
        <div class="container">
            <h1>Apertura / Cierre de Caja</h1>
            <p>El contenido de la página de apertura y cierre de caja irá aquí.</p>
        </div>
    </main>
</div>
