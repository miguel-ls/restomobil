<nav class="sidebar">
    <div class="sidebar-header">
        <a href="dashboard.php" class="logo">
            <i class="bi bi-egg-fried"></i>
            <span class="text">Restaurante</span>
        </a>
        <button id="sidebar-toggle" title="Contraer menú" class="desktop-toggle"><i class="bi bi-list"></i></button>
    </div>
    <ul>
        <li><a href="dashboard.php"><i class="bi bi-house-door-fill icon"></i> <span class="text">Inicio</span></a></li>
        <li><a href="pedidos.php"><i class="bi bi-receipt-cutoff icon"></i> <span class="text">Pedidos</span></a></li>
        <li><a href="reservas.php"><i class="bi bi-calendar-check-fill icon"></i> <span class="text">Reservas</span></a></li>
        <li><a href="productos.php"><i class="bi bi-box-seam-fill icon"></i> <span class="text">Productos</span></a></li>
        <li><a href="categorias.php"><i class="bi bi-tag-fill icon"></i> <span class="text">Categorías</span></a></li>
        <li><a href="mesas.php"><i class="bi bi-tablet-landscape-fill icon"></i> <span class="text">Mesas</span></a></li>
        <?php if (isset($_SESSION['user_rol']) && $_SESSION['user_rol'] == 'Administrador'): ?>
            <li><a href="usuarios.php"><i class="bi bi-people-fill icon"></i> <span class="text">Usuarios</span></a></li>
        <?php endif; ?>
    </ul>
    <div class="sidebar-footer">
        <div class="user-info">
            <i class="bi bi-person-circle icon"></i>
            <span class="text"><?php echo htmlspecialchars($_SESSION['user_nombre'] ?? 'Usuario'); ?></span>
        </div>
        <a href="logout.php" class="logout-link"><i class="bi bi-box-arrow-right icon"></i> <span class="text">Cerrar Sesión</span></a>
    </div>
</nav>
