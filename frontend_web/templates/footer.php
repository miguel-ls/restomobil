<!-- Aquí se podrían añadir scripts de JavaScript en el futuro -->
    <!-- <script src="assets/js/main.js"></script> -->
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
