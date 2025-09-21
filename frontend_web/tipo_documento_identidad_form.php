<?php
session_start();
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('Location: index.php?error=Por+favor+inicie+sesión');
    exit();
}

$page_title = 'Nuevo Tipo de Documento';
$is_editing = false;
$doc_id = null;

if (isset($_GET['id'])) {
    $is_editing = true;
    $doc_id = intval($_GET['id']);
    $page_title = 'Editar Tipo de Documento';
}

include_once 'templates/header.php';
?>

<div class="dashboard-container">
    <?php include_once 'templates/sidebar.php'; ?>

    <main class="main-content">
        <div class="container">
            <div class="page-header">
                <h1><?php echo $page_title; ?></h1>
                <a href="tipos_documento_identidad.php" class="btn btn-secondary">Volver al Listado</a>
            </div>

            <div class="form-container">
                <form id="documento-form">
                    <input type="hidden" id="id" name="id">

                    <div class="form-group">
                        <label for="codigo_sunat">Código SUNAT</label>
                        <input type="text" id="codigo_sunat" name="codigo_sunat">
                    </div>

                    <div class="form-group">
                        <label for="descripcion">Descripción</label>
                        <input type="text" id="descripcion" name="descripcion" required>
                    </div>

                    <div class="form-group">
                        <label for="estado">Estado</label>
                        <select id="estado" name="estado">
                            <option value="1">Activo</option>
                            <option value="0">Inactivo</option>
                        </select>
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="btn"><?php echo $is_editing ? 'Actualizar' : 'Crear'; ?></button>
                    </div>
                </form>
            </div>
        </div>
    </main>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const API_URL = 'http://localhost/restaurante/backend/api/v1/tipos_documento_identidad.php';
    const form = document.getElementById('documento-form');
    const isEditing = <?php echo json_encode($is_editing); ?>;
    const docId = isEditing ? <?php echo json_encode($doc_id); ?> : null;

    // Si estamos editando, cargar los datos del registro
    if (isEditing && docId) {
        fetch(`${API_URL}?id=${docId}`)
            .then(response => response.json())
            .then(data => {
                if (data) {
                    document.getElementById('id').value = data.id;
                    document.getElementById('codigo_sunat').value = data.codigo_sunat;
                    document.getElementById('descripcion').value = data.descripcion;
                    document.getElementById('estado').value = data.estado;
                }
            })
            .catch(error => console.error('Error al cargar los datos:', error));
    }

    // Manejar el envío del formulario
    form.addEventListener('submit', function(e) {
        e.preventDefault();

        const formData = new FormData(form);
        const data = Object.fromEntries(formData.entries());

        // Convertir estado a booleano numérico
        data.estado = parseInt(data.estado, 10);
        data.id = isEditing ? docId : null;

        const method = isEditing ? 'PUT' : 'POST';

        fetch(API_URL, {
            method: method,
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(data)
        })
        .then(response => response.json())
        .then(result => {
            // La función showAlert está definida en un script global, probablemente en header.php o similar
            showAlert(result.mensaje, 'success');
            setTimeout(() => {
                window.location.href = 'tipos_documento_identidad.php';
            }, 1500);
        })
        .catch(error => {
            console.error('Error al guardar:', error);
            showAlert('Error al guardar el registro.', 'error');
        });
    });
});
</script>
