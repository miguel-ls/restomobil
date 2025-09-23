<?php
session_start();
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('Location: index.php?error=Por+favor+inicie+sesión');
    exit();
}

include_once 'templates/header.php';
include_once __DIR__ . '/config.php';

// Función auxiliar para obtener datos de la API
function getAPIData($endpoint) {
    $api_url = API_BASE_URL . $endpoint;
    $ch = curl_init($api_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($ch);
    $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    if ($httpcode >= 400) {
        return null;
    }
    return json_decode($response, true);
}

$is_editing = false;
$movimiento_data = null;
$page_title = 'Registrar Nuevo Movimiento';

if (isset($_GET['id'])) {
    $is_editing = true;
    $movimiento_id = intval($_GET['id']);
    $page_title = 'Editar Movimiento';
    $movimiento_data = getAPIData("movimientos_caja.php?id=$movimiento_id");

    // Formatear la fecha para el input type="datetime-local"
    if ($movimiento_data && !empty($movimiento_data['fecha'])) {
        $movimiento_data['fecha'] = (new DateTime($movimiento_data['fecha']))->format('Y-m-d\TH:i');
    }
}
?>

<div class="dashboard-container">
    <?php include_once 'templates/sidebar.php'; ?>
    <main class="main-content">
        <div class="container">
            <div class="page-header">
                <h1><?php echo htmlspecialchars($page_title); ?></h1>
                <a href="movim_caja.php" class="btn btn-secondary">Volver a Lista</a>
            </div>

            <?php if (isset($_GET['error'])): ?>
                <p class="error-message"><?php echo htmlspecialchars($_GET['error']); ?></p>
            <?php endif; ?>

            <div class="form-container">
                <form action="movimiento_caja_handler.php" method="POST">
                    <input type="hidden" name="id" value="<?php echo htmlspecialchars($movimiento_data['id'] ?? ''); ?>">
                    <input type="hidden" name="action" value="<?php echo $is_editing ? 'update' : 'create'; ?>">

                    <div class="form-group">
                        <label for="fecha">Fecha y Hora</label>
                        <input type="datetime-local" id="fecha" name="fecha" value="<?php echo htmlspecialchars($movimiento_data['fecha'] ?? date('Y-m-d\TH:i')); ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="tipo_movimiento">Tipo de Movimiento</label>
                        <select id="tipo_movimiento" name="tipo_movimiento" required>
                            <option value="entrada" <?php echo (isset($movimiento_data['tipo_movimiento']) && $movimiento_data['tipo_movimiento'] == 'entrada') ? 'selected' : ''; ?>>Entrada</option>
                            <option value="salida" <?php echo (isset($movimiento_data['tipo_movimiento']) && $movimiento_data['tipo_movimiento'] == 'salida') ? 'selected' : ''; ?>>Salida</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="importe">Importe</label>
                        <input type="number" id="importe" name="importe" step="0.01" min="0.01" value="<?php echo htmlspecialchars($movimiento_data['importe'] ?? ''); ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="descripcion">Descripción</label>
                        <textarea id="descripcion" name="descripcion" rows="4"><?php echo htmlspecialchars($movimiento_data['descripcion'] ?? ''); ?></textarea>
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="btn"><?php echo $is_editing ? 'Actualizar Movimiento' : 'Guardar Movimiento'; ?></button>
                    </div>
                </form>
            </div>
        </div>
    </main>
</div>
