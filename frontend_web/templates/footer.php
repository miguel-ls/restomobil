<footer class="main-footer">
    <p>&copy; <?php echo date("Y"); ?> - Sistema de Restaurante. Todos los derechos reservados.</p>
</footer>
</div> <!-- Cierre de .page-wrapper -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const desktopToggle = document.getElementById('sidebar-toggle');
    const mobileToggle = document.getElementById('mobile-menu-toggle');
    const body = document.getElementById('page-body');

    const overlay = document.createElement('div');
    overlay.className = 'mobile-overlay';
    document.body.appendChild(overlay);

    function toggleSidebar() {
        body.classList.toggle('sidebar-collapsed');
    }

    if (body) {
        if (desktopToggle) desktopToggle.addEventListener('click', toggleSidebar);
        if (mobileToggle) mobileToggle.addEventListener('click', toggleSidebar);

        overlay.addEventListener('click', function() {
            body.classList.remove('sidebar-collapsed');
        });
    }
});
</script>
</body>
</html>
