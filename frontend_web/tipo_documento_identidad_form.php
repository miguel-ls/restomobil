<?php
session_start();
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('Location: index.php');
    exit();
}

$page_title = 'Crear Tipo de Documento';
$type_data = ['id' => '', 'codigo' => '', 'nombre' => '', 'descripcion' => '', 'estado' => 1];
$is_editing = false;

if (isset($_GET['id'])) {
    $is_editing = true;
    $type_id = intval($_GET['id']);
    $page_title = 'Editar Tipo de Documento';

    require_once 'config.php';
    $api_url = API_BASE_URL . "tipo_documento_identidad.php?id=$type_id";
    $response = @file_get_contents($api_url);
    if ($response) {
        $type_data = json_decode($response, true);
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
                <a href="tipo_documento_identidad.php" class="btn btn-secondary">Volver</a>
            </div>
            <div class="form-container">
                <form action="tipo_documento_identidad_handler.php" method="POST">
                    <input type="hidden" name="id" value="<?php echo htmlspecialchars($type_data['id']); ?>">
                    <div class="form-group">
                        <label for="codigo">Código SUNAT</label>
                        <input type="text" id="codigo" name="codigo" value="<?php echo htmlspecialchars($type_data['codigo']); ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="nombre">Nombre</label>
                        <input type="text" id="nombre" name="nombre" value="<?php echo htmlspecialchars($type_data['nombre']); ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="descripcion">Descripción</label>
                        <textarea id="descripcion" name="descripcion" rows="4"><?php echo htmlspecialchars($type_data['descripcion']); ?></textarea>
                    </div>
                    <?php if ($is_editing): ?>
                    <div class="form-group">
                        <label for="estado">Estado</label>
                        <select id="estado" name="estado">
                            <option value="1" <?php echo ($type_data['estado'] == 1) ? 'selected' : ''; ?>>Activo</option>
                            <option value="0" <?php echo ($type_data['estado'] == 0) ? 'selected' : ''; ?>>Inactivo</option>
                        </select>
                    </div>
                    <?php endif; ?>
                    <div class="form-actions">
                        <button type="submit" class="btn"><?php echo $is_editing ? 'Actualizar' : 'Crear'; ?></button>
                    </div>
                </form>
            </div>
        </div>
    </main>
</div>
