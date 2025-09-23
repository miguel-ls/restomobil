<?php
session_start();
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('Location: index.php?error=Por+favor+inicie+sesión');
    exit();
}

$page_title = 'Gestión de Ventas';
include_once 'templates/header.php';
include_once __DIR__ . '/config.php';

function getVentas($filters = []) {
    $api_url = API_BASE_URL . 'ventas.php';
    if (!empty($filters)) {
        $api_url .= '?' . http_build_query($filters);
    }
    $ch = curl_init($api_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    $response = curl_exec($ch);
    if (curl_errno($ch)) {
        return ['error' => 'Error de comunicación con la API: ' . curl_error($ch)];
    }
    curl_close($ch);
    return json_decode($response, true);
}

// --- Lógica de Filtros ---
$is_filtering = !empty($_GET);

// Definir valores por defecto para los filtros
$default_anio = date('Y');
$default_mes = date('m');
$default_fecha_inicio = date('Y-m-01');
$default_fecha_fin = date('Y-m-t');

// Usar valores por defecto solo si NO hay filtros en la URL
$anio_seleccionado = $_GET['filtro_anio'] ?? ($is_filtering ? '' : $default_anio);
$mes_seleccionado = $_GET['filtro_mes'] ?? ($is_filtering ? '' : $default_mes);
$fecha_inicio_seleccionada = $_GET['fecha_inicio'] ?? ($is_filtering ? '' : $default_fecha_inicio);
$fecha_fin_seleccionada = $_GET['fecha_fin'] ?? ($is_filtering ? '' : $default_fecha_fin);
$estado_seleccionado = $_GET['estado'] ?? 'Todos';
$search_query = $_GET['search'] ?? '';

// Recoger filtros para la API
$filters = [];
// Si el usuario no está filtrando activamente, usamos los defaults
if (!$is_filtering) {
    $filters['fecha_inicio'] = $default_fecha_inicio;
    $filters['fecha_fin'] = $default_fecha_fin;
} else {
    // Si está filtrando, usamos los valores de la URL
    if ($fecha_inicio_seleccionada) $filters['fecha_inicio'] = $fecha_inicio_seleccionada;
    if ($fecha_fin_seleccionada) $filters['fecha_fin'] = $fecha_fin_seleccionada;
    if ($search_query) $filters['search'] = $search_query;
    if ($estado_seleccionado !== 'Todos') $filters['estado'] = $estado_seleccionado;
}


$ventas_data = getVentas($filters);
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
                        <select id="filtro_anio" name="filtro_anio" title="Año">
                            <option value="">Año</option>
                            <?php
                                $current_year = date('Y');
                                for ($i = $current_year; $i >= $current_year - 5; $i--) {
                                    $selected = ($anio_seleccionado == $i) ? 'selected' : '';
                                    echo "<option value=\"$i\" $selected>$i</option>";
                                }
                            ?>
                        </select>
                        <select id="filtro_mes" name="filtro_mes" title="Mes">
                            <option value="">Mes</option>
                            <?php
                                $meses = ["01"=>"Enero", "02"=>"Febrero", "03"=>"Marzo", "04"=>"Abril", "05"=>"Mayo", "06"=>"Junio", "07"=>"Julio", "08"=>"Agosto", "09"=>"Septiembre", "10"=>"Octubre", "11"=>"Noviembre", "12"=>"Diciembre"];
                                foreach ($meses as $num => $nombre) {
                                    $selected = ($mes_seleccionado == $num) ? 'selected' : '';
                                    echo "<option value=\"$num\" $selected>$nombre</option>";
                                }
                            ?>
                        </select>
                        <input type="date" id="fecha_inicio" name="fecha_inicio" value="<?php echo htmlspecialchars($fecha_inicio_seleccionada); ?>" title="Fecha desde">
                        <input type="date" id="fecha_fin" name="fecha_fin" value="<?php echo htmlspecialchars($fecha_fin_seleccionada); ?>" title="Fecha hasta">
                        <select id="estado" name="estado" title="Estado">
                            <option value="Todos" <?php echo ($estado_seleccionado == 'Todos') ? 'selected' : ''; ?>>Todos los Estados</option>
                            <option value="emitida" <?php echo ($estado_seleccionado == 'emitida') ? 'selected' : ''; ?>>Emitida</option>
                            <option value="anulada" <?php echo ($estado_seleccionado == 'anulada') ? 'selected' : ''; ?>>Anulada</option>
                        </select>
                        <input type="text" id="search" name="search" placeholder="Buscar..." value="<?php echo htmlspecialchars($search_query); ?>">
                        <button type="submit" class="btn">Filtrar</button>
                        <a href="ventas.php" class="btn btn-secondary">Limpiar</a>
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
                        <?php if (isset($ventas_data['records']) && !empty($ventas_data['records'])): ?>
                            <?php foreach ($ventas_data['records'] as $venta): ?>
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
                                        <a href="venta_form.php?id=<?php echo $venta['id']; ?>" class="btn btn-sm btn-edit" title="Ver Venta"><i class="bi bi-eye"></i></a>
                                        <?php if ($venta['estado'] === 'emitida'): ?>
                                            <button type="button" class="btn btn-sm btn-cancelado btn-anular" data-id="<?php echo $venta['id']; ?>" title="Anular Venta"><i class="bi bi-slash-circle"></i></button>
                                        <?php endif; ?>
                                        <button type="button" class="btn btn-sm btn-delete btn-eliminar" data-id="<?php echo $venta['id']; ?>" title="Eliminar Venta"><i class="bi bi-trash"></i></button>
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
        </div>
    </main>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Lógica de filtros existente
    const anioSelect = document.getElementById('filtro_anio');
    const mesSelect = document.getElementById('filtro_mes');
    const fechaInicioInput = document.getElementById('fecha_inicio');
    const fechaFinInput = document.getElementById('fecha_fin');

    function updateDateFields() {
        const year = anioSelect.value;
        const month = mesSelect.value;
        if (year && month) {
            const primerDia = `${year}-${month}-01`;
            const ultimoDia = new Date(year, month, 0).getDate();
            const fechaFin = `${year}-${month}-${ultimoDia}`;
            fechaInicioInput.value = primerDia;
            fechaFinInput.value = fechaFin;
        }
    }
    anioSelect.addEventListener('change', updateDateFields);
    mesSelect.addEventListener('change', updateDateFields);

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
                        const row = this.closest('tr');
                        const statusCell = row.querySelector('.status');
                        if (statusCell) {
                            statusCell.classList.remove('status-emitida');
                            statusCell.classList.add('status-cancelado');
                            statusCell.textContent = 'Anulada';
                        }
                        this.remove(); // Eliminar el botón de anular
                        alert(result.body.message);
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
                        this.closest('tr').remove();
                        alert(result.body.message);
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
    padding-bottom: 20px;
}
.filter-container .filters select,
.filter-container .filters input {
    padding: 8px;
    border-radius: 5px;
    border: 1px solid #ccc;
    flex-grow: 1;
    flex-basis: 130px; /* Ancho base para la mayoría */
}
#filtro_anio {
    flex-grow: 0;
    flex-basis: 90px; /* Ancho reducido para el año */
}
#filtro_mes {
    flex-grow: 0.8;
    flex-basis: 120px;
}
#search {
    flex-grow: 2; /* Más espacio para la búsqueda */
}
.filter-container .filters button,
.filter-container .filters a.btn {
    padding: 8px 15px;
    border-radius: 5px;
    border: 1px solid #007bff;
    background-color: #007bff;
    color: white;
    cursor: pointer;
    flex-grow: 0;
    text-decoration: none;
}
.filter-container .filters a.btn-secondary {
    background-color: #6c757d;
    border-color: #6c757d;
}
</style>
