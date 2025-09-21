<?php
session_start();

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('Location: index.php?error=Por+favor+inicie+sesión');
    exit();
}

$page_title = 'Tipos de Documento de Identidad';
include_once 'templates/header.php';
?>

<div class="dashboard-container">
    <?php include_once 'templates/sidebar.php'; ?>

    <main class="main-content">
        <div class="container">
            <h1>Tipos de Documento de Identidad</h1>
            <p>El contenido de la página de tipos de documento de identidad irá aquí.</p>
        </div>
    </main>
</div>
