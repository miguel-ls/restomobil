<?php
session_start();
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('Location: index.php?error=Por+favor+inicie+sesión');
    exit();
}

$page_title = 'Editar Venta';
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

$venta_data = null;
if (isset($_GET['id'])) {
    $venta_id = intval($_GET['id']);
    $page_title = "Editar Venta #" . $venta_id;
    $venta_data = fetchFromAPI("ventas.php?id=$venta_id");
    if (isset($venta_data['error']) || !$venta_data) {
        $page_title = "Error";
        $venta_data = null;
    }
} else {
    header('Location: ventas.php');
    exit;
}
?>

<div class="dashboard-container">
    <?php include_once 'templates/sidebar.php'; ?>
    <main class="main-content">
        <div class="container">
            <div class="page-header">
                <h1><?php echo $page_title; ?></h1>
            </div>

            <?php if ($venta_data): ?>
            <form id="venta-edit-form">
                <input type="hidden" id="venta_id" name="id" value="<?php echo $venta_id; ?>">

                <div class="card">
                    <div class="card-header">
                        <h3>Datos del Comprobante</h3>
                    </div>
                    <div class="card-body">
                        <div class="form-group-row">
                            <div class="form-group">
                                <label for="fecha_emision">Fecha de Emisión</label>
                                <input type="datetime-local" id="fecha_emision" name="fecha_emision" class="form-control" value="<?php echo htmlspecialchars(date('Y-m-d\TH:i:s', strtotime($venta_data['fecha_emision']))); ?>">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card mt-4">
                    <div class="card-header">
                        <h3>Datos del Cliente (Solo Lectura)</h3>
                    </div>
                    <div class="card-body">
                        <div class="form-group">
                            <label for="nombre_cliente">Nombre del Cliente</label>
                            <input type="text" id="nombre_cliente" name="nombre_cliente" class="form-control" value="<?php echo htmlspecialchars($venta_data['nombre_cliente'] ?? 'Varios'); ?>" readonly>
                        </div>
                        <div class="form-group-row">
                            <div class="form-group">
                                <label for="ruc_cliente">RUC / DNI</label>
                                <input type="text" id="ruc_cliente" name="ruc_cliente" class="form-control" value="<?php echo htmlspecialchars($venta_data['ruc_cliente'] ?? ''); ?>" readonly>
                            </div>
                            <div class="form-group">
                                <label for="direccion_cliente">Dirección</label>
                                <input type="text" id="direccion_cliente" name="direccion_cliente" class="form-control" value="<?php echo htmlspecialchars($venta_data['direccion_cliente'] ?? ''); ?>" readonly>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card mt-4">
                    <div class="card-header">
                        <h3>Detalle de la Venta (Solo Lectura)</h3>
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

                <div class="form-actions mt-4">
                    <button type="submit" class="btn">Guardar Cambios</button>
                    <a href="ventas.php" class="btn btn-secondary">Cancelar</a>
                </div>
            </form>
            <?php else: ?>
            <div class="alert alert-danger">
                No se pudo cargar la información de la venta. Por favor, <a href="ventas.php">vuelva a intentarlo</a>.
            </div>
            <?php endif; ?>
        </div>
    </main>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('venta-edit-form');
    if (form) {
        form.addEventListener('submit', function(event) {
            event.preventDefault();

            const ventaId = document.getElementById('venta_id').value;
            const fechaEmision = document.getElementById('fecha_emision').value;

            const data = {
                id: ventaId,
                fecha_emision: fechaEmision
            };

            const apiBaseUrl = '<?php echo API_BASE_URL; ?>';

            fetch(`${apiBaseUrl}ventas.php`, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(data)
            })
            .then(response => response.json().then(data => ({ status: response.status, body: data })))
            .then(result => {
                if (result.status === 200 && result.body.success) {
                    alert('Venta actualizada correctamente.');
                    window.location.href = 'ventas.php';
                } else {
                    throw new Error(result.body.message || 'Error desconocido al actualizar la venta.');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert(`Error al actualizar la venta: ${error.message}`);
            });
        });
    }
});
</script>

<style>
.form-group-row { display: flex; gap: 20px; }
.form-group { flex-grow: 1; }
.card.mt-4 { margin-top: 1.5rem; }
.form-actions.mt-4 { margin-top: 1.5rem; }
input[readonly] { background-color: #e9ecef; opacity: 1; cursor: not-allowed; }
</style>