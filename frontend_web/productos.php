<?php
session_start();

// Proteger la página
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('Location: index.php?error=Por+favor+inicie+sesión');
    exit();
}

$page_title = 'Gestión de Productos';
include_once 'templates/header.php';
include_once __DIR__ . '/config.php';

// Función para obtener productos desde la API con filtros
function getProducts($filters = []) {
    $api_url = API_BASE_URL . 'productos.php';
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
if (isset($_GET['controlar_stock']) && $_GET['controlar_stock'] !== '') {
    $filters['controlar_stock'] = $_GET['controlar_stock'];
}
if (!empty($_GET['page'])) {
    $filters['page'] = $_GET['page'];
}

$products_data = getProducts($filters);
$pagination = $products_data['pagination'] ?? null;

function getCategories() {
    $api_url = API_BASE_URL . 'categorias.php';
    $ch = curl_init($api_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    $response = curl_exec($ch);
    curl_close($ch);
    $data = json_decode($response, true);
    return $data['records'] ?? [];
}
$categories = getCategories();
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

            <div class="filter-container">
                <form method="GET" action="productos.php">
                    <div class="filters">
                        <input type="text" name="id" placeholder="Filtrar por ID" value="<?php echo htmlspecialchars($_GET['id'] ?? ''); ?>">
                        <input type="text" name="nombre" placeholder="Filtrar por Nombre" value="<?php echo htmlspecialchars($_GET['nombre'] ?? ''); ?>">
                        <input type="text" name="descripcion" placeholder="Filtrar por Descripción" value="<?php echo htmlspecialchars($_GET['descripcion'] ?? ''); ?>">
                        <input type="text" name="precio" placeholder="Filtrar por Precio" value="<?php echo htmlspecialchars($_GET['precio'] ?? ''); ?>">
                        <select name="categoria_nombre">
                            <option value="">Todas las categorías</option>
                            <?php foreach ($categories as $category): ?>
                                <option value="<?php echo htmlspecialchars($category['nombre']); ?>" <?php echo (isset($_GET['categoria_nombre']) && $_GET['categoria_nombre'] == $category['nombre']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($category['nombre']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <select name="estado">
                            <option value="">Todos los estados</option>
                            <option value="activo" <?php echo (isset($_GET['estado']) && $_GET['estado'] == 'activo') ? 'selected' : ''; ?>>Activo</option>
                            <option value="inactivo" <?php echo (isset($_GET['estado']) && $_GET['estado'] == 'inactivo') ? 'selected' : ''; ?>>Inactivo</option>
                        </select>
                        <select name="controlar_stock">
                            <option value="">Controlar Stock</option>
                            <option value="1" <?php echo (isset($_GET['controlar_stock']) && $_GET['controlar_stock'] == '1') ? 'selected' : ''; ?>>Sí</option>
                            <option value="0" <?php echo (isset($_GET['controlar_stock']) && $_GET['controlar_stock'] === '0') ? 'selected' : ''; ?>>No</option>
                        </select>
                        <button type="submit" class="btn">Filtrar</button>
                    </div>
                </form>
            </div>
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nombre</th>
                            <th>Descripción</th>
                            <th>Precio</th>
                            <th>Categoría</th>
                            <th>Estado</th>
                            <th>Controlar Stock</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (isset($products_data['records']) && !empty($products_data['records'])): ?>
                            <?php foreach ($products_data['records'] as $product): ?>
                                <tr>
                                    <td data-label="ID"><?php echo htmlspecialchars($product['id']); ?></td>
                                    <td data-label="Nombre"><?php echo htmlspecialchars($product['nombre']); ?></td>
                                    <td data-label="Descripción"><?php echo htmlspecialchars($product['descripcion']); ?></td>
                                    <td data-label="Precio"><?php echo CURRENCY_SYMBOL; ?><?php echo htmlspecialchars(number_format($product['precio'], 2)); ?></td>
                                    <td data-label="Categoría"><?php echo htmlspecialchars($product['categoria_nombre']); ?></td>
                                    <td data-label="Estado">
                                        <span class="status status-<?php echo htmlspecialchars(strtolower($product['estado'])); ?>">
                                            <?php echo htmlspecialchars($product['estado']); ?>
                                        </span>
                                    </td>
                                    <td data-label="Controlar Stock">
                                        <span class="status status-<?php echo $product['controlar_stock'] ? 'active' : 'inactive'; ?>">
                                            <?php echo $product['controlar_stock'] ? 'Sí' : 'No'; ?>
                                        </span>
                                    </td>
                                    <td data-label="Acciones" class="actions-cell">
                                        <a href="producto_form.php?id=<?php echo $product['id']; ?>" class="btn btn-edit">Editar</a>
                                        <a href="producto_delete_handler.php?id=<?php echo $product['id']; ?>" class="btn btn-delete" onclick="return confirm('¿Estás seguro de que quieres eliminar este producto?');">Eliminar</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="8">No se encontraron productos. La base de datos podría estar vacía o no hay productos que coincidan con los filtros.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
                <?php if ($pagination && $pagination['total_pages'] > 1): ?>
                    <div class="pagination">
                        <?php
                        // Mantener los filtros actuales en los enlaces de paginación
                        $queryParams = $_GET;

                        // Página anterior
                        if ($pagination['page'] > 1) {
                            $queryParams['page'] = $pagination['page'] - 1;
                            echo '<a href="?' . http_build_query($queryParams) . '">&laquo; Anterior</a>';
                        }

                        // Números de página
                        for ($i = 1; $i <= $pagination['total_pages']; $i++) {
                            $queryParams['page'] = $i;
                            $activeClass = ($i == $pagination['page']) ? 'active' : '';
                            echo '<a href="?' . http_build_query($queryParams) . '" class="' . $activeClass . '">' . $i . '</a>';
                        }

                        // Página siguiente
                        if ($pagination['page'] < $pagination['total_pages']) {
                            $queryParams['page'] = $pagination['page'] + 1;
                            echo '<a href="?' . http_build_query($queryParams) . '">Siguiente &raquo;</a>';
                        }
                        ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </main>
</div>