<?php
session_start();
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('Location: index.php?error=Por+favor+inicie+sesión');
    exit();
}

$page_title = 'Tipos de Documento de Identidad';
include_once 'templates/header.php';
?>

<div class="dashboard-container">
    <?php include_once 'templates/sidebar.php'; ?>

    <main class="main-content">
        <div class="container">
            <div class="page-header">
                <h1>Gestión de Tipos de Documento de Identidad</h1>
                <a href="tipo_documento_identidad_form.php" class="btn">
                    <i class="bi bi-plus-lg"></i> Nuevo
                </a>
            </div>

            <div class="filter-container">
                <input type="text" id="search-input" class="form-control" placeholder="Buscar por código o descripción...">
            </div>

            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>Código SUNAT</th>
                            <th>Descripción</th>
                            <th>Estado</th>
                            <th class="actions-cell">Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="documentos-tbody">
                        <!-- Las filas se insertarán aquí dinámicamente -->
                    </tbody>
                </table>
            </div>
        </div>
    </main>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const API_URL = 'http://localhost/restaurante/backend/api/v1/tipos_documento_identidad.php';
    const tbody = document.getElementById('documentos-tbody');
    const searchInput = document.getElementById('search-input');

    let debounceTimer;

    function cargarDocumentos(searchTerm = '') {
        const url = searchTerm ? `${API_URL}?s=${encodeURIComponent(searchTerm)}` : API_URL;

        fetch(url)
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                tbody.innerHTML = ''; // Limpiar el cuerpo de la tabla
                if (data && data.length > 0) {
                    data.forEach(doc => {
                        const tr = document.createElement('tr');
                        const estadoText = doc.estado == 1 ? 'Activo' : 'Inactivo';
                        const estadoClass = doc.estado == 1 ? 'status-activo' : 'status-inactivo';

                        tr.innerHTML = `
                            <td data-label="Código SUNAT">${doc.codigo_sunat}</td>
                            <td data-label="Descripción">${doc.descripcion}</td>
                            <td data-label="Estado"><span class="status ${estadoClass}">${estadoText}</span></td>
                            <td class="actions-cell" data-label="Acciones">
                                <a href="tipo_documento_identidad_form.php?id=${doc.id}" class="btn btn-edit"><i class="bi bi-pencil-fill"></i></a>
                                <button class="btn btn-delete" data-id="${doc.id}"><i class="bi bi-trash-fill"></i></button>
                            </td>
                        `;
                        tbody.appendChild(tr);
                    });
                } else {
                    const tr = document.createElement('tr');
                    tr.innerHTML = `<td colspan="4" style="text-align:center;">${data.mensaje || 'No se encontraron resultados.'}</td>`;
                    tbody.appendChild(tr);
                }
            })
            .catch(error => {
                console.error('Error al cargar los datos:', error);
                tbody.innerHTML = `<td colspan="4" style="text-align:center;">Error al cargar los datos.</td>`;
            });
    }

    // Manejar el evento de clic para eliminar
    tbody.addEventListener('click', function(e) {
        if (e.target.closest('.btn-delete')) {
            const button = e.target.closest('.btn-delete');
            const id = button.dataset.id;

            if (confirm('¿Estás seguro de que quieres eliminar este registro?')) {
                fetch(API_URL, {
                    method: 'DELETE',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ id: id })
                })
                .then(response => response.json())
                .then(data => {
                    showAlert(data.mensaje, 'success');
                    cargarDocumentos(searchInput.value); // Recargar la lista
                })
                .catch(error => {
                    console.error('Error al eliminar:', error);
                    showAlert('Error al eliminar el registro.', 'error');
                });
            }
        }
    });

    // Manejar el filtro de búsqueda
    searchInput.addEventListener('keyup', function() {
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(() => {
            cargarDocumentos(this.value);
        }, 300); // Espera 300ms después de que el usuario deja de escribir
    });

    // Carga inicial
    cargarDocumentos();
});
</script>
