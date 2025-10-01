<?php
session_start();
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('Location: index.php?error=Por+favor+inicie+sesión');
    exit();
}

$page_title = 'Gestión de Ventas';
include_once 'templates/header.php';
include_once __DIR__ . '/config.php';

// --- Funciones de API ---
function fetchFromAPI($endpoint) {
    $api_url = API_BASE_URL . $endpoint;
    $ch = curl_init($api_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    $response = curl_exec($ch);
    if (curl_errno($ch)) {
        error_log("cURL Error fetching $api_url: " . curl_error($ch));
        return ['error' => 'Error de comunicación con la API.'];
    }
    curl_close($ch);
    $data = json_decode($response, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        error_log("JSON Decode Error for $api_url. Response: $response");
        return ['error' => 'Respuesta inválida de la API.'];
    }
    return $data;
}

// --- Obtener datos para filtros ---
$tipos_documento_data = fetchFromAPI('tipos_documentos.php');
$tipos_documento = $tipos_documento_data['records'] ?? [];

// --- Lógica de Filtros y Paginación ---
$is_filtering = !empty($_GET);

// Valores de paginación
$page = $_GET['page'] ?? 1;
$limit = $_GET['limit'] ?? 10;

// Valores de filtros
$default_fecha_inicio = date('Y-m-01');
$default_fecha_fin = date('Y-m-t');

$fecha_inicio_seleccionada = $_GET['fecha_inicio'] ?? ($is_filtering ? '' : $default_fecha_inicio);
$fecha_fin_seleccionada = $_GET['fecha_fin'] ?? ($is_filtering ? '' : $default_fecha_fin);
$estado_seleccionado = $_GET['estado'] ?? 'Todos';
$tipo_documento_seleccionado = $_GET['id_tipo_documento'] ?? '';
$search_query = $_GET['search'] ?? '';

// Construir los parámetros para la API
$filters = [
    'page' => $page,
    'limit' => $limit
];

if ($fecha_inicio_seleccionada) $filters['fecha_inicio'] = $fecha_inicio_seleccionada;
if ($fecha_fin_seleccionada) $filters['fecha_fin'] = $fecha_fin_seleccionada;
if ($search_query) $filters['search'] = $search_query;
if ($estado_seleccionado !== 'Todos') $filters['estado'] = $estado_seleccionado;
if ($tipo_documento_seleccionado) $filters['id_tipo_documento'] = $tipo_documento_seleccionado;


$ventas_data = fetchFromAPI('ventas.php?' . http_build_query($filters));
$ventas = $ventas_data['records'] ?? [];
$pagination = $ventas_data['pagination'] ?? null;
?>

<div class="dashboard-container">
    <?php include_once 'templates/sidebar.php'; ?>

    <main class="main-content">
        <div class="container">
            <div class="page-header">
                <h1>Historial de Ventas</h1>
                <a href="caja.php" class="btn">Ir a Caja para Pagar</a>
            </div>

            <div class="filter-container">
                <form id="filter-form" method="GET" action="ventas.php">
                    <div class="filters">
                        <input type="date" id="fecha_inicio" name="fecha_inicio" value="<?php echo htmlspecialchars($fecha_inicio_seleccionada); ?>" title="Fecha desde">
                        <input type="date" id="fecha_fin" name="fecha_fin" value="<?php echo htmlspecialchars($fecha_fin_seleccionada); ?>" title="Fecha hasta">

                        <select id="id_tipo_documento" name="id_tipo_documento" title="Tipo de Comprobante">
                            <option value="">Tipo de Comprobante</option>
                            <?php foreach ($tipos_documento as $tipo): ?>
                                <option value="<?php echo $tipo['id']; ?>" <?php echo ($tipo_documento_seleccionado == $tipo['id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($tipo['nombre']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>

                        <select id="estado" name="estado" title="Estado">
                            <option value="Todos" <?php echo ($estado_seleccionado == 'Todos') ? 'selected' : ''; ?>>Todos los Estados</option>
                            <option value="emitida" <?php echo ($estado_seleccionado == 'emitida') ? 'selected' : ''; ?>>Emitida</option>
                            <option value="anulada" <?php echo ($estado_seleccionado == 'anulada') ? 'selected' : ''; ?>>Anulada</option>
                        </select>

                        <input type="text" id="search" name="search" placeholder="Buscar por cliente, n° doc..." value="<?php echo htmlspecialchars($search_query); ?>">
                        <button type="submit" class="btn">Filtrar</button>
                        <a href="ventas.php" class="btn btn-secondary">Limpiar</a>
                    </div>
                    <div class="pagination-controls-top">
                        <select name="limit" onchange="this.form.submit()">
                            <option value="10" <?php if ($limit == 10) echo 'selected'; ?>>10 por página</option>
                            <option value="25" <?php if ($limit == 25) echo 'selected'; ?>>25 por página</option>
                            <option value="50" <?php if ($limit == 50) echo 'selected'; ?>>50 por página</option>
                        </select>
                        <?php if ($pagination && $pagination['total_records'] > 0): ?>
                        <span class="pagination-summary">
                            Mostrando <?php echo count($ventas); ?> de <?php echo $pagination['total_records']; ?> registros
                        </span>
                        <?php endif; ?>
                    </div>
                </form>
            </div>

            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Fecha Emisión</th>
                            <th>Cliente</th>
                            <th>Documento</th>
                            <th>Total</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($ventas)): ?>
                            <?php foreach ($ventas as $venta): ?>
                                <tr>
                                    <td data-label="ID"><?php echo htmlspecialchars($venta['id']); ?></td>
                                    <td data-label="Fecha Emisión"><?php echo htmlspecialchars(date("d/m/Y H:i", strtotime($venta['fecha_emision']))); ?></td>
                                    <td data-label="Cliente"><?php echo htmlspecialchars($venta['nombre_cliente'] ?? 'Varios'); ?></td>
                                    <td data-label="Documento"><?php echo htmlspecialchars($venta['tipo_documento'] . ' ' . $venta['serie'] . '-' . $venta['numero_documento']); ?></td>
                                    <td data-label="Total"><?php echo CURRENCY_SYMBOL; ?><?php echo htmlspecialchars(number_format($venta['total'], 2)); ?></td>
                                    <td data-label="Estado">
                                        <span class="status status-<?php echo htmlspecialchars($venta['estado']); ?>">
                                            <?php echo htmlspecialchars(ucfirst($venta['estado'])); ?>
                                        </span>
                                    </td>
                                    <td data-label="Acciones" class="actions-cell">
                                        <a href="venta_form.php?id=<?php echo $venta['id']; ?>" class="btn btn-sm btn-info" title="Ver Venta"><i class="bi bi-eye"></i></a>
                                        <a href="venta_edit_form.php?id=<?php echo $venta['id']; ?>" class="btn btn-sm btn-edit" title="Editar Venta"><i class="bi bi-pencil"></i></a>
                                        <?php if ($venta['estado'] === 'emitida'): ?>
                                            <button type="button" class="btn btn-sm btn-warning btn-anular" data-id="<?php echo $venta['id']; ?>" title="Anular Venta"><i class="bi bi-slash-circle"></i></button>
                                        <?php endif; ?>
                                        <button type="button" class="btn btn-sm btn-danger btn-eliminar" data-id="<?php echo $venta['id']; ?>" title="Eliminar Venta"><i class="bi bi-trash"></i></button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" class="text-center"><?php echo $ventas_data['message'] ?? 'No se encontraron ventas.'; ?></td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- Controles de Paginación Inferiores -->
            <?php if ($pagination && $pagination['total_pages'] > 1): ?>
            <div class="pagination-controls-bottom">
                <nav aria-label="Page navigation">
                    <ul class="pagination">
                        <?php
                        // Botón "Anterior"
                        if ($pagination['current_page'] > 1) {
                            $prev_page_params = http_build_query(array_merge($_GET, ['page' => $pagination['current_page'] - 1]));
                            echo "<li class='page-item'><a class='page-link' href='?$prev_page_params'>Anterior</a></li>";
                        } else {
                            echo "<li class='page-item disabled'><span class='page-link'>Anterior</span></li>";
                        }

                        // Números de página
                        for ($i = 1; $i <= $pagination['total_pages']; $i++) {
                            $page_params = http_build_query(array_merge($_GET, ['page' => $i]));
                            $active_class = ($i == $pagination['current_page']) ? 'active' : '';
                            echo "<li class='page-item $active_class'><a class='page-link' href='?$page_params'>$i</a></li>";
                        }

                        // Botón "Siguiente"
                        if ($pagination['current_page'] < $pagination['total_pages']) {
                            $next_page_params = http_build_query(array_merge($_GET, ['page' => $pagination['current_page'] + 1]));
                            echo "<li class='page-item'><a class='page-link' href='?$next_page_params'>Siguiente</a></li>";
                        } else {
                            echo "<li class='page-item disabled'><span class='page-link'>Siguiente</span></li>";
                        }
                        ?>
                    </ul>
                </nav>
            </div>
            <?php endif; ?>

        </div>
    </main>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const apiBaseUrl = '<?php echo API_BASE_URL; ?>';

    // Lógica para anular venta
    document.querySelectorAll('.btn-anular').forEach(button => {
        button.addEventListener('click', function() {
            const ventaId = this.dataset.id;
            if (confirm('¿Está seguro de que desea anular esta venta?')) {
                fetch(`${apiBaseUrl}anular_venta.php`, {
                    method: 'POST',
                    headers: {'Content-Type': 'application/json'},
                    body: JSON.stringify({ id_venta: ventaId })
                })
                .then(response => response.json().then(data => ({ status: response.status, body: data })))
                .then(result => {
                    if (result.status === 200) {
                        alert(result.body.message);
                        window.location.reload();
                    } else {
                        throw new Error(result.body.message || 'Error desconocido');
                    }
                })
                .catch(error => alert('Error al anular la venta: ' + error.message));
            }
        });
    });

    // Lógica para eliminar venta
    document.querySelectorAll('.btn-eliminar').forEach(button => {
        button.addEventListener('click', function() {
            const ventaId = this.dataset.id;
            if (confirm('¿Está seguro de que desea ELIMINAR esta venta? Esta acción es irreversible.')) {
                fetch(`${apiBaseUrl}eliminar_venta.php`, {
                    method: 'POST',
                    headers: {'Content-Type': 'application/json'},
                    body: JSON.stringify({ id_venta: ventaId })
                })
                .then(response => response.json().then(data => ({ status: response.status, body: data })))
                .then(result => {
                    if (result.status === 200) {
                        alert(result.body.message);
                        this.closest('tr').remove();
                    } else {
                        throw new Error(result.body.message || 'Error desconocido');
                    }
                })
                .catch(error => alert('Error al eliminar la venta: ' + error.message));
            }
        });
    });
});
</script>

