<?php
session_start();

// Esta página requiere que el usuario esté logueado
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('Location: index.php?error=Por+favor+inicie+sesión');
    exit();
}

$page_title = 'Gestión de Pedidos';
include_once 'templates/header.php';
// Incluir configuraciones como el símbolo de la moneda
include_once __DIR__ . '/../backend/config/app_config.php';

// Función para obtener los pedidos desde la API
function getOrders() {
    $api_url = 'http://localhost/restaurante_system/backend/api/v1/pedidos.php';
    $ch = curl_init($api_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    $response = curl_exec($ch);
    curl_close($ch);
    return json_decode($response, true);
}

$orders_data = getOrders();
?>

<div class="dashboard-container">
    <?php include_once 'templates/sidebar.php'; ?>

    <main class="main-content">
        <div class="container">
            <div class="page-header">
                <h1>Historial de Pedidos</h1>
                <a href="pedido_form.php" class="btn">Crear Pedido Nuevo</a>
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
                                <a href="pedido_detalle.php?id=<?php echo $order['id']; ?>" class="btn-card btn-view">Ver Detalle</a>
                                <a href="pedido_form.php?id=<?php echo $order['id']; ?>" class="btn-card btn-edit">Editar</a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p>No se encontraron pedidos.</p>
                <?php endif; ?>
            </div>
        </div>
    </main>
</div>

