<?php
session_start();
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('Location: index.php');
    exit();
}

$page_title = 'Gestión de Reservas';
include_once 'templates/header.php';

function getReservations() {
    $api_url = 'http://localhost/restaurante_system/backend/api/v1/reservas.php';
    $ch = curl_init($api_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
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
                <!-- Futuro botón para crear reserva -->
                <!-- <a href="reserva_form.php" class="btn">Crear Reserva</a> -->
            </div>
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Cliente</th>
                            <th>Fecha y Hora</th>
                            <th>Mesa</th>
                            <th>Personas</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (isset($reservations_data['records']) && !empty($reservations_data['records'])): ?>
                            <?php foreach ($reservations_data['records'] as $reservation): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($reservation['id']); ?></td>
                                    <td><?php echo htmlspecialchars($reservation['nombre_cliente']); ?></td>
                                    <td><?php echo htmlspecialchars(date("d/m/Y H:i", strtotime($reservation['fecha_reserva']))); ?></td>
                                    <td>Mesa <?php echo htmlspecialchars($reservation['numero_mesa']); ?></td>
                                    <td><?php echo htmlspecialchars($reservation['cantidad_personas']); ?></td>
                                    <td>
                                        <span class="status status-<?php echo htmlspecialchars(strtolower($reservation['estado'])); ?>">
                                            <?php echo htmlspecialchars(ucfirst($reservation['estado'])); ?>
                                        </span>
                                    </td>
                                    <td class="actions-cell">
                                        <!-- Futuros botones de acción -->
                                        <!-- <a href="reserva_form.php?id=<?php echo $reservation['id']; ?>" class="btn btn-edit">Editar</a> -->
                                        <!-- <a href="reserva_delete_handler.php?id=<?php echo $reservation['id']; ?>" class="btn btn-delete" onclick="return confirm('¿Estás seguro?');">Cancelar</a> -->
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="7">No se encontraron reservas.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>
</div>

<?php include_once 'templates/footer.php'; ?>
