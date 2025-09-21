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

        <!-- Menu Maestros -->
        <li class="has-submenu">
            <a href="#" class="menu-toggle"><i class="bi bi-folder-fill icon"></i> <span class="text">Maestros</span><i class="bi bi-chevron-down arrow"></i></a>
            <ul class="submenu">
                <li><a href="empresas.php"><span class="text">Empresa</span></a></li>
                <li><a href="productos.php"><span class="text">Productos</span></a></li>
                <li><a href="categorias.php"><span class="text">Categorías</span></a></li>
                <li><a href="mesas.php"><span class="text">Mesas</span></a></li>
                <li><a href="tipos_documentos.php"><span class="text">Tipos de Documentos</span></a></li>
                <li><a href="series_documentos.php"><span class="text">Series Documentos</span></a></li>
                <li><a href="tipo_documento_identidad.php"><span class="text">Tipo de Documento Identidad</span></a></li>
                <li><a href="clientes.php"><span class="text">Clientes</span></a></li>
            </ul>
        </li>

        <!-- Menu Operaciones -->
        <li class="has-submenu">
            <a href="#" class="menu-toggle"><i class="bi bi-gear-fill icon"></i> <span class="text">Operaciones</span><i class="bi bi-chevron-down arrow"></i></a>
            <ul class="submenu">
                <li><a href="caja.php"><span class="text">Caja</span></a></li>
                <li><a href="movim_caja.php"><span class="text">Movim. de Caja</span></a></li>
                <li><a href="apertura_cierre.php"><span class="text">Apertura / Cierre</span></a></li>
                <li><a href="pedidos.php"><span class="text">Pedidos</span></a></li>
                <li><a href="reservas.php"><span class="text">Reservas</span></a></li>
                <li><a href="ventas.php"><span class="text">Ventas</span></a></li>
            </ul>
        </li>

        <!-- Menu Reportes -->
        <li class="has-submenu">
            <a href="#" class="menu-toggle"><i class="bi bi-graph-up icon"></i> <span class="text">Reportes</span><i class="bi bi-chevron-down arrow"></i></a>
            <ul class="submenu">
                <li><a href="reportes_dinamicos.php"><span class="text">Reportes Dinamicos</span></a></li>
            </ul>
        </li>

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

<script>
document.addEventListener('DOMContentLoaded', function() {
    const menuToggles = document.querySelectorAll('.menu-toggle');

    menuToggles.forEach(toggle => {
        toggle.addEventListener('click', function(e) {
            e.preventDefault();
            const parentLi = this.parentElement;
            const submenu = parentLi.querySelector('.submenu');

            if (submenu) {
                if (submenu.style.display === 'block') {
                    submenu.style.display = 'none';
                    parentLi.classList.remove('active');
                } else {
                    // Opcional: cerrar otros submenús abiertos
                    document.querySelectorAll('.has-submenu.active').forEach(openMenu => {
                        openMenu.classList.remove('active');
                        openMenu.querySelector('.submenu').style.display = 'none';
                    });

                    submenu.style.display = 'block';
                    parentLi.classList.add('active');
                }
            }
        });
    });
});
</script>
