<!-- Modal de Alerta Genérico -->
<div id="alertModal" class="modal-overlay">
    <div class="modal-content">
        <div class="modal-header">
            <h5 id="alertModalTitle" class="modal-title">Alerta</h5>
            <button type="button" class="modal-close" onclick="closeAlertModal()">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>
        <div class="modal-body">
            <p id="alertModalMessage"></p>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-primary" onclick="closeAlertModal()">OK</button>
        </div>
    </div>
</div>

<script>
function showAlert(title, message) {
    document.getElementById('alertModalTitle').textContent = title;
    document.getElementById('alertModalMessage').textContent = message;
    document.getElementById('alertModal').style.display = 'flex';
}

function closeAlertModal() {
    document.getElementById('alertModal').style.display = 'none';
}

// Cerrar el modal si se hace clic fuera del contenido
window.addEventListener('click', function(event) {
    const modal = document.getElementById('alertModal');
    if (event.target === modal) {
        closeAlertModal();
    }
});
</script>
