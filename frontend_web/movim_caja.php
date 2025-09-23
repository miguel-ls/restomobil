<?php
session_start();

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('Location: index.php?error=Por+favor+inicie+sesión');
    exit();
}

$page_title = 'Movimientos de Caja';
include_once 'templates/header.php';
include_once __DIR__ . '/config.php';
?>

<div class="dashboard-container">
    <?php include_once 'templates/sidebar.php'; ?>

    <main class="main-content">
        <div class="container">
            <div class="page-header">
                <h1>Historial de Movimientos de Caja</h1>
                <a href="movimiento_caja_form.php" class="btn">Registrar Nuevo Movimiento</a>
            </div>
             <?php if (isset($_GET['success'])): ?>
                <p class="success-message"><?php echo htmlspecialchars(urldecode($_GET['success'])); ?></p>
            <?php endif; ?>
            <?php if (isset($_GET['error'])): ?>
                <p class="error-message"><?php echo htmlspecialchars(urldecode($_GET['error'])); ?></p>
            <?php endif; ?>

            <div class="filter-container">
                <form id="filter-form">
                    <div class="filters">
                        <input type="date" id="fecha_inicio" name="fecha_inicio" title="Fecha desde">
                        <input type="date" id="fecha_fin" name="fecha_fin" title="Fecha hasta">
                        <select id="tipo_movimiento" name="tipo_movimiento">
                            <option value="">Todos los tipos</option>
                            <option value="entrada">Entrada</option>
                            <option value="salida">Salida</option>
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
                    <tbody id="movimientos-tbody"></tbody>
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
    const API_URL = '<?php echo API_BASE_URL; ?>movimientos_caja.php';
    const API_URL_CIERRE = '<?php echo API_BASE_URL; ?>apertura_cierre.php';
    const tbody = document.getElementById('movimientos-tbody');
    const filterForm = document.getElementById('filter-form');
    const paginationControls = document.getElementById('pagination-controls');
    const pageSizeSelector = document.getElementById('page-size');
    const newMovementBtn = document.querySelector('a[href="movimiento_caja_form.php"]');
    let currentPage = 1;

    async function checkIfDateIsClosed(date) {
        try {
            const response = await fetch(`${API_URL_CIERRE}?action=is_date_closed&fecha=${date}`);
            const data = await response.json();
            return data.is_closed;
        } catch (error) {
            console.error('Error al verificar si la fecha está cerrada:', error);
            return true;
        }
    }

    newMovementBtn.addEventListener('click', async function(e) {
        e.preventDefault();
        const today = new Date().toISOString().slice(0, 10);
        const isClosed = await checkIfDateIsClosed(today);
        if (isClosed) {
            alert('La fecha está cerrada y no se pueden registrar nuevos movimientos.');
        } else {
            window.location.href = this.href;
        }
    });

    async function fetchMovimientos() {
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
            const data = await response.json();
            renderTable(data.records);
            updatePagination(data.pagination);
        } catch (error) {
            console.error('Error al obtener los movimientos:', error);
            tbody.innerHTML = '<tr><td colspan="6">Error al cargar los datos.</td></tr>';
        }
    }

    function formatDateTime(isoString) {
        const date = new Date(isoString);
        const day = String(date.getDate()).padStart(2, '0');
        const month = String(date.getMonth() + 1).padStart(2, '0');
        const year = date.getFullYear();
        const hours = String(date.getHours()).padStart(2, '0');
        const minutes = String(date.getMinutes()).padStart(2, '0');
        return `${day}/${month}/${year} ${hours}:${minutes}`;
    }

    function renderTable(movimientos) {
        tbody.innerHTML = '';
        if (!movimientos || movimientos.length === 0) {
            tbody.innerHTML = '<tr><td colspan="6">No hay movimientos que coincidan con los filtros.</td></tr>';
            return;
        }
        movimientos.forEach(mov => {
            const tr = document.createElement('tr');
            tr.dataset.fecha = mov.fecha.split(' ')[0];
            const importeClass = mov.tipo_movimiento === 'entrada' ? 'text-success' : 'text-danger';
            const importeSign = mov.tipo_movimiento === 'entrada' ? '+' : '-';

            tr.innerHTML = `
                <td data-label="Fecha">${formatDateTime(mov.fecha)}</td>
                <td data-label="Tipo">${mov.tipo_movimiento.charAt(0).toUpperCase() + mov.tipo_movimiento.slice(1)}</td>
                <td data-label="Importe" class="${importeClass}">${importeSign} <?php echo CURRENCY_SYMBOL; ?>${parseFloat(mov.importe).toFixed(2)}</td>
                <td data-label="Descripción">${mov.descripcion || ''}</td>
                <td data-label="Usuario">${mov.usuario_nombre || 'N/A'}</td>
                <td data-label="Acciones" class="actions-cell">
                    <a href="movimiento_caja_form.php?id=${mov.id}" class="btn btn-edit">Editar</a>
                    <button class="btn btn-delete" data-id="${mov.id}">Eliminar</button>
                </td>
            `;
            tbody.appendChild(tr);
        });
    }

    function updatePagination(pagination) {
        paginationControls.innerHTML = '';
        if (!pagination || pagination.total_pages <= 1) return;
        const { page, total_pages } = pagination;
        const prevButton = document.createElement('button');
        prevButton.innerText = '« Anterior';
        prevButton.disabled = page <= 1;
        prevButton.addEventListener('click', () => {
            if (currentPage > 1) {
                currentPage--;
                fetchMovimientos();
            }
        });
        paginationControls.appendChild(prevButton);
        for (let i = 1; i <= total_pages; i++) {
            const pageButton = document.createElement('button');
            pageButton.innerText = i;
            pageButton.classList.toggle('active', i === page);
            pageButton.addEventListener('click', () => {
                currentPage = i;
                fetchMovimientos();
            });
            paginationControls.appendChild(pageButton);
        }
        const nextButton = document.createElement('button');
        nextButton.innerText = 'Siguiente »';
        nextButton.disabled = page >= total_pages;
        nextButton.addEventListener('click', () => {
            if (currentPage < total_pages) {
                currentPage++;
                fetchMovimientos();
            }
        });
        paginationControls.appendChild(nextButton);
    }

    filterForm.addEventListener('submit', (e) => {
        e.preventDefault();
        currentPage = 1;
        fetchMovimientos();
    });

    pageSizeSelector.addEventListener('change', () => {
        currentPage = 1;
        fetchMovimientos();
    });

    tbody.addEventListener('click', async (e) => {
        const target = e.target;
        const tr = target.closest('tr');
        if (!tr) return;

        const fechaMovimiento = tr.dataset.fecha;

        if (target.classList.contains('btn-edit')) {
            e.preventDefault();
            const isClosed = await checkIfDateIsClosed(fechaMovimiento);
            if (isClosed) {
                alert('La fecha está cerrada y no se puede editar el movimiento.');
            } else {
                window.location.href = target.href;
            }
        }

        if (target.classList.contains('btn-delete')) {
            const movimientoId = target.getAttribute('data-id');
            const isClosed = await checkIfDateIsClosed(fechaMovimiento);
            if (isClosed) {
                alert('La fecha está cerrada y no se puede eliminar el movimiento.');
                return;
            }
            if (confirm('¿Estás seguro de que quieres eliminar este movimiento?')) {
                deleteMovimiento(movimientoId);
            }
        }
    });

    async function deleteMovimiento(id) {
        try {
            const response = await fetch(`${API_URL}?id=${id}`, {
                method: 'DELETE',
                headers: { 'Content-Type': 'application/json' }
            });
            const result = await response.json();
            if (response.ok) {
                alert(result.message || 'Movimiento eliminado con éxito.');
                fetchMovimientos();
            } else {
                alert('Error al eliminar: ' + (result.message || 'Error desconocido'));
            }
        } catch (error) {
            console.error('Error al eliminar:', error);
            alert('Ocurrió un error de red al intentar eliminar el movimiento.');
        }
    }

    fetchMovimientos();
});
</script>

<style>
.text-success { color: #28a745; font-weight: bold; }
.text-danger { color: #dc3545; font-weight: bold; }
.pagination-container { display: flex; justify-content: space-between; align-items: center; margin-top: 1rem; }
#pagination-controls button { margin: 0 2px; }
#pagination-controls button.active { font-weight: bold; background-color: #007bff; color: white; }
.filter-container .filters { display: flex; flex-wrap: wrap; gap: 10px; align-items: center; }
.filter-container .filters input, .filter-container .filters select, .filter-container .filters button { flex-grow: 0; flex-shrink: 0; }
.filter-container .filters input[type="date"] { width: auto; padding: 8px; }
.filter-container .filters select { width: 150px; padding: 8px; }
</style>
