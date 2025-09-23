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

// Recoger filtros del GET
$filters = [];
if (!empty($_GET['fecha_inicio'])) $filters['fecha_inicio'] = $_GET['fecha_inicio'];
if (!empty($_GET['fecha_fin'])) $filters['fecha_fin'] = $_GET['fecha_fin'];
if (!empty($_GET['search'])) $filters['search'] = $_GET['search'];

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
                <form id="filter-form" method="GET" action="ventas.php" style="display: flex; flex-wrap: wrap; align-items: flex-end; gap: 10px; padding-bottom: 1rem;">

                    <div class="filter-group">
                        <label for="filtro_anio">Año</label>
                        <select id="filtro_anio" name="filtro_anio">
                            <?php
                                $current_year = date('Y');
                                for ($i = $current_year; $i >= $current_year - 5; $i--) {
                                    $selected = (isset($_GET['filtro_anio']) && $_GET['filtro_anio'] == $i) ? 'selected' : '';
                                    echo "<option value=\"$i\" $selected>$i</option>";
                                }
                            ?>
                        </select>
                    </div>

                    <div class="filter-group">
                        <label for="filtro_mes">Mes</label>
                        <select id="filtro_mes" name="filtro_mes">
                            <?php
                                $meses = ["01"=>"Enero", "02"=>"Febrero", "03"=>"Marzo", "04"=>"Abril", "05"=>"Mayo", "06"=>"Junio", "07"=>"Julio", "08"=>"Agosto", "09"=>"Septiembre", "10"=>"Octubre", "11"=>"Noviembre", "12"=>"Diciembre"];
                                foreach ($meses as $num => $nombre) {
                                    $selected = (isset($_GET['filtro_mes']) && $_GET['filtro_mes'] == $num) ? 'selected' : '';
                                    echo "<option value=\"$num\" $selected>$nombre</option>";
                                }
                            ?>
                        </select>
                    </div>

                    <div class="filter-group">
                        <label for="fecha_inicio">Fecha Inicio</label>
                        <input type="date" id="fecha_inicio" name="fecha_inicio" value="<?php echo htmlspecialchars($_GET['fecha_inicio'] ?? ''); ?>" title="Fecha desde">
                    </div>

                    <div class="filter-group">
                        <label for="fecha_fin">Fecha Fin</label>
                        <input type="date" id="fecha_fin" name="fecha_fin" value="<?php echo htmlspecialchars($_GET['fecha_fin'] ?? ''); ?>" title="Fecha hasta">
                    </div>

                    <div class="filter-group">
                        <label for="estado">Estado</label>
                        <select id="estado" name="estado">
                            <option value="Todos" <?php echo (isset($_GET['estado']) && $_GET['estado'] == 'Todos') ? 'selected' : ''; ?>>Todos</option>
                            <option value="emitida" <?php echo (isset($_GET['estado']) && $_GET['estado'] == 'emitida') ? 'selected' : ''; ?>>Emitida</option>
                            <option value="anulada" <?php echo (isset($_GET['estado']) && $_GET['estado'] == 'anulada') ? 'selected' : ''; ?>>Anulada</option>
                        </select>
                    </div>

                    <div class="filter-group">
                        <label for="search">Buscar</label>
                        <input type="text" id="search" name="search" placeholder="Cliente, Doc..." value="<?php echo htmlspecialchars($_GET['search'] ?? ''); ?>" style="flex: 1;">
                    </div>

                    <button type="submit" class="btn">Filtrar</button>
                    <a href="ventas.php" class="btn btn-secondary">Limpiar</a>
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
                                        <a href="venta_form.php?id=<?php echo $venta['id']; ?>" class="btn btn-sm btn-edit">Ver</a>
                                        <?php if ($venta['estado'] === 'emitida'): ?>
                                            <a href="venta_anular_handler.php?id=<?php echo $venta['id']; ?>" class="btn btn-sm btn-cancelado" onclick="return confirm('¿Está seguro de que desea anular esta venta?');">Anular</a>
                                        <?php endif; ?>
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
    const anioSelect = document.getElementById('filtro_anio');
    const mesSelect = document.getElementById('filtro_mes');
    const fechaInicioInput = document.getElementById('fecha_inicio');
    const fechaFinInput = document.getElementById('fecha_fin');

    function updateDateFields() {
        const year = anioSelect.value;
        const month = mesSelect.value;

        if (year && month) {
            // Calcular primer y último día del mes
            const primerDia = `${year}-${month}-01`;
            const ultimoDia = new Date(year, month, 0).getDate(); // El día 0 del siguiente mes es el último día del mes actual
            const fechaFin = `${year}-${month}-${ultimoDia}`;

            fechaInicioInput.value = primerDia;
            fechaFinInput.value = fechaFin;
        }
    }

    // Actualizar al cambiar año o mes
    anioSelect.addEventListener('change', updateDateFields);
    mesSelect.addEventListener('change', updateDateFields);

    // Opcional: Ejecutar al cargar la página si hay valores seleccionados
    // Esto podría sobreescribir fechas específicas que el usuario haya puesto,
    // así que lo dejamos comentado por si se prefiere un control manual.
    // updateDateFields();
});
</script>
