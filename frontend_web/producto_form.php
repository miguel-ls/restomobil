<?php
session_start();

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('Location: index.php?error=Por+favor+inicie+sesión');
    exit();
}

include_once 'templates/header.php';

$product_id = isset($_GET['id']) ? intval($_GET['id']) : null;
$product_data = null;
$page_title = 'Crear Nuevo Producto';
$form_action = 'create';

if ($product_id) {
    // Si es una edición, obtener los datos del producto desde la API
    $api_url = "http://localhost/restaurante_system/backend/api/v1/productos.php?id=" . $product_id;
    $response = file_get_contents($api_url);
    $product_data = json_decode($response, true);
    $page_title = 'Editar Producto';
    $form_action = 'update';
}

// Placeholder para categorías. Esto debería venir de una llamada a la API en el futuro.
$categories = [
    ['id' => 1, 'nombre' => 'Bebidas'],
    ['id' => 2, 'nombre' => 'Entradas'],
    ['id' => 3, 'nombre' => 'Plato Fuerte'],
    ['id' => 4, 'nombre' => 'Postres']
];

?>

<div class="dashboard-container">
    <?php include_once 'templates/sidebar.php'; ?>

    <main class="main-content">
        <div class="container">
            <h1><?php echo $page_title; ?></h1>

            <form action="producto_handler.php" method="POST" class="styled-form">
                <input type="hidden" name="id" value="<?php echo htmlspecialchars($product_data['id'] ?? ''); ?>">
                <input type="hidden" name="action" value="<?php echo $form_action; ?>">

                <div class="form-group">
                    <label for="nombre">Nombre del Producto</label>
                    <input type="text" id="nombre" name="nombre" value="<?php echo htmlspecialchars($product_data['nombre'] ?? ''); ?>" required>
                </div>

                <div class="form-group">
                    <label for="descripcion">Descripción</label>
                    <textarea id="descripcion" name="descripcion" rows="4"><?php echo htmlspecialchars($product_data['descripcion'] ?? ''); ?></textarea>
                </div>

                <div class="form-group">
                    <label for="precio">Precio</label>
                    <input type="number" id="precio" name="precio" step="0.01" value="<?php echo htmlspecialchars($product_data['precio'] ?? ''); ?>" required>
                </div>

                <div class="form-group">
                    <label for="id_categoria">Categoría</label>
                    <select id="id_categoria" name="id_categoria" required>
                        <option value="">Seleccione una categoría</option>
                        <?php foreach ($categories as $category): ?>
                            <option value="<?php echo $category['id']; ?>" <?php echo (isset($product_data['id_categoria']) && $product_data['id_categoria'] == $category['id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($category['nombre']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn">Guardar Producto</button>
                    <a href="productos.php" class="btn btn-secondary">Cancelar</a>
                </div>
            </form>
        </div>
    </main>
</div>

<style>
.styled-form {
    background-color: #fff;
    padding: 30px;
    border-radius: 8px;
    box-shadow: 0 2px 5px rgba(0,0,0,0.1);
    margin-top: 20px;
}
.form-actions {
    margin-top: 20px;
    display: flex;
    gap: 10px;
}
.btn-secondary {
    background-color: var(--secondary-color);
}
.btn-secondary:hover {
    background-color: #5a6268;
}
</style>

<?php
include_once 'templates/footer.php';
?>
