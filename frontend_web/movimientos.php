<?php
session_start();
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('Location: index.php?error=Por+favor+inicie+sesión');
    exit();
}
$page_title = 'Gestión de Movimientos';
include_once 'templates/header.php';
include_once __DIR__ . '/config.php';
?>

<div class="dashboard-container">
    <?php include_once 'templates/sidebar.php'; ?>

    <main class="main-content">
        <div class="container">
            <div class="page-header">
                <h1>Gestión de Movimientos</h1>
                <button id="btn-nuevo-movimiento" class="btn">Nuevo Movimiento</button>
            </div>

            <div id="alert-container"></div>

            <div class="filter-container">
                <form id="filter-form">
                    <div class="filters">
                        <input type="text" id="filter-input" name="filter" placeholder="Buscar por tipo, serie o número...">
                        <button type="submit" class="btn">Filtrar</button>
                    </div>
                </form>
            </div>

            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Fecha</th>
                            <th>Tipo Mov.</th>
                            <th>Documento</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="movimientos-tbody">
                        <!-- Filas de datos se insertarán aquí -->
                    </tbody>
                </table>
            </div>
            <div class="pagination" id="pagination-container">
                <!-- Controles de paginación se insertarán aquí -->
            </div>
        </div>
    </main>
</div>

<!-- Modal para Crear/Editar Movimiento -->
<div id="movimiento-modal" class="modal" style="display:none;">
    <div class="modal-content large">
        <span class="close-button">&times;</span>
        <h2 id="modal-title">Nuevo Movimiento</h2>
        <form id="movimiento-form">
            <input type="hidden" id="movimiento-id" name="id">

            <!-- Fila 1: Datos Generales -->
            <div class="form-row">
                <div class="form-group">
                    <label for="fecha_movimiento">Fecha Movimiento</label>
                    <input type="date" id="fecha_movimiento" name="fecha_movimiento" required>
                </div>
                <div class="form-group">
                    <label for="codigo_movimiento">Tipo de Movimiento</label>
                    <select id="codigo_movimiento" name="codigo_movimiento" required></select>
                </div>
                 <div class="form-group">
                    <label for="estado">Estado</label>
                    <select id="estado" name="estado" required>
                        <option value="Activado">Activado</option>
                        <option value="Desactivado">Desactivado</option>
                    </select>
                </div>
            </div>

            <!-- Fila 2: Documento -->
            <div class="form-row">
                <div class="form-group">
                    <label for="tipo_documento">Tipo Documento</label>
                    <input type="text" id="tipo_documento" name="tipo_documento" placeholder="Ej: Factura, Boleta">
                </div>
                <div class="form-group">
                    <label for="serie_documento">Serie</label>
                    <input type="text" id="serie_documento" name="serie_documento" placeholder="Ej: F001">
                </div>
                <div class="form-group">
                    <label for="numero_documento">Número</label>
                    <input type="text" id="numero_documento" name="numero_documento" placeholder="Ej: 001234">
                </div>
            </div>

            <!-- Fila 3: Entidad -->
            <div class="form-row">
                <div class="form-group">
                    <label for="tipo_entidad">Tipo Entidad</label>
                    <select id="tipo_entidad" name="tipo_entidad">
                        <option value="">Seleccione...</option>
                        <option value="C">Cliente</option>
                        <option value="P">Proveedor</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="id_entidad">Cliente / Proveedor</label>
                    <select id="id_entidad" name="id_entidad"></select>
                </div>
            </div>

            <!-- Detalle del Movimiento -->
            <h3>Detalle</h3>
            <div class="table-container">
                <table id="detalle-movimiento-table">
                    <thead>
                        <tr>
                            <th>Producto</th>
                            <th>Cantidad</th>
                            <th>Costo Unitario</th>
                            <th>Descripción</th>
                            <th>Acción</th>
                        </tr>
                    </thead>
                    <tbody id="detalle-movimiento-tbody">
                        <!-- Filas del detalle -->
                    </tbody>
                </table>
            </div>
            <button type="button" id="btn-agregar-detalle" class="btn">Agregar Producto</button>

            <div class="form-actions">
                <button type="submit" class="btn">Guardar Movimiento</button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const API_URL = '<?php echo API_BASE_URL; ?>';
    const modal = document.getElementById('movimiento-modal');
    const closeModal = modal.querySelector('.close-button');
    const form = document.getElementById('movimiento-form');
    let currentPage = 1;

    // --- CARGA INICIAL Y DE DATOS ---
    function showAlert(message, type = 'success') {
        const alertContainer = document.getElementById('alert-container');
        const alertDiv = document.createElement('div');
        alertDiv.className = `alert alert-${type}`;
        alertDiv.textContent = message;
        alertContainer.innerHTML = '';
        alertContainer.appendChild(alertDiv);
        setTimeout(() => alertDiv.remove(), 5000);
    }

    async function fetchData(url) {
        try {
            const response = await fetch(url);
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return await response.json();
        } catch (error) {
            console.error("Error al obtener datos:", error);
            showAlert(`Error de red al contactar la API: ${error.message}`, 'error');
            return null;
        }
    }

    async function loadMovimientos(page = 1, filter = '') {
        currentPage = page;
        const data = await fetchData(`${API_URL}movimientos.php?page=${page}&filter=${encodeURIComponent(filter)}`);
        const tbody = document.getElementById('movimientos-tbody');
        tbody.innerHTML = '';

        if (data && data.records && data.records.length > 0) {
            data.records.forEach(mov => {
                const tr = document.createElement('tr');
                tr.innerHTML = `
                    <td data-label="ID">${mov.id}</td>
                    <td data-label="Fecha">${mov.fecha_movimiento}</td>
                    <td data-label="Tipo Mov.">${mov.nombre_movimiento}</td>
                    <td data-label="Documento">${mov.tipo_documento || ''} ${mov.serie_documento || ''}-${mov.numero_documento || ''}</td>
                    <td data-label="Estado"><span class="status status-${mov.estado.toLowerCase()}">${mov.estado}</span></td>
                    <td data-label="Acciones" class="actions-cell">
                        <button class="btn btn-edit" data-id="${mov.id}">Editar</button>
                        <button class="btn btn-delete" data-id="${mov.id}">Anular</button>
                    </td>
                `;
                tbody.appendChild(tr);
            });
            setupPagination(data.pagination);
        } else {
            tbody.innerHTML = '<tr><td colspan="6">No se encontraron movimientos.</td></tr>';
            document.getElementById('pagination-container').innerHTML = '';
        }
    }

    function setupPagination(pagination) {
        const container = document.getElementById('pagination-container');
        container.innerHTML = '';
        if (!pagination || pagination.total_pages <= 1) return;

        if (pagination.page > 1) {
            const prev = document.createElement('a');
            prev.href = '#';
            prev.innerHTML = '&laquo; Anterior';
            prev.addEventListener('click', (e) => {
                e.preventDefault();
                loadMovimientos(pagination.page - 1, document.getElementById('filter-input').value);
            });
            container.appendChild(prev);
        }

        for (let i = 1; i <= pagination.total_pages; i++) {
            const pageLink = document.createElement('a');
            pageLink.href = '#';
            pageLink.textContent = i;
            if (i === pagination.page) {
                pageLink.classList.add('active');
            }
            pageLink.addEventListener('click', (e) => {
                e.preventDefault();
                loadMovimientos(i, document.getElementById('filter-input').value);
            });
            container.appendChild(pageLink);
        }

        if (pagination.page < pagination.total_pages) {
            const next = document.createElement('a');
            next.href = '#';
            next.innerHTML = 'Siguiente &raquo;';
            next.addEventListener('click', (e) => {
                e.preventDefault();
                loadMovimientos(pagination.page + 1, document.getElementById('filter-input').value);
            });
            container.appendChild(next);
        }
    }

    // --- LÓGICA DEL MODAL ---
    async function openModalForEdit(id) {
        const data = await fetchData(`${API_URL}movimientos.php?id=${id}`);
        if (!data) return;

        form.reset();
        document.getElementById('modal-title').textContent = 'Editar Movimiento';
        document.getElementById('movimiento-id').value = data.id;
        document.getElementById('fecha_movimiento').value = data.fecha_movimiento;
        document.getElementById('codigo_movimiento').value = data.codigo_movimiento;
        document.getElementById('estado').value = data.estado;
        document.getElementById('tipo_documento').value = data.tipo_documento;
        document.getElementById('serie_documento').value = data.serie_documento;
        document.getElementById('numero_documento').value = data.numero_documento;

        // Cargar y seleccionar entidad
        const tipoEntidadSelect = document.getElementById('tipo_entidad');
        tipoEntidadSelect.value = data.tipo_entidad;
        await loadEntidades(data.tipo_entidad); // Carga las entidades (clientes/proveedores)
        document.getElementById('id_entidad').value = data.id_entidad;

        // Cargar detalle
        const detalleTbody = document.getElementById('detalle-movimiento-tbody');
        detalleTbody.innerHTML = '';
        if(data.detalle && data.detalle.length > 0){
            data.detalle.forEach(item => addDetalleRow(item));
        }

        modal.style.display = 'block';
    }

    function openModalForNew() {
        form.reset();
        document.getElementById('modal-title').textContent = 'Nuevo Movimiento';
        document.getElementById('movimiento-id').value = '';
        document.getElementById('detalle-movimiento-tbody').innerHTML = '';
        document.getElementById('fecha_movimiento').valueAsDate = new Date();
        addDetalleRow();
        modal.style.display = 'block';
    }

    async function loadDropdowns() {
        // Cargar tipos de movimiento
        const tiposMov = await fetchData(`${API_URL}tipo_movimiento.php`);
        const tipoMovSelect = document.getElementById('codigo_movimiento');
        tipoMovSelect.innerHTML = '<option value="">Seleccione...</option>';
        if(tiposMov && tiposMov.records) {
            tiposMov.records.forEach(tm => {
                if(tm.estado == '1'){ // Solo activos
                    const option = document.createElement('option');
                    option.value = tm.id;
                    option.textContent = tm.nombre;
                    option.dataset.tipo = tm.tipo; // Guardar 'E' o 'S'
                    tipoMovSelect.appendChild(option);
                }
            });
        }
    }

    async function loadEntidades(tipo) {
        const entidadSelect = document.getElementById('id_entidad');
        entidadSelect.innerHTML = '<option value="">Seleccione...</option>';
        if (!tipo) return;

        const endpoint = tipo === 'C' ? 'clientes.php' : 'proveedores.php';
        const data = await fetchData(`${API_URL}${endpoint}?estado=Activado`);

        if (data && data.records) {
            data.records.forEach(entidad => {
                const option = document.createElement('option');
                option.value = entidad.id;
                option.textContent = entidad.nombres_apellidos || entidad.nombre; // Para cliente o proveedor
                entidadSelect.appendChild(option);
            });
        }
    }

    let productosCache = null;
    async function getProductos() {
        if (!productosCache) {
            const data = await fetchData(`${API_URL}productos.php?estado=activo`);
            productosCache = (data && data.records) ? data.records : [];
        }
        return productosCache;
    }

    async function addDetalleRow(item = {}) {
        const tbody = document.getElementById('detalle-movimiento-tbody');
        const tr = document.createElement('tr');
        const itemIndex = tbody.rows.length;

        const productos = await getProductos();
        let productoOptions = '<option value="">Seleccione producto</option>';
        productos.forEach(p => {
            productoOptions += `<option value="${p.id}" ${item.id_producto == p.id ? 'selected' : ''}>${p.nombre}</option>`;
        });

        tr.innerHTML = `
            <td><select name="detalle[${itemIndex}][id_producto]" class="producto-select" required>${productoOptions}</select></td>
            <td><input type="number" name="detalle[${itemIndex}][cantidad]" value="${item.cantidad || 1}" min="0.00001" step="any" class="cantidad-input" required></td>
            <td><input type="number" name="detalle[${itemIndex}][costo_unitario]" value="${item.costo_unitario || 0}" min="0" step="any" class="costo-input" required></td>
            <td><input type="text" name="detalle[${itemIndex}][descripcion]" value="${item.descripcion || ''}" placeholder="Descripción opcional"></td>
            <td><button type="button" class="btn btn-delete btn-remove-detalle">X</button></td>
        `;
        tbody.appendChild(tr);
    }

    // --- MANEJO DE EVENTOS ---
    document.getElementById('filter-form').addEventListener('submit', (e) => {
        e.preventDefault();
        loadMovimientos(1, document.getElementById('filter-input').value);
    });

    document.getElementById('btn-nuevo-movimiento').addEventListener('click', openModalForNew);
    closeModal.addEventListener('click', () => modal.style.display = 'none');
    window.addEventListener('click', (event) => {
        if (event.target == modal) {
            modal.style.display = 'none';
        }
    });

    document.getElementById('tipo_entidad').addEventListener('change', (e) => {
        loadEntidades(e.target.value);
    });

    document.getElementById('btn-agregar-detalle').addEventListener('click', () => addDetalleRow());
    document.getElementById('detalle-movimiento-tbody').addEventListener('click', (e) => {
        if (e.target.classList.contains('btn-remove-detalle')) {
            e.target.closest('tr').remove();
        }
    });

    document.getElementById('movimientos-tbody').addEventListener('click', async (e) => {
        const id = e.target.dataset.id;
        if (e.target.classList.contains('btn-edit')) {
            await openModalForEdit(id);
        }
        if (e.target.classList.contains('btn-delete')) {
            if (confirm('¿Está seguro de que desea anular este movimiento? Esta acción no se puede deshacer.')) {
                const response = await fetch(`${API_URL}movimientos.php?id=${id}`, { method: 'DELETE' });
                const result = await response.json();
                if (response.ok) {
                    showAlert('Movimiento anulado correctamente.');
                    loadMovimientos(currentPage, document.getElementById('filter-input').value);
                } else {
                    showAlert(result.message || 'Error al anular el movimiento.', 'error');
                }
            }
        }
    });

    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        const id = document.getElementById('movimiento-id').value;
        const formData = new FormData(form);

        // Construir el objeto de datos
        const data = {
            id_entidad: formData.get('id_entidad') ? parseInt(formData.get('id_entidad')) : null,
            tipo_entidad: formData.get('tipo_entidad') || null,
            fecha_movimiento: formData.get('fecha_movimiento'),
            codigo_movimiento: parseInt(formData.get('codigo_movimiento')),
            tipo_documento: formData.get('tipo_documento') || null,
            serie_documento: formData.get('serie_documento') || null,
            numero_documento: formData.get('numero_documento') || null,
            estado: formData.get('estado'),
            detalle: []
        };

        // Extraer datos del detalle
        const detalleTbody = document.getElementById('detalle-movimiento-tbody');
        for (let i = 0; i < detalleTbody.rows.length; i++) {
            const row = detalleTbody.rows[i];
            const productoSelect = row.querySelector('.producto-select');
            const cantidadInput = row.querySelector('.cantidad-input');
            const costoInput = row.querySelector('.costo-input');

            data.detalle.push({
                item: i + 1,
                id_producto: parseInt(productoSelect.value),
                cantidad: parseFloat(cantidadInput.value),
                costo_unitario: parseFloat(costoInput.value),
                descripcion: row.querySelector('input[type="text"]').value,
                // Asumir unidad de medida del producto (simplificación)
                codigo_unidad_medida: 'NIU'
            });
        }

        // Determinar año y periodo
        const fecha = new Date(data.fecha_movimiento);
        data.anio = fecha.getFullYear().toString();
        data.periodo = (fecha.getMonth() + 1).toString().padStart(2, '0');

        // Determinar tipo de movimiento (E/S)
        const tipoMovSelect = document.getElementById('codigo_movimiento');
        data.tipo_movimiento = tipoMovSelect.options[tipoMovSelect.selectedIndex].dataset.tipo;

        const url = id ? `${API_URL}movimientos.php?id=${id}` : `${API_URL}movimientos.php`;
        const method = id ? 'PUT' : 'POST';

        const response = await fetch(url, {
            method: method,
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data)
        });

        const result = await response.json();
        if (response.ok) {
            showAlert(result.message || 'Operación exitosa.');
            modal.style.display = 'none';
            loadMovimientos(id ? currentPage : 1, document.getElementById('filter-input').value);
        } else {
            showAlert(result.message || 'Ocurrió un error.', 'error');
        }
    });

    // --- INICIALIZACIÓN ---
    loadMovimientos();
    loadDropdowns();
});
</script>

<?php include_once 'templates/footer.php'; ?>