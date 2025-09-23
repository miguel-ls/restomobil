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

            <div class="card">
                <div class="card-header">
                    <h4>Filtros</h4>
                </div>
                <div class="card-body">
                    <form method="GET" action="ventas.php" class="form-filters">
                        <div class="filter-group">
                            <label for="fecha_inicio">Desde:</label>
                            <input type="date" id="fecha_inicio" name="fecha_inicio" value="<?php echo htmlspecialchars($_GET['fecha_inicio'] ?? ''); ?>">
                        </div>
                        <div class="filter-group">
                            <label for="fecha_fin">Hasta:</label>
                            <input type="date" id="fecha_fin" name="fecha_fin" value="<?php echo htmlspecialchars($_GET['fecha_fin'] ?? ''); ?>">
                        </div>
                        <div class="filter-group">
                            <label for="search">Buscar:</label>
                            <input type="text" id="search" name="search" placeholder="Cliente, Serie, Número..." value="<?php echo htmlspecialchars($_GET['search'] ?? ''); ?>">
                        </div>
                        <div class="filter-group">
                            <button type="submit" class="btn">Filtrar</button>
                            <a href="ventas.php" class="btn btn-secondary">Limpiar</a>
                        </div>
                    </form>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table">
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
                                    <td><?php echo htmlspecialchars($venta['id']); ?></td>
                                    <td><?php echo htmlspecialchars(date("d/m/Y H:i", strtotime($venta['fecha_emision']))); ?></td>
                                    <td><?php echo htmlspecialchars($venta['nombre_cliente'] ?? 'Varios'); ?></td>
                                    <td><?php echo htmlspecialchars($venta['tipo_documento'] . ' ' . $venta['serie'] . '-' . $venta['numero_documento']); ?></td>
                                    <td><?php echo CURRENCY_SYMBOL; ?><?php echo htmlspecialchars(number_format($venta['total'], 2)); ?></td>
                                    <td>
                                        <span class="status status-<?php echo htmlspecialchars($venta['estado']); ?>">
                                            <?php echo htmlspecialchars(ucfirst($venta['estado'])); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <a href="venta_form.php?id=<?php echo $venta['id']; ?>" class="btn btn-sm btn-edit">Editar</a>
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

<style>
.form-filters {
    display: flex;
    flex-wrap: wrap;
    gap: 20px;
    align-items: flex-end;
}
.filter-group {
    display: flex;
    flex-direction: column;
}
.filter-group label {
    margin-bottom: 5px;
    font-weight: bold;
}
.filter-group input, .filter-group .btn {
    height: 38px; /* Align height with buttons */
}
</style>

<?php include_once 'templates/footer.php'; ?>
