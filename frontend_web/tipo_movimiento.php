<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

$page_title = 'Tipo de Movimiento';
require_once 'templates/header.php';
require_once 'config.php';

// Función para obtener los datos de la API
function getTiposMovimiento($filters = []) {
    $api_url = API_BASE_URL . 'tipo_movimiento.php';
    $query_params = http_build_query($filters);
    if (!empty($query_params)) {
        $api_url .= '?' . $query_params;
    }

    $ch = curl_init($api_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($ch);

    if (curl_errno($ch)) {
        // Manejo de error de cURL
        $error_message = curl_error($ch);
        curl_close($ch);
        return ['error' => "Error al conectar con la API: $error_message"];
    }

    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($http_code >= 200 && $http_code < 300) {
        return json_decode($response, true);
    } elseif ($http_code == 404) {
        return []; // No se encontraron registros
    }

    // Otro tipo de error
    $error_data = json_decode($response, true);
    return ['error' => $error_data['message'] ?? 'Error desconocido en la API'];
}

$tipos_movimiento = getTiposMovimiento();

?>

<div class="dashboard-container">
    <?php require_once 'templates/sidebar.php'; ?>

    <main class="main-content">
        <div class="container">
            <div class="page-header">
                <h1>Gestión de Tipos de Movimiento</h1>
                <a href="tipo_movimiento_form.php" class="btn">Nuevo Tipo de Movimiento</a>
            </div>

            <div class="filter-container">
                <div class="filters">
                    <input type="text" id="filter-descripcion" placeholder="Filtrar por descripción...">
                    <select id="filter-estado">
                        <option value="">Todos los Estados</option>
                        <option value="activado">Activado</option>
                        <option value="desactivado">Desactivado</option>
                    </select>
                </div>
            </div>

            <div class="table-container">
                <table id="movimientos-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Tipo</th>
                            <th>Código</th>
                            <th>Descripción</th>
                            <th>Estado</th>
                            <th>Fecha Creación</th>
                            <th>Fecha Modificación</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($tipos_movimiento) && !isset($tipos_movimiento['error'])): ?>
                            <?php foreach ($tipos_movimiento as $movimiento): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($movimiento['id']); ?></td>
                                    <td><?php echo htmlspecialchars($movimiento['tipo'] == 'E' ? 'Entrada' : 'Salida'); ?></td>
                                    <td><?php echo htmlspecialchars($movimiento['codigo']); ?></td>
                                    <td><?php echo htmlspecialchars($movimiento['descripcion']); ?></td>
                                    <td>
                                        <span class="status <?php echo $movimiento['estado'] === 'activado' ? 'status-active' : 'status-inactive'; ?>">
                                            <?php echo htmlspecialchars(ucfirst($movimiento['estado'])); ?>
                                        </span>
                                    </td>
                                    <td><?php echo htmlspecialchars($movimiento['fecha_creacion']); ?></td>
                                    <td><?php echo htmlspecialchars($movimiento['fecha_modificacion']); ?></td>
                                    <td class="actions-cell">
                                        <a href="tipo_movimiento_form.php?id=<?php echo $movimiento['id']; ?>" class="btn btn-edit">Editar</a>
                                        <a href="tipo_movimiento_delete_handler.php?id=<?php echo $movimiento['id']; ?>" class="btn btn-delete" onclick="return confirm('¿Estás seguro de que quieres desactivar este registro?');">Eliminar</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="8">No se encontraron registros.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const filterDescripcion = document.getElementById('filter-descripcion');
    const filterEstado = document.getElementById('filter-estado');
    const tableRows = document.querySelectorAll('#movimientos-table tbody tr');

    function filterTable() {
        const descripcionValue = filterDescripcion.value.toLowerCase();
        const estadoValue = filterEstado.value;

        tableRows.forEach(row => {
            if (row.cells.length > 1) {
                const descripcion = row.cells[3].textContent.toLowerCase();
                const estado = row.cells[4].textContent.trim().toLowerCase();

                const matchesDescripcion = descripcion.includes(descripcionValue);
                const matchesEstado = estadoValue === '' || estado === estadoValue;

                if (matchesDescripcion && matchesEstado) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            }
        });
    }

    filterDescripcion.addEventListener('keyup', filterTable);
    filterEstado.addEventListener('change', filterTable);
});
</script>

<?php
if (isset($_GET['success'])) {
    echo "<script>showAlert('Éxito', '" . htmlspecialchars($_GET['success']) . "');</script>";
}
if (isset($_GET['error'])) {
    echo "<script>showAlert('Error', '" . htmlspecialchars($_GET['error']) . "');</script>";
}
?>