<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title : 'Sistema de Restaurante'; ?></title>
    <!-- Bootstrap Icons CDN -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <!-- Main Stylesheet -->
    <link rel="stylesheet" href="assets/css/style.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="assets/css/modal.css?v=<?php echo time(); ?>">
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
