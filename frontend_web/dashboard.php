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

<?php
include_once 'templates/footer.php';
?>
