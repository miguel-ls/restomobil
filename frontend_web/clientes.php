<?php
session_start();
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('Location: index.php');
    exit();
}

$page_title = 'Gestión de Clientes';
include_once 'templates/header.php';
require_once 'config.php';

function getClientes() {
    $api_url = API_BASE_URL . 'clientes.php';
    $ch = curl_init($api_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    $response = curl_exec($ch);
    curl_close($ch);
    return json_decode($response, true);
}

$clientes_data = getClientes();
?>

<div class="dashboard-container">
    <?php include_once 'templates/sidebar.php'; ?>
    <main class="main-content">
        <div class="container">
            <div class="page-header">
                <h1>Catálogo de Clientes</h1>
                <a href="cliente_form.php" class="btn">Nuevo Cliente</a>
            </div>

            <div class="filters">
                <input type="text" id="filter-numero-documento" placeholder="Filtrar por N° Documento...">
                <input type="text" id="filter-nombres" placeholder="Filtrar por Nombres...">
                <input type="text" id="filter-email" placeholder="Filtrar por Email...">
                <input type="text" id="filter-telefono" placeholder="Filtrar por Teléfono...">
                <select id="filter-estado">
                    <option value="">Todos los Estados</option>
                    <option value="Activado">Activado</option>
                    <option value="Desactivado">Desactivado</option>
                </select>
            </div>

            <div class="table-container">
                <table id="clientes-table">
                    <thead>
                        <tr>
                            <th>Tipo Doc.</th>
                            <th>N° Documento</th>
                            <th>Nombres y Apellidos</th>
                            <th>Dirección</th>
                            <th>Email</th>
                            <th>Teléfono</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (isset($clientes_data['records']) && !empty($clientes_data['records'])): ?>
                            <?php foreach ($clientes_data['records'] as $cliente): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($cliente['tipo_documento_nombre']); ?></td>
                                    <td><?php echo htmlspecialchars($cliente['numero_documento']); ?></td>
                                    <td><?php echo htmlspecialchars($cliente['nombres_apellidos']); ?></td>
                                    <td><?php echo htmlspecialchars($cliente['direccion']); ?></td>
                                    <td><?php echo htmlspecialchars($cliente['email']); ?></td>
                                    <td><?php echo htmlspecialchars($cliente['telefono']); ?></td>
                                    <td>
                                        <span class="status <?php echo $cliente['estado'] === 'Activado' ? 'status-active' : 'status-inactive'; ?>">
                                            <?php echo htmlspecialchars($cliente['estado']); ?>
                                        </span>
                                    </td>
                                    <td class="actions-cell">
                                        <a href="cliente_form.php?id=<?php echo $cliente['id']; ?>" class="btn btn-edit">Editar</a>
                                        <a href="cliente_delete_handler.php?id=<?php echo $cliente['id']; ?>" class="btn btn-delete" onclick="return confirm('¿Estás seguro de que quieres eliminar permanentemente este cliente? Esta acción no se puede deshacer.');">Eliminar</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="8">No se encontraron clientes.</td></tr>
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
        numero_documento: document.getElementById('filter-numero-documento'),
        nombres: document.getElementById('filter-nombres'),
        email: document.getElementById('filter-email'),
        telefono: document.getElementById('filter-telefono'),
        estado: document.getElementById('filter-estado')
    };
    const tableRows = document.querySelectorAll('#clientes-table tbody tr');

    function filterTable() {
        const filterValues = {
            numero_documento: filters.numero_documento.value.toLowerCase(),
            nombres: filters.nombres.value.toLowerCase(),
            email: filters.email.value.toLowerCase(),
            telefono: filters.telefono.value.toLowerCase(),
            estado: filters.estado.value.toLowerCase()
        };

        tableRows.forEach(row => {
            if (row.cells.length > 1) {
                const cells = {
                    numero_documento: row.cells[1].textContent.toLowerCase(),
                    nombres: row.cells[2].textContent.toLowerCase(),
                    email: row.cells[4].textContent.toLowerCase(),
                    telefono: row.cells[5].textContent.toLowerCase(),
                    estado: row.cells[6].textContent.trim().toLowerCase()
                };

                const matchesNumeroDoc = cells.numero_documento.includes(filterValues.numero_documento);
                const matchesNombres = cells.nombres.includes(filterValues.nombres);
                const matchesEmail = cells.email.includes(filterValues.email);
                const matchesTelefono = cells.telefono.includes(filterValues.telefono);
                const matchesEstado = filterValues.estado === '' || cells.estado === filterValues.estado;

                if (matchesNumeroDoc && matchesNombres && matchesEmail && matchesTelefono && matchesEstado) {
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

<?php
// Check for success or error messages in the URL and display the modal
if (isset($_GET['success'])) {
    echo "<script>showAlert('Éxito', '" . htmlspecialchars($_GET['success']) . "');</script>";
}
if (isset($_GET['error'])) {
    echo "<script>showAlert('Error', '" . htmlspecialchars($_GET['error']) . "');</script>";
}
?>
