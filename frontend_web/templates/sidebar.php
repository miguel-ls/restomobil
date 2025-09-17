<nav class="sidebar">
    <div class="sidebar-header">
        <h3>Menú Principal</h3>
        <button id="sidebar-toggle" title="Contraer menú">&#9776;</button>
    </div>
    <ul>
        <li><a href="dashboard.php"><span class="icon">&#127968;</span> <span class="text">Inicio</span></a></li>
        <li><a href="pedidos.php"><span class="icon">&#128221;</span> <span class="text">Pedidos</span></a></li>
        <li><a href="reservas.php"><span class="icon">&#128197;</span> <span class="text">Reservas</span></a></li>
        <li><a href="productos.php"><span class="icon">&#128230;</span> <span class="text">Productos</span></a></li>
        <li><a href="categorias.php"><span class="icon">&#127991;</span> <span class="text">Categorías</span></a></li>
        <li><a href="mesas.php"><span class="icon">&#127869;</span> <span class="text">Mesas</span></a></li>
        <?php if (isset($_SESSION['user_rol']) && $_SESSION['user_rol'] == 'Administrador'): ?>
            <li><a href="usuarios.php"><span class="icon">&#128101;</span> <span class="text">Usuarios</span></a></li>
        <?php endif; ?>
        <li><a href="logout.php"><span class="icon">&#128682;</span> <span class="text">Cerrar Sesión</span></a></li>
    </ul>
</nav>
