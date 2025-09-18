<?php
session_start();

// Proteger la página
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('Location: index.php?error=Por+favor+inicie+sesión');
    exit();
}

$page_title = 'Gestión de Productos';
include_once 'templates/header.php';
include_once __DIR__ . '/../backend/config/app_config.php';

// Función para obtener productos desde la API con filtros
function getProducts($filters = []) {
    $api_url = 'http://localhost/restaurante_system/backend/api/v1/productos.php';
    if (!empty($filters)) {
        $api_url .= '?' . http_build_query($filters);
    }
    $ch = curl_init($api_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    $response = curl_exec($ch);
    curl_close($ch);
    return json_decode($response, true);
}

$filters = [];
if (!empty($_GET['id'])) {
    $filters['id'] = $_GET['id'];
}
if (!empty($_GET['nombre'])) {
    $filters['nombre'] = $_GET['nombre'];
}
if (!empty($_GET['descripcion'])) {
    $filters['descripcion'] = $_GET['descripcion'];
}
if (!empty($_GET['precio'])) {
    $filters['precio'] = $_GET['precio'];
}
if (!empty($_GET['categoria_nombre'])) {
    $filters['categoria_nombre'] = $_GET['categoria_nombre'];
}
if (!empty($_GET['estado'])) {
    $filters['estado'] = $_GET['estado'];
}

$products_data = getProducts($filters);
?>

<div class="dashboard-container">
    <?php include_once 'templates/sidebar.php'; // Incluiré la barra lateral para la navegación ?>

    <main class="main-content">
        <div class="container">
            <div class="page-header">
                <h1>Catálogo de Productos</h1>
                <a href="producto_form.php" class="btn">Crear Producto Nuevo</a>
            </div>

            <?php
            if (isset($_GET['success'])) {
                echo '<p class="success-message">' . htmlspecialchars($_GET['success']) . '</p>';
            }
            if (isset($_GET['error'])) {
                echo '<p class="error-message">' . htmlspecialchars($_GET['error']) . '</p>';
            }
            ?>

            <div class="table-container">
                <form method="GET" action="productos.php">
                    <div class="filters">
                        <input type="text" name="id" placeholder="Filtrar por ID" value="<?php echo htmlspecialchars($_GET['id'] ?? ''); ?>">
                        <input type="text" name="nombre" placeholder="Filtrar por Nombre" value="<?php echo htmlspecialchars($_GET['nombre'] ?? ''); ?>">
                        <input type="text" name="descripcion" placeholder="Filtrar por Descripción" value="<?php echo htmlspecialchars($_GET['descripcion'] ?? ''); ?>">
                        <input type="text" name="precio" placeholder="Filtrar por Precio" value="<?php echo htmlspecialchars($_GET['precio'] ?? ''); ?>">
                        <input type="text" name="categoria_nombre" placeholder="Filtrar por Categoría" value="<?php echo htmlspecialchars($_GET['categoria_nombre'] ?? ''); ?>">
                        <select name="estado">
                            <option value="">Todos los estados</option>
                            <option value="activo" <?php echo (isset($_GET['estado']) && $_GET['estado'] == 'activo') ? 'selected' : ''; ?>>Activo</option>
                            <option value="inactivo" <?php echo (isset($_GET['estado']) && $_GET['estado'] == 'inactivo') ? 'selected' : ''; ?>>Inactivo</option>
                        </select>
                        <button type="submit" class="btn">Filtrar</button>
                    </div>
                </form>
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nombre</th>
                            <th>Descripción</th>
                            <th>Precio</th>
                            <th>Categoría</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (isset($products_data['records']) && !empty($products_data['records'])): ?>
                            <?php foreach ($products_data['records'] as $product): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($product['id']); ?></td>
                                    <td><?php echo htmlspecialchars($product['nombre']); ?></td>
                                    <td><?php echo htmlspecialchars($product['descripcion']); ?></td>
                                    <td><?php echo CURRENCY_SYMBOL; ?><?php echo htmlspecialchars(number_format($product['precio'], 2)); ?></td>
                                    <td><?php echo htmlspecialchars($product['categoria_nombre']); ?></td>
                                    <td><?php echo htmlspecialchars($product['estado']); ?></td>
                                    <td class="actions-cell">
                                        <a href="producto_form.php?id=<?php echo $product['id']; ?>" class="btn btn-edit">Editar</a>
                                        <a href="producto_delete_handler.php?id=<?php echo $product['id']; ?>" class="btn btn-delete" onclick="return confirm('¿Estás seguro de que quieres eliminar este producto?');">Eliminar</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7">No se encontraron productos. La base de datos podría estar vacía o no hay productos que coincidan con los filtros.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>
</div>

<?php
include_once 'templates/footer.php';
?>
