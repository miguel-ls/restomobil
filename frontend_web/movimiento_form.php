<?php
session_start();
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('Location: index.php?error=Por+favor+inicie+sesión');
    exit();
}

include_once 'templates/header.php';
include_once __DIR__ . '/config.php';

$movimiento_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$movimiento_data = null;
$page_title = $movimiento_id ? 'Editar Movimiento' : 'Nuevo Movimiento';

if ($movimiento_id) {
    // Cargar datos del movimiento para editar
    $api_url = API_BASE_URL . "movimientos.php?id=" . $movimiento_id;
    $response = @file_get_contents($api_url);
    if ($response) {
        $movimiento_data = json_decode($response, true);
    }
}

?>
<div class="dashboard-container">
    <?php include_once 'templates/sidebar.php'; ?>
    <main class="main-content">
        <div class="container">
            <div class="page-header">
                <h1><?php echo $page_title; ?></h1>
                <a href="movimientos.php" class="btn btn-back">Volver a la lista</a>
            </div>

            <form id="movimiento-form" action="movimiento_handler.php" method="POST">
                <input type="hidden" id="movimiento-id" name="id" value="<?php echo $movimiento_id; ?>">

                <!-- Fila 1: Datos Generales -->
                <div class="form-row">
                    <div class="form-group">
                        <label for="fecha_movimiento">Fecha Movimiento</label>
                        <input type="date" id="fecha_movimiento" name="fecha_movimiento" value="<?php echo htmlspecialchars($movimiento_data['fecha_movimiento'] ?? date('Y-m-d')); ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="tipo_operacion">Tipo de Movimiento (E/S)</label>
                        <select id="tipo_operacion" name="tipo_operacion" required>
                            <option value="">Seleccione...</option>
                            <option value="E" <?php echo (isset($movimiento_data['tipo_movimiento']) && $movimiento_data['tipo_movimiento'] == 'E') ? 'selected' : ''; ?>>Entrada</option>
                            <option value="S" <?php echo (isset($movimiento_data['tipo_movimiento']) && $movimiento_data['tipo_movimiento'] == 'S') ? 'selected' : ''; ?>>Salida</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="codigo_movimiento">Código de Movimiento</label>
                        <select id="codigo_movimiento" name="codigo_movimiento" required></select>
                    </div>
                </div>

                <!-- Fila 2: Documento y Estado -->
                <div class="form-row">
                    <div class="form-group">
                        <label for="id_tipo_documento_venta">Tipo Documento</label>
                        <select id="id_tipo_documento_venta" name="id_tipo_documento_venta"></select>
                    </div>
                    <div class="form-group">
                        <label for="serie_documento">Serie</label>
                        <input type="text" id="serie_documento" name="serie_documento" placeholder="Ej: F001" value="<?php echo htmlspecialchars($movimiento_data['serie_documento'] ?? ''); ?>">
                    </div>
                    <div class="form-group">
                        <label for="numero_documento">Número</label>
                        <input type="text" id="numero_documento" name="numero_documento" placeholder="Ej: 001234" value="<?php echo htmlspecialchars($movimiento_data['numero_documento'] ?? ''); ?>">
                    </div>
                    <div class="form-group">
                        <label for="estado">Estado</label>
                        <select id="estado" name="estado" required>
                            <option value="Activado" <?php echo (!isset($movimiento_data['estado']) || $movimiento_data['estado'] == 'Activado') ? 'selected' : ''; ?>>Activado</option>
                            <option value="Desactivado" <?php echo (isset($movimiento_data['estado']) && $movimiento_data['estado'] == 'Desactivado') ? 'selected' : ''; ?>>Desactivado</option>
                        </select>
                    </div>
                </div>

                <!-- Fila 3: Entidad -->
                <div class="form-row">
                    <div class="form-group">
                        <label for="tipo_entidad">Tipo Entidad</label>
                        <select id="tipo_entidad" name="tipo_entidad">
                            <option value="">Seleccione...</option>
                            <option value="C" <?php echo (isset($movimiento_data['tipo_entidad']) && $movimiento_data['tipo_entidad'] == 'C') ? 'selected' : ''; ?>>Cliente</option>
                            <option value="P" <?php echo (isset($movimiento_data['tipo_entidad']) && $movimiento_data['tipo_entidad'] == 'P') ? 'selected' : ''; ?>>Proveedor</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="id_entidad">Cliente / Proveedor</label>
                        <select id="id_entidad" name="id_entidad"></select>
                    </div>
                </div>

                <!-- Detalle del Movimiento -->
                <h3>Detalle de Productos</h3>
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
                        <tbody id="detalle-movimiento-tbody"></tbody>
                    </table>
                </div>
                <button type="button" id="btn-agregar-detalle" class="btn">Agregar Producto</button>

                <div class="form-actions">
                    <button type="submit" class="btn">Guardar Movimiento</button>
                </div>
            </form>
        </div>
    </main>
