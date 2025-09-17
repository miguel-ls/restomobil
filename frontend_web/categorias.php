<?php
session_start();

// Proteger la página
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('Location: index.php?error=Por+favor+inicie+sesión');
    exit();
}

$page_title = 'Gestión de Categorías';
include_once 'templates/header.php';

// Función para obtener categorías desde la API
function getCategories() {
    // La URL de la API debe ser accesible desde el servidor donde se ejecuta este script.
    // Usar localhost puede no funcionar si el entorno de ejecución está en un contenedor Docker o similar.
    // Asegúrate de que la URL es la correcta para tu entorno.
    $api_url = 'http://localhost/restaurante_system/backend/api/v1/categorias.php';

    // Usar cURL para más flexibilidad
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $api_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    $response = curl_exec($ch);
    $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    // Solo decodificar si la respuesta es exitosa
    if ($httpcode == 200) {
        return json_decode($response, true);
    }
    return ['records' => []]; // Devolver un array vacío si hay un error
}

$categories_data = getCategories();
?>

<div class="dashboard-container">
    <?php include_once 'templates/sidebar.php'; ?>

    <main class="main-content">
        <div class="container">
            <div class="page-header">
                <h1>Catálogo de Categorías</h1>
                <a href="categoria_form.php" class="btn">Crear Categoría Nueva</a>
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
                                        <a href="categoria_form.php?id=<?php echo $category['id']; ?>" class="btn-edit">Editar</a>
                                        <a href="categoria_delete_handler.php?id=<?php echo $category['id']; ?>" class="btn-delete" onclick="return confirm('¿Estás seguro de que quieres eliminar esta categoría?');">Eliminar</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="4">No se encontraron categorías. Puede que no haya ninguna registrada.</td>
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
