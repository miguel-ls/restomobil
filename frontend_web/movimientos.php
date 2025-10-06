<?php
session_start();
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('Location: index.php?error=Por+favor+inicie+sesión');
    exit();
}
$page_title = 'Gestión de Movimientos';
include_once 'templates/header.php';
include_once __DIR__ . '/config.php';

// Obtener filtro y paginación de la URL
$filter = isset($_GET['filter']) ? $_GET['filter'] : '';
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;

// Construir la URL de la API
$api_url = API_BASE_URL . 'movimientos.php?' . http_build_query(['page' => $page, 'filter' => $filter]);

// Realizar la petición a la API
$response = file_get_contents($api_url);
$data = json_decode($response, true);
$movimientos = $data['records'] ?? [];
$pagination = $data['pagination'] ?? null;
?>

<div class="dashboard-container">
    <?php include_once 'templates/sidebar.php'; ?>

    <main class="main-content">
        <div class="container">
            <div class="page-header">
                <h1>Gestión de Movimientos</h1>
                <a href="movimiento_form.php" class="btn">Nuevo Movimiento</a>
            </div>

            <?php
            if (isset($_GET['success'])) {
                echo '<p class="success-message">' . htmlspecialchars(urldecode($_GET['success'])) . '</p>';
            }
            if (isset($_GET['error'])) {
                echo '<p class="error-message">' . htmlspecialchars(urldecode($_GET['error'])) . '</p>';
            }
            ?>

            <div class="filter-container">
                <form method="GET" action="movimientos.php">
                    <div class="filters">
                        <input type="text" id="filter-input" name="filter" placeholder="Buscar por tipo, serie o número..." value="<?php echo htmlspecialchars($filter); ?>">
                        <button type="submit" class="btn">Filtrar</button>
                    </div>
                </form>
            </div>

            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Fecha</th>
                            <th>Tipo Mov.</th>
                            <th>Documento</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="movimientos-tbody">
                        <?php if (!empty($movimientos)): ?>
                            <?php foreach ($movimientos as $mov): ?>
                                <tr>
                                    <td data-label="ID"><?php echo htmlspecialchars($mov['id']); ?></td>
                                    <td data-label="Fecha"><?php echo htmlspecialchars($mov['fecha_movimiento']); ?></td>
                                    <td data-label="Tipo Mov."><?php echo htmlspecialchars($mov['nombre_movimiento']); ?></td>
                                    <td data-label="Documento"><?php echo htmlspecialchars(($mov['tipo_documento'] ?? '') . ' ' . ($mov['serie_documento'] ?? '') . '-' . ($mov['numero_documento'] ?? '')); ?></td>
                                    <td data-label="Estado"><span class="status status-<?php echo strtolower(htmlspecialchars($mov['estado'])); ?>"><?php echo htmlspecialchars($mov['estado']); ?></span></td>
                                    <td data-label="Acciones" class="actions-cell">
                                        <a href="movimiento_form.php?id=<?php echo $mov['id']; ?>" class="btn btn-edit">Editar</a>
                                        <a href="movimiento_delete_handler.php?id=<?php echo $mov['id']; ?>" class="btn btn-delete" onclick="return confirm('¿Está seguro de que desea anular este movimiento? Esta acción no se puede deshacer.');">Anular</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6">No se encontraron movimientos.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <?php if ($pagination && $pagination['total_pages'] > 1): ?>
                <div class="pagination">
                    <?php
                    $queryParams = ['filter' => $filter];
                    if ($pagination['page'] > 1) {
                        $queryParams['page'] = $pagination['page'] - 1;
                        echo '<a href="?' . http_build_query($queryParams) . '">&laquo; Anterior</a>';
                    }
                    for ($i = 1; $i <= $pagination['total_pages']; $i++) {
                        $queryParams['page'] = $i;
                        $activeClass = ($i == $pagination['page']) ? 'active' : '';
                        echo '<a href="?' . http_build_query($queryParams) . '" class="' . $activeClass . '">' . $i . '</a>';
                    }
                    if ($pagination['page'] < $pagination['total_pages']) {
                        $queryParams['page'] = $pagination['page'] + 1;
                        echo '<a href="?' . http_build_query($queryParams) . '">Siguiente &raquo;</a>';
                    }
                    ?>
                </div>
            <?php endif; ?>
        </div>
    </main>
</div>
