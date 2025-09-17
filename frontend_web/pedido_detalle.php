<?php
session_start();

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('Location: index.php?error=Por+favor+inicie+sesión');
    exit();
}

if (!isset($_GET['id'])) {
    header('Location: pedidos.php?error=No+se+proporcionó+un+ID+de+pedido.');
    exit();
}

$order_id = intval($_GET['id']);
$page_title = "Detalle del Pedido #$order_id";
include_once 'templates/header.php';
include_once __DIR__ . '/../backend/config/app_config.php';

function getOrderDetails($id) {
    $api_url = "http://localhost/restaurante_system/backend/api/v1/pedidos.php?id=$id";
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $api_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    $response = curl_exec($ch);
    $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpcode == 200) {
        return json_decode($response, true);
    }
    return null;
}

$order = getOrderDetails($order_id);

?>

<div class="dashboard-container">
    <?php include_once 'templates/sidebar.php'; ?>

    <main class="main-content">
        <div class="container">
            <div class="page-header">
                <h1><?php echo $page_title; ?></h1>
                <a href="pedidos.php" class="btn btn-secondary">Volver a la Lista</a>
            </div>

            <?php if ($order): ?>
                <div class="order-details-container">
                    <div class="order-header">
                        <h3>Información General</h3>
                        <p><strong>Mesa:</strong> <?php echo htmlspecialchars($order['numero_mesa'] ?? 'N/A'); ?></p>
                        <p><strong>Mozo:</strong> <?php echo htmlspecialchars($order['nombre_mozo'] ?? 'N/A'); ?></p>
                        <p><strong>Estado:</strong>
                            <span class="status status-<?php echo htmlspecialchars($order['estado']); ?>">
                                <?php echo htmlspecialchars(ucfirst(str_replace('_', ' ', $order['estado']))); ?>
                            </span>
                        </p>
                        <p><strong>Fecha de Creación:</strong> <?php echo htmlspecialchars(date("d/m/Y H:i:s", strtotime($order['fecha_creacion']))); ?></p>
                        <p><strong>Última Actualización:</strong> <?php echo htmlspecialchars(date("d/m/Y H:i:s", strtotime($order['fecha_actualizacion']))); ?></p>
                    </div>


                    <div class="order-items">
                        <h3>Items del Pedido</h3>
                        <div class="table-container">
                            <table id="order-detail-items-table">
                                <thead>
                                    <tr>
                                        <th>Producto</th>
                                        <th>Cantidad</th>
                                        <th>Precio Unitario</th>
                                        <th>Subtotal</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($order['items'] as $item): ?>
                                        <tr>
                                            <td data-label="Producto"><?php echo htmlspecialchars($item['nombre_producto']); ?></td>
                                            <td data-label="Cantidad"><?php echo htmlspecialchars($item['cantidad']); ?></td>
                                            <td data-label="Precio Unit."><?php echo CURRENCY_SYMBOL; ?><?php echo htmlspecialchars(number_format($item['precio_unitario'], 2)); ?></td>
                                            <td data-label="Subtotal"><?php echo CURRENCY_SYMBOL; ?><?php echo htmlspecialchars(number_format($item['subtotal'], 2)); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <td colspan="3" style="text-align: right; font-weight: bold;">Total del Pedido:</td>
                                        <td style="font-weight: bold;"><?php echo CURRENCY_SYMBOL; ?><?php echo htmlspecialchars(number_format($order['total'], 2)); ?></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
            <?php else: ?>
                <p class="error-message">No se pudieron cargar los detalles del pedido. Es posible que el pedido no exista.</p>
            <?php endif; ?>
        </div>
    </main>
</div>

<style>
.order-details-container {
    background-color: #fff;
    padding: 25px;
    border-radius: 8px;
    box-shadow: 0 2px 5px rgba(0,0,0,0.1);
}
.order-header, .order-items {
    margin-bottom: 25px;
}
.order-header h3, .order-items h3 {
    border-bottom: 2px solid var(--border-color);
    padding-bottom: 10px;
    margin-bottom: 15px;
}
.order-header p {
    margin: 5px 0;
    font-size: 16px;
}
.status {
    padding: 4px 8px;
    border-radius: 12px;
    font-size: 12px;
    font-weight: bold;
    color: #fff;
    text-transform: capitalize;
}
.status-recibido { background-color: #0d6efd; }
.status-en_preparacion { background-color: #ffc107; color: #000; }
.status-listo_para_servir { background-color: #fd7e14; }
.status-servido { background-color: #198754; }
.status-pagado { background-color: #20c997; }
.status-cancelado { background-color: #dc3545; }
</style>

<?php
include_once 'templates/footer.php';
?>
