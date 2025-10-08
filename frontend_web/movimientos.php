<?php
session_start();
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('Location: index.php?error=Por+favor+inicie+sesión');
    exit();
}
$page_title = 'Gestión de Movimientos';
include_once 'templates/header.php';
include_once __DIR__ . '/config.php';

// Obtener filtros de la URL
$filter = $_GET['filter'] ?? '';
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$tipo_movimiento_filter = $_GET['tipo_movimiento'] ?? '';
$tipo_entidad_filter = $_GET['tipo_entidad'] ?? '';
$anio_filter = $_GET['anio'] ?? '';
$mes_filter = $_GET['mes'] ?? '';

// Construir el array de parámetros para la API
$api_params = [
    'page' => $page,
    'filter' => $filter,
    'tipo_movimiento' => $tipo_movimiento_filter,
    'tipo_entidad' => $tipo_entidad_filter,
    'anio' => $anio_filter,
    'mes' => $mes_filter,
];

// Construir la URL de la API
$api_url = API_BASE_URL . 'movimientos.php?' . http_build_query(array_filter($api_params));

// Realizar la petición a la API con cURL
$ch = curl_init($api_url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curl_error = curl_error($ch);
curl_close($ch);

$data = null;
$movimientos = [];
$pagination = null;
$api_error_message = '';

if ($http_code == 200) {
    $data = json_decode($response, true);
    $movimientos = $data['records'] ?? [];
    $pagination = $data['pagination'] ?? null;
} else {
    $api_error_message = "Error: No se pudo conectar a la API para cargar los movimientos (Código: {$http_code})";
    if (!empty($curl_error)) {
        $api_error_message .= " - " . $curl_error;
    }
}
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
            if (!empty($api_error_message)) {
                echo '<p class="error-message">' . htmlspecialchars($api_error_message) . '</p>';
            }
            ?>

            <div class="filter-container">
                <form method="GET" action="movimientos.php">
                    <div class="filters">
                        <select name="anio">
                            <option value="">Año</option>
                            <?php
                            $current_year = date('Y');
                            for ($i = $current_year; $i >= 2020; $i--) {
                                $selected = ($anio_filter == $i) ? 'selected' : '';
                                echo "<option value='{$i}' {$selected}>{$i}</option>";
                            }
                            ?>
                        </select>
                        <select name="mes">
                            <option value="">Mes</option>
                            <?php
                            $meses = [
                                '01' => 'Enero', '02' => 'Febrero', '03' => 'Marzo', '04' => 'Abril',
                                '05' => 'Mayo', '06' => 'Junio', '07' => 'Julio', '08' => 'Agosto',
                                '09' => 'Septiembre', '10' => 'Octubre', '11' => 'Noviembre', '12' => 'Diciembre'
                            ];
                            foreach ($meses as $num => $nombre) {
                                $selected = ($mes_filter == $num) ? 'selected' : '';
                                echo "<option value='{$num}' {$selected}>{$nombre}</option>";
                            }
                            ?>
                        </select>
                        <input type="text" name="filter" placeholder="Buscar por serie, número, etc." value="<?php echo htmlspecialchars($filter); ?>">
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
                            <th>ID</th>
                            <th>Fecha</th>
                            <th>Almacen</th>
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
                                    <td data-label="ID"><?php echo htmlspecialchars($mov['id']); ?></td>
                                    <td data-label="Fecha"><?php echo htmlspecialchars($mov['fecha_movimiento']); ?></td>
                                    <td data-label="Almacen"><?php echo htmlspecialchars($mov['nombre_almacen']); ?></td>

                                    

                                    <td>
                                        <span class="status <?php echo $mov['tipo_movimiento'] === 'E' ? 'status-active' : 'status-inactive'; ?>">
                                            <?php echo htmlspecialchars(ucfirst($mov['tipo_movimiento'])); ?>
                                        </span>
                                    </td>

                                    

                                    <td data-label="Cód. Movimiento"><?php echo htmlspecialchars($mov['nombre_movimiento']); ?></td>
                                    <td data-label="Documento"><?php echo htmlspecialchars(($mov['tipo_documento_nombre'] ?? '') . ' ' . ($mov['serie_documento'] ?? '') . '-' . ($mov['numero_documento'] ?? '')); ?></td>
                                    <td data-label="Tipo Entidad"><?php echo htmlspecialchars($mov['tipo_entidad']); ?></td>
                                    <td data-label="Cliente/Proveedor"><?php echo htmlspecialchars($mov['entidad_nombre'] ?? 'N/A'); ?></td>
                                    
                                    <td>
                                        <span class="status <?php echo $mov['estado'] === 'Activado' ? 'status-active' : 'status-inactive'; ?>">
                                            <?php echo htmlspecialchars(ucfirst($mov['estado'])); ?>
                                        </span>
                                    </td>
                                    
                                    
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

            <?php if ($pagination && $pagination['total_pages'] > 1): ?>
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