<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

require_once 'config.php';
$page_title = 'Formulario de Tipo de Movimiento';
include_once 'templates/header.php';

$movimiento = [
    'id' => '',
    'tipo' => 'E',
    'codigo' => '',
    'descripcion' => '',
    'estado' => 'activado'
];
$is_edit = false;

if (isset($_GET['id'])) {
    $is_edit = true;
    $id = $_GET['id'];
    $page_title = 'Editar Tipo de Movimiento';
    $api_url = API_BASE_URL . "tipo_movimiento.php?id=$id";

    $response = @file_get_contents($api_url);
    if ($response) {
        $data = json_decode($response, true);
        if ($data) {
            $movimiento = $data;
        }
    }
}
?>

<div class="dashboard-container">
    <?php include_once 'templates/sidebar.php'; ?>
    <main class="main-content">
        <div class="container">
            <div class="page-header">
                <h1><?php echo $is_edit ? 'Editar Tipo de Movimiento' : 'Crear Nuevo Tipo de Movimiento'; ?></h1>
                <a href="tipo_movimiento.php" class="btn btn-secondary">Volver a Lista</a>
            </div>

            <div class="form-container">
                <form action="tipo_movimiento_handler.php" method="POST">
                    <input type="hidden" name="id" value="<?php echo htmlspecialchars($movimiento['id']); ?>">

                    <div class="form-group">
                        <label for="tipo">Tipo</label>
                        <select id="tipo" name="tipo" required>
                            <option value="E" <?php echo ($movimiento['tipo'] == 'E') ? 'selected' : ''; ?>>Entrada</option>
                            <option value="S" <?php echo ($movimiento['tipo'] == 'S') ? 'selected' : ''; ?>>Salida</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="codigo">Código</label>
                        <input type="text" id="codigo" name="codigo" value="<?php echo htmlspecialchars($movimiento['codigo']); ?>" required maxlength="3">
                    </div>

                    <div class="form-group">
                        <label for="descripcion">Descripción</label>
                        <textarea id="descripcion" name="descripcion" rows="4" required><?php echo htmlspecialchars($movimiento['descripcion']); ?></textarea>
                    </div>

                    <?php if ($is_edit): ?>
                    <div class="form-group">
                        <label for="estado">Estado</label>
                        <select id="estado" name="estado">
                            <option value="activado" <?php echo ($movimiento['estado'] === 'activado') ? 'selected' : ''; ?>>Activado</option>
                            <option value="desactivado" <?php echo ($movimiento['estado'] === 'desactivado') ? 'selected' : ''; ?>>Desactivado</option>
                        </select>
                    </div>
                    <?php endif; ?>

                    <div class="form-actions">
                        <button type="submit" class="btn"><?php echo $is_edit ? 'Actualizar' : 'Guardar'; ?></button>
                    </div>
                </form>
            </div>
        </div>
    </main>
</div>

<?php
if (isset($_GET['error'])) {
    echo "<script>showAlert('Error de Validación', '" . htmlspecialchars($_GET['error']) . "');</script>";
}
?>