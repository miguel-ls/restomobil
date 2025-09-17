<?php
session_start();

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('Location: index.php?error=Por+favor+inicie+sesión');
    exit();
}

include_once 'templates/header.php';

$category_data = [
    'id' => '',
    'nombre' => '',
    'descripcion' => ''
];
$page_title = 'Crear Categoría';
$action = 'categoria_handler.php';

// Si se proporciona un ID, estamos editando
if (isset($_GET['id'])) {
    $category_id = $_GET['id'];
    $api_url = "http://localhost/restaurante_system/backend/api/v1/categorias.php?id=$category_id";

    $response = file_get_contents($api_url);
    if ($response) {
        $category_data = json_decode($response, true);
    }

    $page_title = 'Editar Categoría';
}
?>

<div class="dashboard-container">
    <?php include_once 'templates/sidebar.php'; ?>

    <main class="main-content">
        <div class="container">
            <div class="page-header">
                <h1><?php echo $page_title; ?></h1>
            </div>

            <div class="form-container">
                <form action="<?php echo $action; ?>" method="POST">
                    <!-- Campo oculto para el ID en caso de edición -->
                    <input type="hidden" name="id" value="<?php echo htmlspecialchars($category_data['id']); ?>">

                    <div class="form-group">
                        <label for="nombre">Nombre de la Categoría</label>
                        <input type="text" id="nombre" name="nombre" value="<?php echo htmlspecialchars($category_data['nombre']); ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="descripcion">Descripción</label>
                        <textarea id="descripcion" name="descripcion" rows="4"><?php echo htmlspecialchars($category_data['descripcion']); ?></textarea>
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="btn">Guardar Cambios</button>
                        <a href="categorias.php" class="btn btn-secondary">Cancelar</a>
                    </div>
                </form>
            </div>
        </div>
    </main>
</div>

<?php
include_once 'templates/footer.php';
?>
