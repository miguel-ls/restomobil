<!-- Aquí se podrían añadir scripts de JavaScript en el futuro -->
    <!-- <script src="assets/js/main.js"></script> -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const pageHeader = document.querySelector('.page-header');
    if (pageHeader) {
        const toggleButton = document.createElement('button');
        toggleButton.id = 'menu-toggle';
        toggleButton.innerHTML = '&#9776;'; // Hamburger icon
        toggleButton.style.display = 'inline-block'; // Make it visible

        // Insertar el botón al principio del page-header
        pageHeader.insertBefore(toggleButton, pageHeader.firstChild);

        toggleButton.addEventListener('click', function() {
            document.getElementById('page-body').classList.toggle('sidebar-collapsed');
        });
    }
});
</script>
</body>
</html>
