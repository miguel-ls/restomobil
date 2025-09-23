<?php
session_start();

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('Location: index.php?error=Por+favor+inicie+sesión');
    exit();
}

$page_title = 'Apertura y Cierre de Caja';
include_once 'templates/header.php';
include_once __DIR__ . '/config.php';
?>

<div class="dashboard-container">
    <?php include_once 'templates/sidebar.php'; ?>

    <main class="main-content">
        <div class="container">
            <div class="page-header">
                <h1>Historial de Apertura y Cierre de Caja</h1>
                <a href="apertura_cierre_form.php" class="btn">Registrar Nuevo Movimiento</a>
            </div>

            <div class="filter-container">
                <form id="filter-form">
                    <div class="filters">
                        <input type="date" id="fecha_inicio" name="fecha_inicio" title="Fecha desde">
                        <input type="date" id="fecha_fin" name="fecha_fin" title="Fecha hasta">
                        <select id="tipo_movimiento" name="tipo_movimiento">
                            <option value="">Todos los tipos</option>
                            <option value="apertura">Apertura</option>
                            <option value="cierre">Cierre</option>
                        </select>
                        <button type="submit" class="btn">Filtrar</button>
                    </div>
                </form>
            </div>

            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>Fecha</th>
                            <th>Tipo</th>
                            <th>Importe</th>
                            <th>Descripción</th>
                            <th>Usuario</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="apertura-cierre-tbody">
                        <!-- Las filas de datos se insertarán aquí dinámicamente -->
                    </tbody>
                </table>
            </div>

            <div class="pagination-container">
                <div id="pagination-controls"></div>
                <div class="page-size-selector">
                    <label for="page-size">Registros por página:</label>
                    <select id="page-size">
                        <option value="10" selected>10</option>
                        <option value="25">25</option>
                        <option value="50">50</option>
                        <option value="100">100</option>
                    </select>
                </div>
            </div>
        </div>
    </main>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const API_URL = '<?php echo API_BASE_URL; ?>apertura_cierre.php';
    const tbody = document.getElementById('apertura-cierre-tbody');
    const filterForm = document.getElementById('filter-form');
    const paginationControls = document.getElementById('pagination-controls');
    const pageSizeSelector = document.getElementById('page-size');

    let currentPage = 1;

    async function fetchRegistros() {
        const pageSize = pageSizeSelector.value;
        const fechaInicio = document.getElementById('fecha_inicio').value;
        const fechaFin = document.getElementById('fecha_fin').value;
        const tipoMovimiento = document.getElementById('tipo_movimiento').value;

        let queryParams = `?page=${currentPage}&page_size=${pageSize}`;
        if (fechaInicio) queryParams += `&fecha_inicio=${fechaInicio}`;
        if (fechaFin) queryParams += `&fecha_fin=${fechaFin}`;
        if (tipoMovimiento) queryParams += `&tipo_movimiento=${tipoMovimiento}`;

        try {
            const response = await fetch(API_URL + queryParams);
            if (!response.ok) {
                if (response.status === 404) {
                    tbody.innerHTML = '<tr><td colspan="6">No se encontraron registros.</td></tr>';
                    updatePagination(null);
                } else {
                    throw new Error('Error en la respuesta de la red: ' + response.statusText);
                }
                return;
            }

            const data = await response.json();
            renderTable(data.records);
            updatePagination(data.pagination);

        } catch (error) {
            console.error('Error al obtener los registros:', error);
            tbody.innerHTML = '<tr><td colspan="6">Error al cargar los datos. Por favor, intente de nuevo.</td></tr>';
        }
    }

    function formatDateTime(isoString) {
        const date = new Date(isoString);
        const day = String(date.getDate()).padStart(2, '0');
        const month = String(date.getMonth() + 1).padStart(2, '0'); // Los meses son 0-indexados
        const year = date.getFullYear();
        const hours = String(date.getHours()).padStart(2, '0');
        const minutes = String(date.getMinutes()).padStart(2, '0');
        return `${day}/${month}/${year} ${hours}:${minutes}`;
    }

    function renderTable(registros) {
        tbody.innerHTML = '';
        if (!registros || registros.length === 0) {
            tbody.innerHTML = '<tr><td colspan="6">No hay registros que coincidan con los filtros.</td></tr>';
            return;
        }

        registros.forEach(reg => {
            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td data-label="Fecha">${formatDateTime(reg.fecha)}</td>
                <td data-label="Tipo">${reg.tipo_movimiento.charAt(0).toUpperCase() + reg.tipo_movimiento.slice(1)}</td>
                <td data-label="Importe"><?php echo CURRENCY_SYMBOL; ?>${parseFloat(reg.importe).toFixed(2)}</td>
                <td data-label="Descripción">${reg.descripcion || ''}</td>
                <td data-label="Usuario">${reg.usuario_nombre || 'N/A'}</td>
                <td data-label="Acciones" class="actions-cell">
                    <a href="apertura_cierre_form.php?id=${reg.id}" class="btn btn-edit">Editar</a>
                    <button class="btn btn-delete" data-id="${reg.id}">Eliminar</button>
                </td>
            `;
            tbody.appendChild(tr);
        });
    }

    function updatePagination(pagination) {
        paginationControls.innerHTML = '';
        if (!pagination || pagination.total_pages <= 1) {
            return;
        }

        const { page, total_pages } = pagination;

        const prevButton = document.createElement('button');
        prevButton.innerText = '« Anterior';
        prevButton.disabled = page <= 1;
        prevButton.addEventListener('click', () => {
            if (currentPage > 1) {
                currentPage--;
                fetchRegistros();
            }
        });
        paginationControls.appendChild(prevButton);

        for (let i = 1; i <= total_pages; i++) {
            const pageButton = document.createElement('button');
            pageButton.innerText = i;
            pageButton.classList.toggle('active', i === page);
            pageButton.addEventListener('click', () => {
                currentPage = i;
                fetchRegistros();
            });
            paginationControls.appendChild(pageButton);
        }

        const nextButton = document.createElement('button');
        nextButton.innerText = 'Siguiente »';
        nextButton.disabled = page >= total_pages;
        nextButton.addEventListener('click', () => {
            if (currentPage < total_pages) {
                currentPage++;
                fetchRegistros();
            }
        });
        paginationControls.appendChild(nextButton);
    }

    filterForm.addEventListener('submit', function(e) {
        e.preventDefault();
        currentPage = 1;
        fetchRegistros();
    });

    pageSizeSelector.addEventListener('change', function() {
        currentPage = 1;
        fetchRegistros();
    });

    tbody.addEventListener('click', function(e) {
        if (e.target.classList.contains('btn-delete')) {
            const registroId = e.target.getAttribute('data-id');
            if (confirm('¿Estás seguro de que quieres eliminar este registro?')) {
                deleteRegistro(registroId);
            }
        }
    });

    async function deleteRegistro(id) {
        try {
            const response = await fetch(`${API_URL}?id=${id}`, {
                method: 'DELETE',
                headers: { 'Content-Type': 'application/json' }
            });

            const result = await response.json();

            if (response.ok) {
                alert(result.message || 'Registro eliminado con éxito.');
                fetchRegistros();
            } else {
                alert('Error al eliminar: ' + (result.message || 'Error desconocido'));
            }

        } catch (error) {
            console.error('Error al eliminar:', error);
            alert('Ocurrió un error de red al intentar eliminar el registro.');
        }
    }

    // Carga inicial
    fetchRegistros();
});
</script>

<style>
.pagination-container {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-top: 1rem;
}
#pagination-controls button {
    margin: 0 2px;
}
#pagination-controls button.active {
    font-weight: bold;
    background-color: #007bff;
    color: white;
}
.filter-container .filters {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
    align-items: center;
}
.filter-container .filters input,
.filter-container .filters select,
.filter-container .filters button {
    flex-grow: 0;
    flex-shrink: 0;
}
.filter-container .filters input[type="date"] {
    width: auto;
    padding: 8px;
}
.filter-container .filters select {
    width: 150px;
    padding: 8px;
}
</style>
