<?php
session_start();
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('Location: index.php?error=Por+favor+inicie+sesión');
    exit();
}

$page_title = 'Detalle de Venta';
include_once 'templates/header.php';
include_once __DIR__ . '/config.php';

function fetchFromAPI($endpoint)
{
    $api_url = API_BASE_URL . $endpoint;
    $ch = curl_init($api_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    $response = curl_exec($ch);
    if (curl_errno($ch)) {
        return ['error' => 'Error de comunicación con la API.'];
    }
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

                            <div class="form-group-row" style="display: flex; justify-content: center; text-align: center;">
                                <div class="form-group" style="flex-grow: 1; max-width: 400px;">
                                    <img id="logo-preview"
                                        src="<?php echo htmlspecialchars($venta_data['logo_url']); ?>"
                                        alt="Logo Preview"
                                        style="max-width: 150px; max-height: 150px; display:block; margin:0 auto 5px auto;">

                                    <p style="margin:0;"><?php echo htmlspecialchars($venta_data['nombre_largo'] ?? ''); ?></p>
                                    <p style="margin:0;">Dirección: <?php echo htmlspecialchars($venta_data['direccion'] ?? ''); ?></p>
                                    <p style="margin:0;">Telf: <?php echo htmlspecialchars($venta_data['telefonos'] ?? ''); ?></p>
                                    <p style="margin:0;">Web: <?php echo htmlspecialchars($venta_data['web'] ?? ''); ?></p>
                                    <p style="margin:0;">Mail: <?php echo htmlspecialchars($venta_data['email'] ?? ''); ?></p>
                                </div>
                            </div>

                        </div>
                        <div class="card-body">
                            <div class="form-group-row">
                                <div class="form-group" style="flex-grow: 1;">
                                    <label>Tipo de Comprobante</label>
                                    <input type="text" value="<?php echo htmlspecialchars($venta_data['tipo_documento'] ?? ''); ?>" readonly>
                                    <label>Serie</label>
                                    <input type="text" value="<?php echo htmlspecialchars($venta_data['serie'] ?? ''); ?>" readonly>
                                    <label>Número</label>
                                    <input type="text" value="<?php echo htmlspecialchars($venta_data['numero_documento'] ?? ''); ?>" readonly>
                                    <label>Fecha de Emisión</label>
                                    <input type="text" value="<?php echo htmlspecialchars(date("d/m/Y H:i", strtotime($venta_data['fecha_emision']))); ?>" readonly>

                                    <label>Nombre del Cliente</label>
                                    <input type="text" value="<?php echo htmlspecialchars($venta_data['nombre_cliente'] ?? 'Varios'); ?>" readonly>
                                    <label>RUC / DNI</label>
                                    <input type="text" value="<?php echo htmlspecialchars($venta_data['ruc_cliente'] ?? ''); ?>" readonly>
                                    <label>Dirección</label>
                                    <input type="text" value="<?php echo htmlspecialchars($venta_data['direccion_cliente'] ?? ''); ?>" readonly>

                                    <div class="card-body">
                                        <div class="table-responsive">
                                            <table class="table">
                                                <thead>
                                                    <tr>
                                                    <th style="text-align: left;">Cant.</th>    
                                                    <th style="text-align: left;">Producto</th>
                                                        
                                                        <th style="text-align: right;">P.Unit.</th>
                                                        <th style="text-align: right;">Subtotal</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php if (!empty($venta_data['items'])): ?>
                                                        <?php foreach ($venta_data['items'] as $item): ?>
                                                            <tr>
                                                                <td style="text-align: left;"><?php echo htmlspecialchars($item['cantidad']); ?></td>
                                                                <td style="text-align: left;"><?php echo htmlspecialchars($item['nombre_producto']); ?></td>

                                                                <td style="text-align: right;"><?php echo htmlspecialchars(number_format($item['precio_unitario'], 2)); ?></td>
                                                                <td style="text-align: right;"><?php echo htmlspecialchars(number_format($item['subtotal'], 2)); ?></td>
                                                            </tr>
                                                        <?php endforeach; ?>
                                                    <?php endif; ?>
                                                </tbody>
                                                <tfoot>
                                                    <tr>
                                                        <th colspan="3" style="text-align: right;">Sub Total:</th>
                                                        <th style="text-align: right;"><?php echo CURRENCY_SYMBOL; ?><?php echo htmlspecialchars(number_format($venta_data['base'], 2)); ?></th>
                                                    </tr>
                                                    <tr>
                                                        <th colspan="3" style="text-align: right;">
                                                            IGV (<?php echo htmlspecialchars(number_format($venta_data['porcentaje'], 0)); ?>%):
                                                        </th>
                                                        <th style="text-align: right;"><?php echo CURRENCY_SYMBOL; ?><?php echo htmlspecialchars(number_format($venta_data['impuesto'], 2)); ?></th>
                                                    </tr>
                                                    <tr>
                                                        <th colspan="3" style="text-align: right;">Total:</th>
                                                        <th style="text-align: right;"><?php echo CURRENCY_SYMBOL; ?><?php echo htmlspecialchars(number_format($venta_data['total'], 2)); ?></th>
                                                    </tr>
                                                </tfoot>
                                            </table>
                                        </div>
                                    </div>

                                </div>
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
    .form-group-row {
        display: flex;
        gap: 20px;
    }

    .form-group {
        flex-grow: 1;
    }

    .card.mt-4 {
        margin-top: 1.5rem;
    }

    .form-actions.mt-4 {
        margin-top: 1.5rem;
    }

    input[readonly] {
        background-color: #e9ecef;
        opacity: 1;
    }
</style>