<?php
session_start();

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('Location: index.php?error=Por+favor+inicie+sesión');
    exit();
}

$page_title = 'Gestión de Pedidos';
include_once 'templates/header.php';

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
                                <p class="total"><strong>Total:</strong> $<?php echo htmlspecialchars(number_format($order['total'], 2)); ?></p>
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

<style>
.order-cards-container {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 20px;
}
.order-card {
    background-color: #fff;
    border-radius: 8px;
    box-shadow: 0 2px 5px rgba(0,0,0,0.1);
    display: flex;
    flex-direction: column;
}
.card-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 10px 15px;
    border-bottom: 1px solid var(--border-color);
}
.card-body {
    padding: 15px;
    flex-grow: 1;
}
.card-body p {
    margin: 0 0 10px 0;
}
.card-body p.total {
    margin-top: 15px;
    font-size: 1.1em;
}
.card-footer {
    padding: 10px 15px;
    border-top: 1px solid var(--border-color);
    display: flex;
    gap: 10px;
}
.btn-card {
    flex-grow: 1;
    text-align: center;
    padding: 8px;
    text-decoration: none;
    border-radius: 5px;
    color: #fff;
    font-weight: 500;
}
.btn-view {
    background-color: var(--secondary-color);
}
.btn-edit {
    background-color: #ffc107;
    color: #000;
}

/* Estilos de estado */
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
