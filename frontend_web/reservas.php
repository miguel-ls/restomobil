<?php
session_start();

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('Location: index.php?error=Por+favor+inicie+sesión');
    exit();
}

$page_title = 'Gestión de Reservas';
include_once 'templates/header.php';

function getReservations() {
    $api_url = 'http://localhost/restaurante_system/backend/api/v1/reservas.php';
    $ch = curl_init($api_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($ch);
    curl_close($ch);
    return json_decode($response, true);
}

$reservations_data = getReservations();
?>

<div class="dashboard-container">
    <?php include_once 'templates/sidebar.php'; ?>

    <main class="main-content">
        <div class="container">
            <div class="page-header">
                <h1>Gestión de Reservas</h1>
                <a href="reserva_form.php" class="btn">Crear Reserva Nueva</a>
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
                            <th>ID</th>
                            <th>Cliente</th>
                            <th>Mesa</th>
                            <th>Fecha y Hora</th>
                            <th>Personas</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (isset($reservations_data['records']) && !empty($reservations_data['records'])): ?>
                            <?php foreach ($reservations_data['records'] as $reserva): ?>
                                <tr>
                                    <td>#<?php echo htmlspecialchars($reserva['id']); ?></td>
                                    <td><?php echo htmlspecialchars($reserva['nombre_cliente']); ?></td>
                                    <td><?php echo htmlspecialchars($reserva['numero_mesa'] ?? 'N/A'); ?></td>
                                    <td><?php echo htmlspecialchars(date("d/m/Y H:i", strtotime($reserva['fecha_reserva']))); ?></td>
                                    <td><?php echo htmlspecialchars($reserva['cantidad_personas']); ?></td>
                                    <td>
                                        <span class="status status-<?php echo htmlspecialchars($reserva['estado']); ?>">
                                            <?php echo htmlspecialchars(ucfirst($reserva['estado'])); ?>
                                        </span>
                                    </td>
                                    <td class="actions-cell">
                                        <a href="reserva_form.php?id=<?php echo $reserva['id']; ?>" class="btn-edit">Editar</a>
                                        <?php if ($reserva['estado'] === 'confirmada'): ?>
                                            <a href="reserva_cancel_handler.php?id=<?php echo $reserva['id']; ?>" class="btn-delete" onclick="return confirm('¿Estás seguro de que quieres cancelar esta reserva?');">Cancelar</a>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7">No se encontraron reservas.</td>
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
.status-confirmada { background-color: #0d6efd; } /* Azul */
.status-completada { background-color: #198754; } /* Verde */
.status-cancelada { background-color: #dc3545; } /* Rojo */
</style>

<?php
include_once 'templates/footer.php';
?>