<style>
.filter-container .filters {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
    align-items: center;
    padding-bottom: 10px;
}
.filter-container .filters select,
.filter-container .filters input {
    padding: 8px;
    border-radius: 5px;
    border: 1px solid #ccc;
    flex-grow: 1;
    flex-basis: 150px;
}
#search { flex-grow: 2; }
.filter-container .filters button,
.filter-container .filters a.btn {
    padding: 8px 15px;
    flex-grow: 0;
}
.pagination-controls-top {
    display: flex;
    justify-content: flex-start;
    align-items: center;
    gap: 20px;
    padding-bottom: 20px;
}
.pagination-controls-top select {
    padding: 8px;
    border-radius: 5px;
    border: 1px solid #ccc;
}
.pagination-summary {
    font-size: 0.9em;
    color: #555;
}
.pagination-controls-bottom {
    display: flex;
    justify-content: center;
    padding-top: 20px;
}
.pagination {
    display: flex;
    list-style: none;
    padding: 0;
    margin: 0;
    border-radius: 5px;
    overflow: hidden;
}
.page-item .page-link {
    padding: 10px 15px;
    display: block;
    color: #007bff;
    background-color: #fff;
    border: 1px solid #ddd;
    text-decoration: none;
}
.page-item:first-child .page-link {
    border-top-left-radius: 5px;
    border-bottom-left-radius: 5px;
}
.page-item:last-child .page-link {
    border-top-right-radius: 5px;
    border-bottom-right-radius: 5px;
}
.page-item.active .page-link {
    z-index: 1;
    color: #fff;
    background-color: #007bff;
    border-color: #007bff;
}
.page-item.disabled .page-link {
    color: #6c757d;
    pointer-events: none;
    background-color: #fff;
    border-color: #ddd;
}
.page-item:not(:first-child) .page-link {
    margin-left: -1px;
}
.page-item .page-link:hover {
    background-color: #e9ecef;
}
</style>