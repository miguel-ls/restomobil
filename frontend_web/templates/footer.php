<footer class="main-footer">
    <p>&copy; <?php echo date("Y"); ?> - Sistema de Restaurante. Todos los derechos reservados.</p>
</footer>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const toggleButton = document.getElementById('sidebar-toggle');
    const body = document.getElementById('page-body');

    // Crear el overlay para cerrar el menú en móvil
    const overlay = document.createElement('div');
    overlay.className = 'mobile-overlay';
    document.body.appendChild(overlay);

    if (toggleButton && body) {
        toggleButton.addEventListener('click', function() {
            body.classList.toggle('sidebar-collapsed');
        });

        overlay.addEventListener('click', function() {
            body.classList.remove('sidebar-collapsed');
        });
    }
});
</script>
</body>
</html>