</div>

<script>
document.addEventListener('DOMContentLoaded', async function() {
    const API_URL = '<?php echo API_BASE_URL; ?>';
    const initialData = <?php echo json_encode($movimiento_data); ?>;
    let tiposMovimientoCache = null;

    async function fetchData(url) {
        try {
            const response = await fetch(url);
            if (!response.ok) throw new Error(`Error de red: ${response.status}`);
            return await response.json();
        } catch (error) {
            console.error("Error al obtener datos:", error);
            return null;
        }
    }

    async function loadDropdown(selector, url, valueField, textField, selectedValue = null) {
        const select = document.querySelector(selector);
        select.innerHTML = '<option value="">Seleccione...</option>';
        const data = await fetchData(url);
        const records = Array.isArray(data) ? data : (data?.records || []);

        if (records) {
            records.forEach(item => {
                if (item.estado === 'activado' || item.estado === '1' || item.estado === 1) {
                    const option = document.createElement('option');
                    option.value = item[valueField];
                    option.textContent = item[textField];
                    if (item[valueField] == selectedValue) {
                        option.selected = true;
                    }
                    select.appendChild(option);
                }
            });
        }
    }

    async function getTiposMovimiento() {
        if (!tiposMovimientoCache) {
            const data = await fetchData(`${API_URL}tipo_movimiento.php`);
            tiposMovimientoCache = (data && Array.isArray(data)) ? data : [];
        }
        return tiposMovimientoCache;
    }

    async function filterTiposMovimiento(tipoOperacion, selectedValue = null) {
        const tipoMovSelect = document.getElementById('codigo_movimiento');
        tipoMovSelect.innerHTML = '<option value="">Seleccione...</option>';
        if (!tipoOperacion) return;

        const tipos = await getTiposMovimiento();
        const filteredTipos = tipos.filter(tm => tm.tipo === tipoOperacion && tm.estado === 'activado');

        filteredTipos.forEach(tm => {
            const option = document.createElement('option');
            option.value = tm.id;
            option.textContent = tm.descripcion;
            if (tm.id == selectedValue) {
                option.selected = true;
            }
            tipoMovSelect.appendChild(option);
        });
    }

    document.getElementById('tipo_operacion').addEventListener('change', (e) => {
        filterTiposMovimiento(e.target.value);
    });

    document.getElementById('tipo_entidad').addEventListener('change', (e) => {
        const tipo = e.target.value;
        const selectedId = (initialData && tipo === initialData.tipo_entidad) ? (initialData.id_cliente || initialData.id_proveedor) : null;
        if(tipo === 'C') {
            loadDropdown('#id_entidad', `${API_URL}clientes.php?estado=Activado`, 'id', 'nombres_apellidos', selectedId);
        } else if (tipo === 'P') {
            loadDropdown('#id_entidad', `${API_URL}proveedores.php?estado=Activado`, 'id', 'nombres_apellidos', selectedId);
        } else {
            document.getElementById('id_entidad').innerHTML = '<option value="">Seleccione...</option>';
        }
    });

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
        const itemIndex = tbody.rows.length;
        const tr = document.createElement('tr');

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

    document.getElementById('btn-agregar-detalle').addEventListener('click', () => addDetalleRow());
    document.getElementById('detalle-movimiento-tbody').addEventListener('click', (e) => {
        if (e.target.classList.contains('btn-remove-detalle')) {
            e.target.closest('tr').remove();
        }
    });

    // --- INICIALIZACIÓN ---
    await loadDropdown('#id_tipo_documento_venta', `${API_URL}tipos_documentos.php`, 'id', 'nombre', initialData?.id_tipo_documento_venta);

    if (initialData) {
        await filterTiposMovimiento(initialData.tipo_movimiento, initialData.codigo_movimiento);
        document.getElementById('tipo_entidad').dispatchEvent(new Event('change'));
    }

    if (initialData && initialData.detalle && initialData.detalle.length > 0) {
        for (const item of initialData.detalle) {
            await addDetalleRow(item);
        }
    } else if (!initialData) {
        addDetalleRow();
    }
});
</script>
