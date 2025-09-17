<?php
session_start();
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('Location: index.php');
    exit();
}

$page_title = 'Gestión de Categorías';
include_once 'templates/header.php';

function getCategories() {
    $api_url = 'http://localhost/restaurante_system/backend/api/v1/categorias.php';
    $ch = curl_init($api_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    $response = curl_exec($ch);
    curl_close($ch);
    return json_decode($response, true);
}

$categories_data = getCategories();
?>

<div class="dashboard-container">
    <?php include_once 'templates/sidebar.php'; ?>
    <main class="main-content">
        <div class="container">
            <div class="page-header">
                <h1>Catálogo de Categorías</h1>
                <a href="categoria_form.php" class="btn">Crear Categoría</a>
            </div>
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nombre</th>
                            <th>Descripción</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (isset($categories_data['records']) && !empty($categories_data['records'])): ?>
                            <?php foreach ($categories_data['records'] as $category): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($category['id']); ?></td>
                                    <td><?php echo htmlspecialchars($category['nombre']); ?></td>
                                    <td><?php echo htmlspecialchars($category['descripcion']); ?></td>
                                    <td class="actions-cell">
                                        <a href="categoria_form.php?id=<?php echo $category['id']; ?>" class="btn btn-edit">Editar</a>
                                        <a href="categoria_delete_handler.php?id=<?php echo $category['id']; ?>" class="btn btn-delete" onclick="return confirm('¿Estás seguro?');">Eliminar</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="4">No se encontraron categorías.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>
</div>

<?php include_once 'templates/footer.php'; ?>
