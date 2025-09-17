<?php
session_start();

// Proteger la página
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('Location: index.php?error=Por+favor+inicie+sesión');
    exit();
}

$page_title = 'Gestión de Productos';
include_once 'templates/header.php';

// Función para obtener productos desde la API
function getProducts() {
    $api_url = 'http://localhost/restaurante_system/backend/api/v1/productos.php';
    $response = file_get_contents($api_url);
    return json_decode($response, true);
}

$products_data = getProducts();
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
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nombre</th>
                            <th>Descripción</th>
                            <th>Precio</th>
                            <th>Categoría</th>
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
                                    <td>$<?php echo htmlspecialchars(number_format($product['precio'], 2)); ?></td>
                                    <td><?php echo htmlspecialchars($product['categoria_nombre']); ?></td>
                                    <td class="actions-cell">
                                        <a href="producto_form.php?id=<?php echo $product['id']; ?>" class="btn-edit">Editar</a>
                                        <a href="producto_delete_handler.php?id=<?php echo $product['id']; ?>" class="btn-delete" onclick="return confirm('¿Estás seguro de que quieres eliminar este producto?');">Eliminar</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6">No se encontraron productos. La base de datos podría estar vacía.</td>
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
