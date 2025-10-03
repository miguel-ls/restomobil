<?php
session_start();

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('Location: index.php?error=Por+favor+inicie+sesión');
    exit();
}

$page_title = 'Caja - Pedidos';
include_once 'templates/header.php';
include_once __DIR__ . '/config.php';
?>

<div class="dashboard-container">
    <?php include_once 'templates/sidebar.php'; ?>

    <main class="main-content">
        <div class="container">
            <div class="page-header">
                <h1>Caja - Pedidos</h1>
                <a href="pedido_form.php?view=caja_create" class="btn">Crear Pedido Nuevo</a>
            </div>

            <div class="filter-container">
                <form id="filter-form">
                    <div class="filters">
                        <select id="filter-year" name="year"></select>
                        <select id="filter-month" name="month"></select>
                        <input type="date" id="filter-start-date" name="start_date" title="Fecha desde">
                        <input type="date" id="filter-end-date" name="end_date" title="Fecha hasta">
                        <select id="filter-status" name="status">
                            <option value="">Todos los Estados</option>
                            <option value="abierto">Abierto</option>
                            <option value="completado">Completado</option>
                            <option value="cancelado">Cancelado</option>
                            <option value="pagado">Pagado</option>
                        </select>
                        <button type="submit" class="btn">Filtrar</button>
                    </div>
                </form>
            </div>

            <?php
            if (isset($_GET['success'])) {
                echo '<p class="success-message">' . htmlspecialchars($_GET['success']) . '</p>';
            }
            if (isset($_GET['error'])) {
                echo '<p class="error-message">' . htmlspecialchars($_GET['error']) . '</p>';
            }
            ?>

            <div id="order-cards-container" class="order-cards-container">
                <!-- Las tarjetas de pedidos se cargarán aquí dinámicamente -->
            </div>
        </div>
    </main>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const API_URL = '<?php echo API_BASE_URL; ?>pedidos.php';
    const yearSelect = document.getElementById('filter-year');
    const monthSelect = document.getElementById('filter-month');
    const startDateInput = document.getElementById('filter-start-date');
    const endDateInput = document.getElementById('filter-end-date');
    const statusSelect = document.getElementById('filter-status');
    const filterForm = document.getElementById('filter-form');
    const orderCardsContainer = document.getElementById('order-cards-container');

    function populateYears() {
        const currentYear = new Date().getFullYear();
        for (let year = currentYear; year >= currentYear - 5; year--) {
            const option = document.createElement('option');
            option.value = year;
            option.textContent = year;
            yearSelect.appendChild(option);
        }
    }

    function populateMonths() {
        const months = ["Enero", "Febrero", "Marzo", "Abril", "Mayo", "Junio", "Julio", "Agosto", "Septiembre", "Octubre", "Noviembre", "Diciembre"];
        months.forEach((month, index) => {
            const option = document.createElement('option');
            option.value = index + 1;
            option.textContent = month;
            monthSelect.appendChild(option);
        });
    }

    function updateDateFields() {
        const year = yearSelect.value;
        const month = monthSelect.value;
        if (year && month) {
            const startDate = new Date(year, month - 1, 1);
            const endDate = new Date(year, month, 0);
            startDateInput.value = startDate.toISOString().split('T')[0];
            endDateInput.value = endDate.toISOString().split('T')[0];
        }
    }

    async function fetchOrders() {
        const year = yearSelect.value;
        const month = monthSelect.value;
        const startDate = startDateInput.value;
        const endDate = endDateInput.value;
        const status = statusSelect.value;

        let queryParams = `?`;
        if (status) queryParams += `estado=${status}&`;
        if (startDate) queryParams += `start_date=${startDate}&`;
        if (endDate) queryParams += `end_date=${endDate}&`;

        // Remove trailing '&' or '?'
        queryParams = queryParams.length > 1 ? queryParams.slice(0, -1) : '';

        try {
            const response = await fetch(API_URL + queryParams);
            const data = await response.json();
            renderOrders(data.records);
        } catch (error) {
            console.error('Error al obtener los pedidos:', error);
            orderCardsContainer.innerHTML = '<p>Error al cargar los datos. Por favor, intente de nuevo.</p>';
        }
    }

    function formatCustomDateTime(isoString) {
        const date = new Date(isoString);
        const day = String(date.getDate()).padStart(2, '0');
        const month = String(date.getMonth() + 1).padStart(2, '0'); // Los meses son 0-indexados
        const year = date.getFullYear();
        const hours = String(date.getHours()).padStart(2, '0');
        const minutes = String(date.getMinutes()).padStart(2, '0');
        return `${day}/${month}/${year} ${hours}:${minutes}`;
    }

    function renderOrders(orders) {
        orderCardsContainer.innerHTML = '';
        if (!orders || orders.length === 0) {
            orderCardsContainer.innerHTML = '<p>No se encontraron pedidos que coincidan con los filtros.</p>';
            return;
        }

        orders.forEach(order => {
            const card = document.createElement('div');
            card.className = 'order-card';

            const statusClass = `status-${order.estado || 'desconocido'}`;
            const statusText = (order.estado || 'desconocido').replace('_', ' ');

            let footerButtons = '';
            if (order.estado === 'completado') {
                footerButtons = `<a href="pedido_form.php?id=${order.id}&view=pago" class="btn-card btn-edit">Pagar</a>`;
            } else if (order.estado === 'cancelado') {
                footerButtons = `<a href="pedido_form.php?id=${order.id}&view=pago" class="btn-card btn-edit" style="background-color: green; color: white;">Editar</a>`;
            } else if (order.estado === 'pagado') {
                 footerButtons = `<a href="pedido_form.php?id=${order.id}&view=pago" class="btn-card btn-view">Ver Detalle</a>`;
            }

            card.innerHTML = `
                <div class="card-header">
                    <strong>Pedido #${order.id}</strong>
                    <span class="status ${statusClass}">${statusText.charAt(0).toUpperCase() + statusText.slice(1)}</span>
                </div>
                <div class="card-body">
                    <p><strong><?php echo PUNTO_VENTA; ?>:</strong> ${order.numero_mesa || 'N/A'}</p>
                    <p><strong><?php echo VENDEDOR; ?>:</strong> ${order.nombre_mozo || 'N/A'}</p>
                    <p><strong>Fecha:</strong> ${formatCustomDateTime(order.fecha_creacion)}</p>
                    <p class="total"><strong>Total:</strong> <?php echo CURRENCY_SYMBOL; ?>${parseFloat(order.total).toFixed(2)}</p>
                </div>
                <div class="card-footer">
                    ${footerButtons}
                </div>
            `;
            orderCardsContainer.appendChild(card);
        });
    }

    // Event Listeners
    yearSelect.addEventListener('change', updateDateFields);
    monthSelect.addEventListener('change', updateDateFields);
    filterForm.addEventListener('submit', function(e) {
        e.preventDefault();
        fetchOrders();
    });

    // Initial setup
    populateYears();
    populateMonths();
    const today = new Date();
    yearSelect.value = today.getFullYear();
    monthSelect.value = today.getMonth() + 1;
    statusSelect.value = 'completado'; // Estado por defecto
    updateDateFields();
    fetchOrders(); // Carga inicial
});
</script>

<style>
.filter-container .filters {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
    align-items: center;
    padding-bottom: 20px;
}
.filter-container .filters select,
.filter-container .filters input {
    padding: 8px;
    border-radius: 5px;
    border: 1px solid #ccc;
    flex-grow: 1;
    flex-basis: 150px; /* Base width before growing/shrinking */
}
.filter-container .filters button {
    padding: 8px 15px;
    border-radius: 5px;
    border: 1px solid #007bff;
    background-color: #007bff;
    color: white;
    cursor: pointer;
    flex-grow: 0;
}
</style>
