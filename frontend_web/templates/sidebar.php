<nav class="sidebar">
    <h3>Menú Principal</h3>
    <ul>
        <li><a href="dashboard.php">Inicio</a></li>
        <li><a href="productos.php">Productos</a></li>
        <li><a href="mesas.php">Mesas</a></li>
        <li><a href="pedidos.php">Pedidos</a></li>
        <li><a href="reservas.php">Reservas</a></li>
        <?php if (isset($_SESSION['user_rol']) && $_SESSION['user_rol'] == 'Administrador'): ?>
            <li><a href="usuarios.php">Usuarios</a></li>
        <?php endif; ?>
        <li><a href="logout.php">Cerrar Sesión</a></li>
    </ul>
</nav>
