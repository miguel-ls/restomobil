<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}
require_once 'templates/header.php';
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-2 p-0">
            <?php require_once 'templates/sidebar.php'; ?>
        </div>
        <div class="col-10">
            <main class="main-content">
                <div class="header">
                    <h1>Inventario</h1>
                </div>
                <p>Página en construcción.</p>
            </main>
        </div>
    </div>
</div>
