<?php
session_start();
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('Location: index.php');
    exit();
}

$page_title = 'Gestión de Unidades de Medida';
include_once 'templates/header.php';
include_once 'config.php';
?>

<div class="dashboard-container">
    <?php include_once 'templates/sidebar.php'; ?>
    <main class="main-content">
        <div class="container">
            <div class="page-header">
                <h1>Catálogo de Unidades de Medida</h1>
                <a href="unidades_medida_form.php" class="btn">Nueva Unidad de Medida</a>
            </div>

            <!-- Filtros -->
            <div class="filter-container">
                <form id="filter-form" class="filters">
                    <div class="form-group">
                        <label for="filter-input">Buscar:</label>
                        <input type="text" id="filter-input" name="filter" placeholder="Código o descripción...">
                    </div>
                    <button type="submit" class="btn">Filtrar</button>
                </form>
            </div>

            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>Código</th>
                            <th>Descripción</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="unidades-medida-tbody">
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
    const tbody = document.getElementById('unidades-medida-tbody');
    const paginationContainer = document.getElementById('pagination-container');
    const filterForm = document.getElementById('filter-form');
    const filterInput = document.getElementById('filter-input');

    let currentPage = 1;

    async function fetchUnidades(page = 1) {
        const filterValue = filterInput.value.trim();
        const params = new URLSearchParams({
            page: page,
            filter: filterValue
        });

        try {
            const response = await fetch(`${API_URL}unidades_medida.php?${params.toString()}`);
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            const data = await response.json();
            renderTable(data.records);
            renderPagination(data.pagination);
        } catch (error) {
            console.error('Error al obtener las unidades de medida:', error);
            tbody.innerHTML = `<tr><td colspan="4">Error al cargar los datos. ${error.message}</td></tr>`;
        }
    }

    function renderTable(unidades) {
        tbody.innerHTML = '';
        if (unidades.length === 0) {
            tbody.innerHTML = '<tr><td colspan="4">No se encontraron unidades de medida.</td></tr>';
            return;
        }

        unidades.forEach(unidad => {
            const tr = document.createElement('tr');
            const estadoText = unidad.estado == 1 ? 'Activo' : 'Inactivo';
            const estadoClass = unidad.estado == 1 ? 'status-active' : 'status-inactive';

            tr.innerHTML = `
                <td data-label="Código">${unidad.codigo}</td>
                <td data-label="Descripción">${unidad.descripcion}</td>
                <td data-label="Estado"><span class="status ${estadoClass}">${estadoText}</span></td>
                <td data-label="Acciones" class="actions-cell">
                    <a href="unidades_medida_form.php?id=${unidad.id}" class="btn btn-edit">Editar</a>
                    <a href="unidad_medida_delete_handler.php?id=${unidad.id}" class="btn btn-delete" onclick="return confirm('¿Estás seguro de eliminar esta unidad de medida?');">Eliminar</a>
                </td>
            `;
            tbody.appendChild(tr);
        });
    }

    function renderPagination(pagination) {
        paginationContainer.innerHTML = '';
        if (pagination.total_pages <= 1) return;

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
                fetchUnidades(currentPage);
            });
            paginationContainer.appendChild(pageLink);
        }
    }

    filterForm.addEventListener('submit', function(e) {
        e.preventDefault();
        currentPage = 1;
        fetchUnidades(currentPage);
    });

    // Carga inicial
    fetchUnidades(currentPage);
});
</script>