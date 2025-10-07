<?php
session_start();
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('Location: index.php?error=Por+favor+inicie+sesión');
    exit();
}
$page_title = 'Gestión de Movimientos';
include_once 'templates/header.php';
include_once __DIR__ . '/config.php';

// Función para obtener los almacenes activos
function getAlmacenes() {
    $api_url = API_BASE_URL . 'almacenes.php?action=getAllActive';
    $response = @file_get_contents($api_url);
    if ($response) {
        $data = json_decode($response, true);
        return $data['records'] ?? [];
    }
    return [];
}

$almacenes = getAlmacenes();

// Obtener filtros de la URL
$filter = $_GET['filter'] ?? '';
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$tipo_movimiento_filter = $_GET['tipo_movimiento'] ?? '';
$tipo_entidad_filter = $_GET['tipo_entidad'] ?? '';
$id_almacen_filter = $_GET['id_almacen'] ?? '';
$anio_filter = $_GET['anio'] ?? '';
$mes_filter = $_GET['mes'] ?? '';


// Construir el array de parámetros para la API
$api_params = [
    'page' => $page,
    'filter' => $filter,
    'tipo_movimiento' => $tipo_movimiento_filter,
    'tipo_entidad' => $tipo_entidad_filter,
    'id_almacen' => $id_almacen_filter,
    'anio' => $anio_filter,
    'mes' => $mes_filter,
];

// Construir la URL de la API
$api_url = API_BASE_URL . 'movimientos.php?' . http_build_query(array_filter($api_params));

// Realizar la petición a la API
$response = @file_get_contents($api_url);
$data = $response ? json_decode($response, true) : null;
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
            if (!$response) {
                 echo '<p class="error-message">Error: No se pudo conectar a la API para cargar los movimientos.</p>';
            }
            ?>

            <div class="filter-container">
                <form method="GET" action="movimientos.php">
                    <div class="filters">
                        <input type="text" name="filter" placeholder="Buscar por serie, número, etc." value="<?php echo htmlspecialchars($filter); ?>">

                        <select name="id_almacen">
                            <option value="">Todos los Almacenes</option>
                            <?php foreach ($almacenes as $almacen): ?>
                                <option value="<?php echo $almacen['id']; ?>" <?php echo ($id_almacen_filter == $almacen['id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($almacen['nombre']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>

                        <select name="anio">
                            <option value="">Año</option>
                            <?php for ($i = date('Y'); $i >= 2020; $i--): ?>
                                <option value="<?php echo $i; ?>" <?php echo ($anio_filter == $i) ? 'selected' : ''; ?>><?php echo $i; ?></option>
                            <?php endfor; ?>
                        </select>

                        <select name="mes">
                            <option value="">Mes</option>
                            <?php for ($i = 1; $i <= 12; $i++): ?>
                                <option value="<?php echo str_pad($i, 2, '0', STR_PAD_LEFT); ?>" <?php echo ($mes_filter == str_pad($i, 2, '0', STR_PAD_LEFT)) ? 'selected' : ''; ?>>
                                    <?php echo DateTime::createFromFormat('!m', $i)->format('F'); ?>
                                </option>
                            <?php endfor; ?>
                        </select>

                        <select name="tipo_movimiento">
                            <option value="">Tipo (E/S)</option>
                            <option value="E" <?php echo ($tipo_movimiento_filter == 'E') ? 'selected' : ''; ?>>Entrada</option>
                            <option value="S" <?php echo ($tipo_movimiento_filter == 'S') ? 'selected' : ''; ?>>Salida</option>
                        </select>
                        <select name="tipo_entidad">
                            <option value="">Tipo Entidad</option>
                            <option value="C" <?php echo ($tipo_entidad_filter == 'C') ? 'selected' : ''; ?>>Cliente</option>
                            <option value="P" <?php echo ($tipo_entidad_filter == 'P') ? 'selected' : ''; ?>>Proveedor</option>
                        </select>
                        <button type="submit" class="btn">Filtrar</button>
                    </div>
                </form>
            </div>

            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>Almacén</th>
                            <th>Fecha</th>
                            <th>Tipo</th>
                            <th>Cód. Movimiento</th>
                            <th>Documento</th>
                            <th>Tipo Entidad</th>
                            <th>Cliente/Proveedor</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($movimientos)): ?>
                            <?php foreach ($movimientos as $mov): ?>
                                <tr>
                                    <td data-label="Almacén"><?php echo htmlspecialchars($mov['almacen_nombre'] ?? 'N/A'); ?></td>
                                    <td data-label="Fecha"><?php echo htmlspecialchars($mov['fecha_movimiento']); ?></td>
                                    <td data-label="Tipo"><?php echo htmlspecialchars($mov['tipo_movimiento']); ?></td>
                                    <td data-label="Cód. Movimiento"><?php echo htmlspecialchars($mov['nombre_movimiento']); ?></td>
                                    <td data-label="Documento"><?php echo htmlspecialchars(($mov['tipo_documento_nombre'] ?? '') . ' ' . ($mov['serie_documento'] ?? '') . '-' . ($mov['numero_documento'] ?? '')); ?></td>
                                    <td data-label="Tipo Entidad"><?php echo htmlspecialchars($mov['tipo_entidad']); ?></td>
                                    <td data-label="Cliente/Proveedor"><?php echo htmlspecialchars($mov['entidad_nombre'] ?? 'N/A'); ?></td>
                                    <td data-label="Estado"><span class="status status-<?php echo strtolower(htmlspecialchars($mov['estado'])); ?>"><?php echo htmlspecialchars($mov['estado']); ?></span></td>
                                    <td data-label="Acciones" class="actions-cell">
                                        <a href="movimiento_form.php?id=<?php echo $mov['id']; ?>" class="btn btn-edit">Editar</a>
                                        <a href="movimiento_delete_handler.php?id=<?php echo $mov['id']; ?>" class="btn btn-delete" onclick="return confirm('¿Está seguro de que desea anular este movimiento? Esta acción no se puede deshacer.');">Anular</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="9">No se encontraron movimientos.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <?php if ($pagination && isset($pagination['total_pages']) && $pagination['total_pages'] > 1): ?>
                <div class="pagination">
                    <?php
                    $queryParams = $_GET;
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