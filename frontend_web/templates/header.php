<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title : 'Sistema de Restaurante'; ?></title>
    <!-- Bootstrap CSS CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <!-- Bootstrap Icons CDN -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <!-- Main Stylesheet -->
    <link rel="stylesheet" href="assets/css/style.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="assets/css/modal.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="assets/css/print-ticket.css?v=<?php echo time(); ?>" media="print">
</head>
<body id="page-body">
<?php include_once __DIR__ . '/modal_alert.php'; ?>
<div class="page-wrapper">
<header class="mobile-header">
    <a href="dashboard.php" class="logo">
        <i class="bi bi-egg-fried"></i>
    </a>
    <button id="mobile-menu-toggle"><i class="bi bi-list"></i></button>
</header>
<div class="mobile-overlay" id="mobile-overlay"></div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const body = document.getElementById('page-body');

    // Toggle para el menú móvil
    const mobileMenuToggle = document.getElementById('mobile-menu-toggle');
    const overlay = document.getElementById('mobile-overlay');
    if (mobileMenuToggle && body && overlay) {
        mobileMenuToggle.addEventListener('click', function() {
            body.classList.toggle('sidebar-collapsed');
        });

        overlay.addEventListener('click', function() {
            body.classList.remove('sidebar-collapsed');
        });
    }

    // Toggle para el menú de escritorio
    const desktopToggle = document.getElementById('sidebar-toggle');
    if (desktopToggle && body) {
        desktopToggle.addEventListener('click', function() {
            body.classList.toggle('sidebar-collapsed');
        });
    }
});
</script>
