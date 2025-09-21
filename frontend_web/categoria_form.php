<?php
session_start();
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('Location: index.php');
    exit();
}

$page_title = 'Crear Categoría';
$category_data = ['id' => '', 'nombre' => '', 'descripcion' => ''];
$is_editing = false;

if (isset($_GET['id'])) {
    $is_editing = true;
    $category_id = intval($_GET['id']);
    $page_title = 'Editar Categoría';

    // Incluir configuración de la API
    require_once 'config.php';
    $api_url = API_BASE_URL . "categorias.php?id=$category_id";
    $response = @file_get_contents($api_url);
    if ($response) {
        $category_data = json_decode($response, true);
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
                <a href="categorias.php" class="btn btn-secondary">Volver</a>
            </div>
            <div class="form-container">
                <form action="categoria_handler.php" method="POST">
                    <input type="hidden" name="id" value="<?php echo htmlspecialchars($category_data['id']); ?>">
                    <div class="form-group">
                        <label for="nombre">Nombre</label>
                        <input type="text" id="nombre" name="nombre" value="<?php echo htmlspecialchars($category_data['nombre']); ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="descripcion">Descripción</label>
                        <textarea id="descripcion" name="descripcion" rows="4"><?php echo htmlspecialchars($category_data['descripcion']); ?></textarea>
                    </div>
                    <div class="form-actions">
                        <button type="submit" class="btn"><?php echo $is_editing ? 'Actualizar' : 'Crear'; ?></button>
                    </div>
                </form>
            </div>
        </div>
    </main>
</div>
