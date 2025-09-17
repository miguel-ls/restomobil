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
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $api_url);
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

            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>ID Pedido</th>
                            <th>Mesa</th>
                            <th>Mozo</th>
                            <th>Estado</th>
                            <th>Total</th>
                            <th>Fecha</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (isset($orders_data['records']) && !empty($orders_data['records'])): ?>
                            <?php foreach ($orders_data['records'] as $order): ?>
                                <tr>
                                    <td>#<?php echo htmlspecialchars($order['id']); ?></td>
                                    <td><?php echo htmlspecialchars($order['numero_mesa']); ?></td>
                                    <td><?php echo htmlspecialchars($order['nombre_mozo']); ?></td>
                                    <td>
                                        <span class="status status-<?php echo htmlspecialchars($order['estado']); ?>">
                                            <?php echo htmlspecialchars(ucfirst(str_replace('_', ' ', $order['estado']))); ?>
                                        </span>
                                    </td>
                                    <td>$<?php echo htmlspecialchars(number_format($order['total'], 2)); ?></td>
                                    <td><?php echo htmlspecialchars(date("d/m/Y H:i", strtotime($order['fecha_creacion']))); ?></td>
                                    <td class="actions-cell">
                                        <a href="pedido_detalle.php?id=<?php echo $order['id']; ?>" class="btn-edit">Ver Detalle</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7">No se encontraron pedidos.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>
</div>

<style>
.status {
    padding: 4px 8px;
    border-radius: 12px;
    font-size: 12px;
    font-weight: bold;
    color: #fff;
    text-transform: capitalize;
}
.status-recibido { background-color: #0d6efd; } /* Azul */
.status-en_preparacion { background-color: #ffc107; color: #000; } /* Amarillo */
.status-listo_para_servir { background-color: #fd7e14; } /* Naranja */
.status-servido { background-color: #198754; } /* Verde */
.status-pagado { background-color: #20c997; } /* Turquesa */
.status-cancelado { background-color: #dc3545; } /* Rojo */
</style>

<?php
include_once 'templates/footer.php';
?>
