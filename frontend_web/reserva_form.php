<?php
session_start();
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('Location: index.php?error=Por+favor+inicie+sesión');
    exit();
}

include_once 'templates/header.php';
include_once __DIR__ . '/../backend/config/app_config.php';

if (!defined('API_BASE_URL')) {
    define('API_BASE_URL', 'http://localhost/restaurante_system/backend/api/v1/');
}

function fetchFromAPI($endpoint) {
    $api_url = API_BASE_URL . $endpoint;
    $ch = curl_init($api_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    $response = curl_exec($ch);
    if (curl_errno($ch)) {
        error_log("cURL Error fetching $api_url: " . curl_error($ch));
        return null;
    }
    curl_close($ch);
    return json_decode($response, true);
}

$is_editing = false;
$reservation_data = null;

if (isset($_GET['id'])) {
    $is_editing = true;
    $reservation_id = intval($_GET['id']);
    $page_title = "Editar Reserva #$reservation_id";
    $reservation_data = fetchFromAPI("reservas.php?id=$reservation_id");
} else {
    $page_title = 'Crear Nueva Reserva';
}

$mesas_data = fetchFromAPI('mesas.php');
$mesas = $mesas_data['records'] ?? [];
$reservation_states = ['confirmada', 'completada', 'cancelada'];

?>

<div class="dashboard-container">
    <?php include_once 'templates/sidebar.php'; ?>
    <main class="main-content">
        <div class="container">
            <div class="page-header">
                <h1><?php echo $page_title; ?></h1>
                <a href="reservas.php" class="btn btn-secondary">Volver a la Lista</a>
            </div>

            <div class="form-container">
                <form action="reserva_handler.php" method="POST">
                    <?php if ($is_editing): ?>
                        <input type="hidden" name="id" value="<?php echo htmlspecialchars($reservation_data['id']); ?>">
                    <?php endif; ?>

                    <div class="form-group">
                        <label for="nombre_cliente">Nombre del Cliente</label>
                        <input type="text" id="nombre_cliente" name="nombre_cliente" value="<?php echo htmlspecialchars($reservation_data['nombre_cliente'] ?? ''); ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="telefono_cliente">Teléfono</label>
                        <input type="tel" id="telefono_cliente" name="telefono_cliente" value="<?php echo htmlspecialchars($reservation_data['telefono_cliente'] ?? ''); ?>">
                    </div>

                    <div class="form-group">
                        <label for="email_cliente">Email</label>
                        <input type="email" id="email_cliente" name="email_cliente" value="<?php echo htmlspecialchars($reservation_data['email_cliente'] ?? ''); ?>">
                    </div>

                    <div class="form-group">
                        <label for="fecha_reserva">Fecha y Hora</label>
                        <input type="datetime-local" id="fecha_reserva" name="fecha_reserva" value="<?php echo htmlspecialchars(date('Y-m-d\TH:i', strtotime($reservation_data['fecha_reserva'] ?? 'now'))); ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="cantidad_personas">Cantidad de Personas</label>
                        <input type="number" id="cantidad_personas" name="cantidad_personas" value="<?php echo htmlspecialchars($reservation_data['cantidad_personas'] ?? '1'); ?>" min="1" required>
                    </div>

                    <div class="form-group">
                        <label for="id_mesa">Mesa</label>
                        <select id="id_mesa" name="id_mesa" required>
                            <option value="">Seleccione una mesa</option>
                            <?php foreach ($mesas as $mesa): ?>
                                <option value="<?php echo $mesa['id']; ?>" <?php echo (isset($reservation_data['id_mesa']) && $reservation_data['id_mesa'] == $mesa['id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($mesa['numero_mesa'] . ' (Capacidad: ' . $mesa['capacidad'] . ')'); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <?php if ($is_editing): ?>
                    <div class="form-group">
                        <label for="estado">Estado</label>
                        <select id="estado" name="estado" required>
                            <?php foreach ($reservation_states as $state): ?>
                                <option value="<?php echo $state; ?>" <?php echo (isset($reservation_data['estado']) && $reservation_data['estado'] == $state) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars(ucfirst($state)); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <?php endif; ?>

                    <div class="form-group">
                        <label for="observaciones">Observaciones</label>
                        <textarea id="observaciones" name="observaciones" rows="3"><?php echo htmlspecialchars($reservation_data['observaciones'] ?? ''); ?></textarea>
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="btn"><?php echo $is_editing ? 'Actualizar Reserva' : 'Crear Reserva'; ?></button>
                    </div>
                </form>
            </div>
        </div>
    </main>
</div>

<?php include_once 'templates/footer.php'; ?>
