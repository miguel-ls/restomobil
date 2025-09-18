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
    $api_url = API_BASE_URL . "pedidos.php?id=$id";
    $ch = curl_init($api_url);
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

                        <!-- Vista de Tabla para Desktop -->
                        <div class="table-container desktop-only">
                            <table>
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
                                            <td><?php echo htmlspecialchars($item['nombre_producto']); ?></td>
                                            <td><?php echo htmlspecialchars($item['cantidad']); ?></td>
                                            <td><?php echo CURRENCY_SYMBOL; ?><?php echo htmlspecialchars(number_format($item['precio_unitario'], 2)); ?></td>
                                            <td><?php echo CURRENCY_SYMBOL; ?><?php echo htmlspecialchars(number_format($item['subtotal'], 2)); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>

                        <!-- Vista de Tarjetas para Móvil -->
                        <div class="mobile-only">
                            <?php foreach ($order['items'] as $item): ?>
                                <div class="order-item-card">
                                    <div class="card-item-header"><?php echo htmlspecialchars($item['nombre_producto']); ?></div>
                                    <div class="card-item-body">
                                        <div class="card-item-row">
                                            <span>Precio Unit.:</span>
                                            <span><?php echo CURRENCY_SYMBOL; ?><?php echo htmlspecialchars(number_format($item['precio_unitario'], 2)); ?></span>
                                        </div>
                                        <div class="card-item-row">
                                            <span>Cantidad:</span>
                                            <span><?php echo htmlspecialchars($item['cantidad']); ?></span>
                                        </div>
                                        <div class="card-item-row">
                                            <span>Subtotal:</span>
                                            <strong><?php echo CURRENCY_SYMBOL; ?><?php echo htmlspecialchars(number_format($item['subtotal'], 2)); ?></strong>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>

                        <div class="order-total-container" style="text-align: right; margin-top: 20px; font-size: 1.2em;">
                            <strong>Total del Pedido:</strong>
                            <strong><?php echo CURRENCY_SYMBOL; ?><?php echo htmlspecialchars(number_format($order['total'], 2)); ?></strong>
                        </div>
                    </div>
                </div>
            <?php else: ?>
                <p class="error-message">No se pudieron cargar los detalles del pedido. Es posible que el pedido no exista o haya sido eliminado.</p>
            <?php endif; ?>
        </div>
    </main>
</div>
