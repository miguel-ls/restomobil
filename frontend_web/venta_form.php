<?php
session_start();
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('Location: index.php?error=Por+favor+inicie+sesión');
    exit();
}

$page_title = 'Detalle de Venta';
include_once 'templates/header.php';
include_once __DIR__ . '/config.php';

function fetchFromAPI($endpoint) {
    $api_url = API_BASE_URL . $endpoint;
    $ch = curl_init($api_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    $response = curl_exec($ch);
    if (curl_errno($ch)) { return ['error' => 'Error de comunicación con la API.']; }
    curl_close($ch);
    return json_decode($response, true);
}

$is_editing = false;
$venta_data = null;
if (isset($_GET['id'])) {
    $is_editing = true;
    $venta_id = intval($_GET['id']);
    $page_title = "Detalle de Venta #" . $venta_id;
    $venta_data = fetchFromAPI("ventas.php?id=$venta_id");
    if (isset($venta_data['error']) || !$venta_data) {
        $page_title = "Error";
        $venta_data = null;
    }
} else {
    // No se permite crear ventas desde aquí, redirigir
    header('Location: ventas.php');
    exit;
}

$is_anulada = ($venta_data && $venta_data['estado'] === 'anulada');
?>

<div class="dashboard-container">
    <?php include_once 'templates/sidebar.php'; ?>
    <main class="main-content">
        <div class="container">
            <div class="page-header">
                <h1><?php echo $page_title; ?></h1>
            </div>

            <div id="printable-receipt">
                <form id="venta-form" method="POST" action="venta_handler.php?id=<?php echo $venta_id; ?>">
                    <div class="card">
                        <div class="card-header">
                            <h3>Datos del Comprobante</h3>
                            <?php if ($is_editing && isset($venta_data['estado'])): ?>
                                <span class="status status-<?php echo htmlspecialchars($venta_data['estado']); ?>">
                                    <?php echo htmlspecialchars(ucfirst(str_replace('_', ' ', $venta_data['estado']))); ?>
                                </span>
                            <?php endif; ?>
                        </div>
                        <div class="card-body">
                            <div class="form-group-row">
                                <div class="form-group" style="flex-grow: 1;">
                                    <label>Tipo de Comprobante</label>
                                    <input type="text" value="<?php echo htmlspecialchars($venta_data['tipo_documento'] ?? ''); ?>" readonly>
                                </div>
                                <div class="form-group" style="flex-grow: 1;">
                                    <label>Serie</label>
                                    <input type="text" value="<?php echo htmlspecialchars($venta_data['serie'] ?? ''); ?>" readonly>
                                </div>
                                <div class="form-group" style="flex-grow: 1;">
                                    <label>Número</label>
                                    <input type="text" value="<?php echo htmlspecialchars($venta_data['numero_documento'] ?? ''); ?>" readonly>
                                </div>
                                <div class="form-group" style="flex-grow: 1;">
                                    <label>Fecha de Emisión</label>
                                    <input type="text" value="<?php echo htmlspecialchars(date("d/m/Y H:i", strtotime($venta_data['fecha_emision']))); ?>" readonly>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card mt-4">
                        <div class="card-header">
                            <h3>Datos del Cliente</h3>
                        </div>
                        <div class="card-body">
                            <div class="form-group">
                                <label>Nombre del Cliente</label>
                                <input type="text" value="<?php echo htmlspecialchars($venta_data['nombre_cliente'] ?? 'Varios'); ?>" readonly>
                            </div>
                            <div class="form-group-row">
                                <div class="form-group" style="flex-grow: 2;">
                                    <label>RUC / DNI</label>
                                    <input type="text" value="<?php echo htmlspecialchars($venta_data['ruc_cliente'] ?? ''); ?>" readonly>
                                </div>
                                <div class="form-group" style="flex-grow: 3;">
                                    <label>Dirección</label>
                                    <input type="text" value="<?php echo htmlspecialchars($venta_data['direccion_cliente'] ?? ''); ?>" readonly>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card mt-4">
                        <div class="card-header">
                            <h3>Detalle de la Venta</h3>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>Producto</th>
                                            <th>Cantidad</th>
                                            <th>Precio Unit.</th>
                                            <th>Subtotal</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (!empty($venta_data['items'])): ?>
                                            <?php foreach ($venta_data['items'] as $item): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($item['nombre_producto']); ?></td>
                                                <td><?php echo htmlspecialchars($item['cantidad']); ?></td>
                                                <td><?php echo CURRENCY_SYMBOL; ?><?php echo htmlspecialchars(number_format($item['precio_unitario'], 2)); ?></td>
                                                <td><?php echo CURRENCY_SYMBOL; ?><?php echo htmlspecialchars(number_format($item['subtotal'], 2)); ?></td>
                                            </tr>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </tbody>
                                    <tfoot>
                                        <tr>
                                            <th colspan="3" style="text-align: right;">Total:</th>
                                            <th><?php echo CURRENCY_SYMBOL; ?><?php echo htmlspecialchars(number_format($venta_data['total'], 2)); ?></th>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="form-actions mt-4">
                <a href="ventas.php" class="btn btn-secondary">Volver a la Lista</a>
                <?php if (!$is_anulada): ?>
                    <button type="button" class="btn" onclick="window.print();">Imprimir</button>
                <?php endif; ?>
            </div>
        </div>
    </main>
</div>

<style>
.form-group-row { display: flex; gap: 20px; }
.form-group { flex-grow: 1; }
.card.mt-4 { margin-top: 1.5rem; }
.form-actions.mt-4 { margin-top: 1.5rem; }
input[readonly] { background-color: #e9ecef; opacity: 1; }
</style>

<?php include_once 'templates/footer.php'; ?>
