<?php
session_start();
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('Location: index.php');
    exit();
}

include_once 'templates/header.php';
include_once __DIR__ . '/../backend/config/app_config.php';

function getAPIdata($endpoint) {
    $api_url = API_BASE_URL . $endpoint;
    $ch = curl_init($api_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($ch);
    curl_close($ch);
    $data = json_decode($response, true);
    return isset($data['records']) ? $data['records'] : $data;
}

$is_editing = false;
$product_data = null;
$page_title = 'Crear Producto';

if (isset($_GET['id'])) {
    $is_editing = true;
    $product_id = intval($_GET['id']);
    $page_title = 'Editar Producto';
    $product_data = getAPIdata("productos.php?id=$product_id");
}

$categories = getAPIdata('categorias.php');
?>

<div class="dashboard-container">
    <?php include_once 'templates/sidebar.php'; ?>
    <main class="main-content">
        <div class="container">
            <div class="page-header">
                <h1><?php echo $page_title; ?></h1>
                <a href="productos.php" class="btn btn-secondary">Volver</a>
            </div>
            <div class="form-container">
                <form action="producto_handler.php" method="POST">
                    <input type="hidden" name="id" value="<?php echo htmlspecialchars($product_data['id'] ?? ''); ?>">
                    <input type="hidden" name="action" value="<?php echo $is_editing ? 'update' : 'create'; ?>">
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
                            <?php if (!empty($categories)): ?>
                                <?php foreach ($categories as $category): ?>
                                    <option value="<?php echo $category['id']; ?>" <?php echo (isset($product_data['id_categoria']) && $product_data['id_categoria'] == $category['id']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($category['nombre']); ?>
                                    </option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="estado">Estado</label>
                        <select id="estado" name="estado" required>
                            <option value="activo" <?php echo (isset($product_data['estado']) && $product_data['estado'] == 'activo') ? 'selected' : ''; ?>>Activo</option>
                            <option value="inactivo" <?php echo (isset($product_data['estado']) && $product_data['estado'] == 'inactivo') ? 'selected' : ''; ?>>Inactivo</option>
                        </select>
                    </div>
                    <div class="form-actions">
                        <button type="submit" class="btn"><?php echo $is_editing ? 'Actualizar' : 'Crear'; ?></button>
                    </div>
                </form>
            </div>
        </div>
    </main>
</div>

