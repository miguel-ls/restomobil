<?php
session_start();

// Proteger la página. Si el usuario no está logueado, redirigir a index.php
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('Location: index.php?error=Por+favor+inicie+sesión');
    exit();
}

$page_title = 'Dashboard';
include_once 'templates/header.php';
?>

<div class="dashboard-container">
    <?php include_once 'templates/sidebar.php'; ?>

    <main class="main-content">
        <div class="container">
            <h1>Bienvenido al Sistema</h1>
            <p>Seleccione una opción del menú para comenzar a gestionar el restaurante.</p>

            <!-- Aquí se cargará el contenido dinámico de las otras secciones -->

        </div>
    </main>
</div>

<style>
.dashboard-container {
    display: flex;
    height: 100vh;
}
.sidebar {
    width: 250px;
    background-color: #343a40; /* Un azul oscuro para la barra lateral */
    color: var(--light-text);
    padding: 20px;
}
.sidebar h3 {
    color: var(--light-text);
    text-align: center;
    border-bottom: 1px solid #495057;
    padding-bottom: 15px;
}
.sidebar ul {
    list-style-type: none;
    padding: 0;
    margin: 0;
}
.sidebar ul li a {
    display: block;
    color: var(--light-text);
    text-decoration: none;
    padding: 15px 10px;
    border-radius: 5px;
    transition: background-color 0.3s ease;
}
.sidebar ul li a:hover {
    background-color: #495057;
}
.main-content {
    flex-grow: 1;
    padding: 20px;
    overflow-y: auto; /* Para permitir scroll si el contenido es largo */
}
</style>

