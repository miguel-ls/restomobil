<?php
session_start();
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('Location: index.php');
    exit();
}

$page_title = 'Gestión de Impuestos';
include_once 'templates/header.php';
include_once 'config.php'; // Para API_BASE_URL

// Función para obtener los códigos de impuestos para el filtro
function getImpuestoCodigos() {
    $api_url = API_BASE_URL . 'impuestos.php?action=getCodigos';
    $response = file_get_contents($api_url);
    $data = json_decode($response, true);
    return $data['records'] ?? [];
}

$codigos_impuestos = getImpuestoCodigos();
?>

<div class="dashboard-container">
    <?php include_once 'templates/sidebar.php'; ?>
    <main class="main-content">
        <div class="container">
            <div class="page-header">
                <h1>Catálogo de Impuestos</h1>
                <a href="impuesto_form.php" class="btn">Nuevo Impuesto</a>
            </div>

            <!-- Filtros -->
            <div class="filter-container">
                <form id="filter-form" class="filters">
                    <div class="form-group">
                        <label for="codigo-filter">Código:</label>
                        <select id="codigo-filter" name="codigo">
                            <option value="">Todos</option>
                            <?php foreach ($codigos_impuestos as $codigo): ?>
                                <option value="<?php echo htmlspecialchars($codigo['codigo']); ?>">
                                    <?php echo htmlspecialchars($codigo['codigo']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
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
                            <th>Código</th>
                            <th>Fecha Inicial</th>
                            <th>Fecha Final</th>
                            <th>Valor (%)</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="impuestos-tbody">
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
    const tbody = document.getElementById('impuestos-tbody');
    const paginationContainer = document.getElementById('pagination-container');
    const filterForm = document.getElementById('filter-form');

    let currentPage = 1;

    async function fetchImpuestos(page = 1) {
        const formData = new FormData(filterForm);
        const params = new URLSearchParams();

        params.append('page', page);
        if (formData.get('codigo')) {
            params.append('codigo', formData.get('codigo'));
        }
        if (formData.get('estado') !== '') {
            params.append('estado', formData.get('estado'));
        }

        try {
            const response = await fetch(`${API_URL}impuestos.php?${params.toString()}`);
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            const data = await response.json();
            renderTable(data.records);
            renderPagination(data.pagination);
        } catch (error) {
            console.error('Error al obtener los impuestos:', error);
            tbody.innerHTML = `<tr><td colspan="6">Error al cargar los datos. ${error.message}</td></tr>`;
        }
    }

    function renderTable(impuestos) {
        tbody.innerHTML = '';
        if (impuestos.length === 0) {
            tbody.innerHTML = '<tr><td colspan="6">No se encontraron impuestos.</td></tr>';
            return;
        }

        impuestos.forEach(impuesto => {
            const tr = document.createElement('tr');
            const estadoText = impuesto.estado ? 'Activo' : 'Inactivo';
            const estadoClass = impuesto.estado ? 'status-active' : 'status-inactive';

            tr.innerHTML = `
                <td data-label="Código">${impuesto.codigo}</td>
                <td data-label="Fecha Inicial">${impuesto.fecha_inicial}</td>
                <td data-label="Fecha Final">${impuesto.fecha_final}</td>
                <td data-label="Valor (%)">${impuesto.valor}</td>
                <td data-label="Estado"><span class="status ${estadoClass}">${estadoText}</span></td>
                <td data-label="Acciones" class="actions-cell">
                    <a href="impuesto_form.php?id=${impuesto.id}" class="btn btn-edit">Editar</a>
                    <a href="impuesto_delete_handler.php?id=${impuesto.id}" class="btn btn-delete" onclick="return confirm('¿Estás seguro de eliminar este impuesto?');">Eliminar</a>
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
                fetchImpuestos(currentPage);
            });
            paginationContainer.appendChild(pageLink);
        }
    }

    filterForm.addEventListener('submit', function(e) {
        e.preventDefault();
        currentPage = 1;
        fetchImpuestos(currentPage);
    });

    // Carga inicial
    fetchImpuestos(currentPage);
});
</script>