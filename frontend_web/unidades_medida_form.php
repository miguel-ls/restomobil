<?php
session_start();
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('Location: index.php');
    exit();
}

require_once 'config.php';

$page_title = 'Crear Unidad de Medida';
$unidad_data = [
    'id' => '',
    'codigo' => '',
    'descripcion' => '',
    'estado' => 1 // Por defecto, Activo
];
$is_editing = false;

if (isset($_GET['id'])) {
    $is_editing = true;
    $id = intval($_GET['id']);
    $page_title = 'Editar Unidad de Medida';

    $api_url = API_BASE_URL . "unidades_medida.php?id=$id";
    $response = @file_get_contents($api_url);
    if ($response) {
        $data = json_decode($response, true);
        if ($data) {
            $unidad_data = $data;
        }
    }
}

include_once 'templates/header.php';
?>

<div class="dashboard-container">
    <?php include_once 'templates/sidebar.php'; ?>
    <main class="main-content">
        <div class="container">
            <div class="page-header">
                <h1><?php echo $page_title; ?></h1>
                <a href="unidades_medida.php" class="btn btn-secondary">Volver a la lista</a>
            </div>
            <div class="form-container">
                <form id="unidad-medida-form" action="unidades_medida_handler.php" method="POST">
                    <input type="hidden" name="id" value="<?php echo htmlspecialchars($unidad_data['id']); ?>">

                    <div class="form-group">
                        <label for="codigo">Código</label>
                        <input type="text" id="codigo" name="codigo" value="<?php echo htmlspecialchars($unidad_data['codigo']); ?>" required maxlength="10">
                    </div>

                    <div class="form-group">
                        <label for="descripcion">Descripción</label>
                        <input type="text" id="descripcion" name="descripcion" value="<?php echo htmlspecialchars($unidad_data['descripcion']); ?>" required maxlength="255">
                    </div>

                    <?php if ($is_editing): ?>
                    <div class="form-group">
                        <label for="estado">Estado</label>
                        <select id="estado" name="estado">
                            <option value="1" <?php echo ($unidad_data['estado'] == 1) ? 'selected' : ''; ?>>Activo</option>
                            <option value="0" <?php echo ($unidad_data['estado'] == 0) ? 'selected' : ''; ?>>Inactivo</option>
                        </select>
                    </div>
                    <?php endif; ?>

                    <div class="form-actions">
                        <button type="submit" class="btn"><?php echo $is_editing ? 'Actualizar' : 'Grabar'; ?></button>
                    </div>
                </form>
            </div>
        </div>
    </main>
</div>