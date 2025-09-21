<?php
session_start();
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('Location: index.php');
    exit();
}

$page_title = 'Gestión de Categorías';
include_once 'templates/header.php';

function getCategories() {
    require_once 'config.php';
    $api_url = API_BASE_URL . 'categorias.php';
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

            <div class="filters">
                <input type="text" id="filter-id" placeholder="Filtrar por ID...">
                <input type="text" id="filter-nombre" placeholder="Filtrar por Nombre...">
                <input type="text" id="filter-descripcion" placeholder="Filtrar por Descripción...">
                <select id="filter-tipo">
                    <option value="">Todos los Tipos</option>
                    <option value="Bienes">Bienes</option>
                    <option value="Servicios">Servicios</option>
                </select>
                <select id="filter-estado">
                    <option value="">Todos los Estados</option>
                    <option value="Activo">Activo</option>
                    <option value="Inactivo">Inactivo</option>
                </select>
            </div>

            <div class="table-container">
                <table id="categorias-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nombre</th>
                            <th>Descripción</th>
                            <th>Tipo de Categoría</th>
                            <th>Estado</th>
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
                                    <td><?php echo htmlspecialchars($category['tipo_categoria']); ?></td>
                                    <td>
                                        <span class="status <?php echo $category['estado'] ? 'status-active' : 'status-inactive'; ?>">
                                            <?php echo $category['estado'] ? 'Activo' : 'Inactivo'; ?>
                                        </span>
                                    </td>
                                    <td class="actions-cell">
                                        <a href="categoria_form.php?id=<?php echo $category['id']; ?>" class="btn btn-edit">Editar</a>
                                        <a href="categoria_delete_handler.php?id=<?php echo $category['id']; ?>" class="btn btn-delete" onclick="return confirm('¿Estás seguro?');">Eliminar</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="6">No se encontraron categorías.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>
</div>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const filters = {
        id: document.getElementById('filter-id'),
        nombre: document.getElementById('filter-nombre'),
        descripcion: document.getElementById('filter-descripcion'),
        tipo: document.getElementById('filter-tipo'),
        estado: document.getElementById('filter-estado')
    };
    const tableRows = document.querySelectorAll('#categorias-table tbody tr');

    function filterTable() {
        const filterValues = {
            id: filters.id.value.toLowerCase(),
            nombre: filters.nombre.value.toLowerCase(),
            descripcion: filters.descripcion.value.toLowerCase(),
            tipo: filters.tipo.value.toLowerCase(),
            estado: filters.estado.value.toLowerCase()
        };

        tableRows.forEach(row => {
            if (row.cells.length > 1) { // Check if it's not the "no records found" row
                const cells = {
                    id: row.cells[0].textContent.toLowerCase(),
                    nombre: row.cells[1].textContent.toLowerCase(),
                    descripcion: row.cells[2].textContent.toLowerCase(),
                    tipo: row.cells[3].textContent.toLowerCase(),
                    estado: row.cells[4].textContent.trim().toLowerCase()
                };

                const matchesId = cells.id.includes(filterValues.id);
                const matchesNombre = cells.nombre.includes(filterValues.nombre);
                const matchesDesc = cells.descripcion.includes(filterValues.descripcion);
                const matchesTipo = filterValues.tipo === '' || cells.tipo === filterValues.tipo;
                const matchesEstado = filterValues.estado === '' || cells.estado === filterValues.estado;

                if (matchesId && matchesNombre && matchesDesc && matchesTipo && matchesEstado) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            }
        });
    }

    for (const key in filters) {
        filters[key].addEventListener('keyup', filterTable);
        filters[key].addEventListener('change', filterTable);
    }
});
</script>
