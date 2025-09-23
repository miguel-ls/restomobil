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
$registro_data = null;
$page_title = 'Registrar Apertura o Cierre';

if (isset($_GET['id'])) {
    $is_editing = true;
    $registro_id = intval($_GET['id']);
    $page_title = 'Editar Registro';
    $registro_data = getAPIData("apertura_cierre.php?id=$registro_id");

    // Formatear la fecha para el input type="datetime-local"
    if ($registro_data && !empty($registro_data['fecha'])) {
        $registro_data['fecha'] = (new DateTime($registro_data['fecha']))->format('Y-m-d\TH:i');
    }
}
?>

<div class="dashboard-container">
    <?php include_once 'templates/sidebar.php'; ?>
    <main class="main-content">
        <div class="container">
            <div class="page-header">
                <h1><?php echo htmlspecialchars($page_title); ?></h1>
                <a href="apertura_cierre.php" class="btn btn-secondary">Volver a Lista</a>
            </div>

            <?php if (isset($_GET['error'])): ?>
                <p class="error-message"><?php echo htmlspecialchars(urldecode($_GET['error'])); ?></p>
            <?php endif; ?>

            <div class="form-container">
                <form action="apertura_cierre_handler.php" method="POST">
                    <input type="hidden" name="id" value="<?php echo htmlspecialchars($registro_data['id'] ?? ''); ?>">
                    <input type="hidden" name="action" value="<?php echo $is_editing ? 'update' : 'create'; ?>">

                    <div class="form-group">
                        <label for="fecha">Fecha y Hora</label>
                        <input type="datetime-local" id="fecha" name="fecha" value="<?php echo htmlspecialchars($registro_data['fecha'] ?? date('Y-m-d\TH:i')); ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="tipo_movimiento">Tipo de Movimiento</label>
                        <select id="tipo_movimiento" name="tipo_movimiento" required>
                            <option value="apertura" <?php echo (isset($registro_data['tipo_movimiento']) && $registro_data['tipo_movimiento'] == 'apertura') ? 'selected' : ''; ?>>Apertura</option>
                            <option value="cierre" <?php echo (isset($registro_data['tipo_movimiento']) && $registro_data['tipo_movimiento'] == 'cierre') ? 'selected' : ''; ?>>Cierre</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="importe">Importe</label>
                        <input type="number" id="importe" name="importe" step="0.01" min="0.01" value="<?php echo htmlspecialchars($registro_data['importe'] ?? ''); ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="descripcion">Descripción</label>
                        <textarea id="descripcion" name="descripcion" rows="4"><?php echo htmlspecialchars($registro_data['descripcion'] ?? ''); ?></textarea>
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="btn"><?php echo $is_editing ? 'Actualizar Registro' : 'Guardar Registro'; ?></button>
                    </div>
                </form>
            </div>
        </div>
    </main>
</div>
