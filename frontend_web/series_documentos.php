<?php
session_start();
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('Location: index.php');
    exit();
}

$page_title = 'Gestión de Series de Documentos';
include_once 'templates/header.php';

function getSeries() {
    require_once 'config.php';
    $api_url = API_BASE_URL . 'series_documentos.php';
    $ch = curl_init($api_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    $response = curl_exec($ch);
    curl_close($ch);
    return json_decode($response, true);
}

$series_data = getSeries();
?>

<div class="dashboard-container">
    <?php include_once 'templates/sidebar.php'; ?>
    <main class="main-content">
        <div class="container">
            <div class="page-header">
                <h1>Catálogo de Series de Documentos</h1>
                <a href="serie_documento_form.php" class="btn">Crear Serie</a>
            </div>

            <div class="filters">
                <input type="text" id="filter-tipo-documento" placeholder="Filtrar por Tipo de Documento...">
                <input type="text" id="filter-serie" placeholder="Filtrar por Serie...">
            </div>

            <div class="table-container">
                <table id="series-table">
                    <thead>
                        <tr>
                            <th>Tipo de Documento</th>
                            <th>Serie</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (isset($series_data['records']) && !empty($series_data['records'])): ?>
                            <?php foreach ($series_data['records'] as $serie): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($serie['nombre_tipo_documento']); ?></td>
                                    <td><?php echo htmlspecialchars($serie['serie']); ?></td>
                                    <td>
                                        <span class="status <?php echo $serie['estado'] ? 'status-active' : 'status-inactive'; ?>">
                                            <?php echo $serie['estado'] ? 'Activo' : 'Inactivo'; ?>
                                        </span>
                                    </td>
                                    <td class="actions-cell">
                                        <a href="serie_documento_form.php?id=<?php echo $serie['id']; ?>" class="btn btn-edit">Editar</a>
                                        <a href="serie_documento_delete_handler.php?id=<?php echo $serie['id']; ?>" class="btn btn-delete" onclick="return confirm('¿Estás seguro?');">Eliminar</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="4">No se encontraron series de documentos.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const filterTipo = document.getElementById('filter-tipo-documento');
    const filterSerie = document.getElementById('filter-serie');
    const tableRows = document.querySelectorAll('#series-table tbody tr');

    function filterTable() {
        const tipoText = filterTipo.value.toLowerCase();
        const serieText = filterSerie.value.toLowerCase();

        tableRows.forEach(row => {
            const tipoCell = row.cells[0].textContent.toLowerCase();
            const serieCell = row.cells[1].textContent.toLowerCase();

            const matchesTipo = tipoCell.includes(tipoText);
            const matchesSerie = serieCell.includes(serieText);

            if (matchesTipo && matchesSerie) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    }

    filterTipo.addEventListener('keyup', filterTable);
    filterSerie.addEventListener('keyup', filterTable);
});
</script>
