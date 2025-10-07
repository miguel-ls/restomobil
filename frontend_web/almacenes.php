<?php
session_start();
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('Location: index.php');
    exit();
}

$page_title = 'Gestión de Almacenes';
include_once 'templates/header.php';
include_once 'config.php'; // Para API_BASE_URL

?>

<div class="dashboard-container">
    <?php include_once 'templates/sidebar.php'; ?>
    <main class="main-content">
        <div class="container">
            <div class="page-header">
                <h1>Catálogo de Almacenes</h1>
                <a href="almacen_form.php" class="btn">Nuevo Almacén</a>
            </div>

            <!-- Filtros -->
            <div class="filter-container">
                <form id="filter-form" class="filters">
                    <div class="form-group">
                        <label for="nombre-filter">Nombre:</label>
                        <input type="text" id="nombre-filter" name="nombre" placeholder="Buscar por nombre...">
                    </div>
                    <div class="form-group">
                        <label for="estado-filter">Estado:</label>
                        <select id="estado-filter" name="estado">
                            <option value="">Todos</option>
                            <option value="1">Activo</option>
                            <option value="0">Inactivo</option>
                        </select>
                    </div>
                    <button type="submit" class="btn">Filtrar</button>
                </form>
            </div>

            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>Nombre</th>
                            <th>Estado</th>
                            <th>Fecha de Creación</th>
                            <th>Fecha de Eliminación</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="almacenes-tbody">
                        <!-- Los datos se cargarán aquí mediante JavaScript -->
                    </tbody>
                </table>
            </div>
            <div class="pagination" id="pagination-container">
                <!-- La paginación se generará aquí -->
            </div>
        </div>
    </main>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const API_URL = '<?php echo API_BASE_URL; ?>';
    const tbody = document.getElementById('almacenes-tbody');
    const paginationContainer = document.getElementById('pagination-container');
    const filterForm = document.getElementById('filter-form');

    let currentPage = 1;

    async function fetchAlmacenes(page = 1) {
        const formData = new FormData(filterForm);
        const params = new URLSearchParams();

        params.append('page', page);
        if (formData.get('nombre')) {
            params.append('nombre', formData.get('nombre'));
        }
        if (formData.get('estado') !== '') {
            params.append('estado', formData.get('estado'));
        }

        try {
            const response = await fetch(`${API_URL}almacenes.php?${params.toString()}`);
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            const data = await response.json();
            renderTable(data.records);
            renderPagination(data.pagination);
        } catch (error) {
            console.error('Error al obtener los almacenes:', error);
            tbody.innerHTML = `<tr><td colspan="5">Error al cargar los datos. ${error.message}</td></tr>`;
        }
    }

    function renderTable(almacenes) {
        tbody.innerHTML = '';
        if (almacenes.length === 0) {
            tbody.innerHTML = '<tr><td colspan="5">No se encontraron almacenes.</td></tr>';
            return;
        }

        almacenes.forEach(almacen => {
            const tr = document.createElement('tr');
            const estadoText = almacen.estado ? 'Activo' : 'Inactivo';
            const estadoClass = almacen.estado ? 'status-active' : 'status-inactive';
            const fechaCreacion = new Date(almacen.fecha_creacion).toLocaleDateString();
            const fechaEliminacion = almacen.fecha_eliminacion ? new Date(almacen.fecha_eliminacion).toLocaleDateString() : '';

            tr.innerHTML = `
                <td data-label="Nombre">${almacen.nombre}</td>
                <td data-label="Estado"><span class="status ${estadoClass}">${estadoText}</span></td>
                <td data-label="Fecha de Creación">${fechaCreacion}</td>
                <td data-label="Fecha de Eliminación">${fechaEliminacion}</td>
                <td data-label="Acciones" class="actions-cell">
                    <a href="almacen_form.php?id=${almacen.id}" class="btn btn-edit">Editar</a>
                    <button class="btn btn-delete" data-id="${almacen.id}">Eliminar</button>
                </td>
            `;
            tbody.appendChild(tr);
        });
    }

    function renderPagination(pagination) {
        paginationContainer.innerHTML = '';
        if (!pagination || pagination.total_pages <= 1) return;

        for (let i = 1; i <= pagination.total_pages; i++) {
            const pageLink = document.createElement('a');
            pageLink.href = '#';
            pageLink.innerText = i;
            pageLink.classList.add('pagination-link');
            if (i === pagination.current_page) {
                pageLink.classList.add('active');
            }
            pageLink.addEventListener('click', (e) => {
                e.preventDefault();
                currentPage = i;
                fetchAlmacenes(currentPage);
            });
            paginationContainer.appendChild(pageLink);
        }
    }

    async function deleteAlmacen(id) {
        if (!confirm('¿Estás seguro de eliminar este almacén? Esta acción es una desactivación lógica.')) {
            return;
        }

        try {
            const response = await fetch(`${API_URL}almacenes.php?id=${id}`, {
                method: 'DELETE'
            });
            const result = await response.json();
            if (response.ok) {
                alert(result.message);
                fetchAlmacenes(currentPage); // Recargar la tabla
            } else {
                throw new Error(result.message || 'Error en la respuesta del servidor');
            }
        } catch (error) {
            console.error('Error al eliminar el almacén:', error);
            alert(`No se pudo eliminar el almacén: ${error.message}`);
        }
    }

    tbody.addEventListener('click', function(e) {
        if (e.target && e.target.classList.contains('btn-delete')) {
            const id = e.target.getAttribute('data-id');
            deleteAlmacen(id);
        }
    });

    filterForm.addEventListener('submit', function(e) {
        e.preventDefault();
        currentPage = 1;
        fetchAlmacenes(currentPage);
    });

    // Carga inicial
    fetchAlmacenes(currentPage);
});
</script>