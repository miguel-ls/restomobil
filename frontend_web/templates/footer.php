<footer class="main-footer">
    <p>&copy; <?php echo date("Y"); ?> - Sistema de Restaurante. Todos los derechos reservados.</p>
</footer>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const toggleButton = document.getElementById('sidebar-toggle');
    if (toggleButton) {
        toggleButton.addEventListener('click', function() {
            document.getElementById('page-body').classList.toggle('sidebar-collapsed');
        });
    }
});
</script>
</body>
</html>
