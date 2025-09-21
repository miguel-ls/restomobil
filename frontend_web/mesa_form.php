<?php
session_start();
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('Location: index.php');
    exit();
}

$page_title = 'Crear Mesa';
$table_data = ['id' => '', 'numero_mesa' => '', 'capacidad' => '', 'estado' => 'disponible'];
$is_editing = false;

if (isset($_GET['id'])) {
    $is_editing = true;
    $table_id = intval($_GET['id']);
    $page_title = 'Editar Mesa';

    // Incluir configuración de la API
    require_once 'config.php';
    $api_url = API_BASE_URL . "mesas.php?id=$table_id";
    $response = @file_get_contents($api_url);
    if ($response) {
        $table_data = json_decode($response, true);
    }
}

include_once 'templates/header.php';
$estados = ['disponible', 'ocupada', 'reservada', 'mantenimiento'];
?>

<div class="dashboard-container">
    <?php include_once 'templates/sidebar.php'; ?>
    <main class="main-content">
        <div class="container">
            <div class="page-header">
                <h1><?php echo $page_title; ?></h1>
                <a href="mesas.php" class="btn btn-secondary">Volver</a>
            </div>
            <div class="form-container">
                <form action="mesa_handler.php" method="POST">
                    <input type="hidden" name="id" value="<?php echo htmlspecialchars($table_data['id']); ?>">
                    <div class="form-group">
                        <label for="numero_mesa">Número de Mesa</label>
                        <input type="text" id="numero_mesa" name="numero_mesa" value="<?php echo htmlspecialchars($table_data['numero_mesa']); ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="capacidad">Capacidad</label>
                        <input type="number" id="capacidad" name="capacidad" value="<?php echo htmlspecialchars($table_data['capacidad']); ?>" required min="1">
                    </div>
                    <div class="form-group">
                        <label for="estado">Estado</label>
                        <select id="estado" name="estado" required>
                            <?php foreach ($estados as $estado): ?>
                                <option value="<?php echo $estado; ?>" <?php if ($estado == $table_data['estado']) echo 'selected'; ?>>
                                    <?php echo ucfirst($estado); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="es_libre">
                            <input type="checkbox" id="es_libre" name="es_libre" value="1" <?php echo ($table_data['es_libre'] ?? true) ? 'checked' : ''; ?>>
                            Mesa de Servicio Libre
                        </label>
                    </div>
                    <div class="form-actions">
                        <button type="submit" class="btn"><?php echo $is_editing ? 'Actualizar' : 'Crear'; ?></button>
                    </div>
                </form>
            </div>
        </div>
    </main>
</div>

