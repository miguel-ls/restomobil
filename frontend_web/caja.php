<?php
session_start();

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('Location: index.php?error=Por+favor+inicie+sesión');
    exit();
}

$page_title = 'Caja';
include_once 'templates/header.php';
include_once __DIR__ . '/config.php';

function getCompletedOrders() {
    // I will modify the API endpoint later to handle this properly
    $api_url = API_BASE_URL . 'pedidos.php?estado=completado,pagado';
    $ch = curl_init($api_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    $response = curl_exec($ch);
    curl_close($ch);
    return json_decode($response, true);
}

$orders_data = getCompletedOrders();
?>

<div class="dashboard-container">
    <?php include_once 'templates/sidebar.php'; ?>

    <main class="main-content">
        <div class="container">
            <div class="page-header">
                <h1>Pedidos Completados</h1>
            </div>

            <?php
            if (isset($_GET['success'])) {
                echo '<p class="success-message">' . htmlspecialchars($_GET['success']) . '</p>';
            }
            if (isset($_GET['error'])) {
                echo '<p class="error-message">' . htmlspecialchars($_GET['error']) . '</p>';
            }
            ?>

            <div class="order-cards-container">
                <?php if (isset($orders_data['records']) && !empty($orders_data['records'])): ?>
                    <?php foreach ($orders_data['records'] as $order): ?>
                        <div class="order-card">
                            <div class="card-header">
                                <strong>Pedido #<?php echo htmlspecialchars($order['id']); ?></strong>
                                <span class="status status-<?php echo htmlspecialchars($order['estado']); ?>">
                                    <?php echo htmlspecialchars(ucfirst(str_replace('_', ' ', $order['estado']))); ?>
                                </span>
                            </div>
                            <div class="card-body">
                                <p><strong>Mesa:</strong> <?php echo htmlspecialchars($order['numero_mesa'] ?? 'N/A'); ?></p>
                                <p><strong>Mozo:</strong> <?php echo htmlspecialchars($order['nombre_mozo'] ?? 'N/A'); ?></p>
                                <p><strong>Fecha:</strong> <?php echo htmlspecialchars(date("d/m/Y H:i", strtotime($order['fecha_creacion']))); ?></p>
                                <p class="total"><strong>Total:</strong> <?php echo CURRENCY_SYMBOL; ?><?php echo htmlspecialchars(number_format($order['total'], 2)); ?></p>
                            </div>
                            <div class="card-footer">
                                <?php if ($order['estado'] == 'completado'): ?>
                                    <a href="pedido_form.php?id=<?php echo $order['id']; ?>&view=pago" class="btn-card btn-edit">Pagar</a>
                                <?php else: ?>
                                    <button class="btn-card btn-edit" disabled>Pagado</button>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p>No se encontraron pedidos completados.</p>
                <?php endif; ?>
            </div>
        </div>
    </main>
</div>
