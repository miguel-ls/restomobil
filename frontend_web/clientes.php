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

function getIdentityDocumentTypes() {
    $api_url = API_BASE_URL . 'tipo_documento_identidad.php';
    $response = file_get_contents($api_url);
    $data = json_decode($response, true);
    return $data['records'] ?? [];
}

$clientes_data = getClientes();
$document_types = getIdentityDocumentTypes();
?>

<div class="dashboard-container">
    <?php include_once 'templates/sidebar.php'; ?>
    <main class="main-content">
        <div class="container">
            <div class="page-header">
                <h1>Catálogo de Clientes</h1>
                <div class="page-header-actions">
                    <a href="cliente_form.php" class="btn">Nuevo Cliente</a>
                </div>
            </div>

            <div class="filters">
                <select id="filter-tipo-documento">
                    <option value="">Todos los Tipos</option>
                    <?php foreach ($document_types as $type): ?>
                        <option value="<?php echo htmlspecialchars($type['nombre']); ?>" data-codigo="<?php echo htmlspecialchars($type['codigo']); ?>">
                            <?php echo htmlspecialchars($type['nombre']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <div class="filter-group">
                    <input type="text" id="filter-numero-documento" placeholder="Filtrar por N° Documento...">
                    <button type="button" class="btn" id="sunat-btn">SUNAT</button>
                </div>
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
                            <th>Ubigeo</th>
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
                                    <td><?php echo htmlspecialchars($cliente['codigo_ubigeo']); ?></td>
                                    <td><?php echo htmlspecialchars($cliente['email']); ?></td>
                                    <td><?php echo htmlspecialchars($cliente['telefono']); ?></td>
                                    <td>
                                        <span class="status <?php echo $cliente['estado'] === 'Activado' ? 'status-active' : 'status-inactive'; ?>">
                                            <?php echo htmlspecialchars($cliente['estado']); ?>
                                        </span>
                                    </td>
                                    <td class="actions-cell">
                                        <a href="cliente_form.php?id=<?php echo $cliente['id']; ?>" class="btn btn-edit">Editar</a>
                                        <a href="cliente_delete_handler.php?id=<?php echo $cliente['id']; ?>" class="btn btn-delete" onclick="return confirm('¿Estás seguro de que quieres desactivar este cliente?');">Eliminar</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="9">No se encontraron clientes.</td></tr>
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
        tipo_documento: document.getElementById('filter-tipo-documento'),
        numero_documento: document.getElementById('filter-numero-documento'),
        nombres: document.getElementById('filter-nombres'),
        email: document.getElementById('filter-email'),
        telefono: document.getElementById('filter-telefono'),
        estado: document.getElementById('filter-estado')
    };
    const tableRows = document.querySelectorAll('#clientes-table tbody tr');
    const sunatBtn = document.getElementById('sunat-btn');

    function filterTable() {
        const filterValues = {
            tipo_documento: filters.tipo_documento.value.toLowerCase(),
            numero_documento: filters.numero_documento.value.toLowerCase(),
            nombres: filters.nombres.value.toLowerCase(),
            email: filters.email.value.toLowerCase(),
            telefono: filters.telefono.value.toLowerCase(),
            estado: filters.estado.value.toLowerCase()
        };

        tableRows.forEach(row => {
            if (row.cells.length > 1) {
                const cells = {
                    tipo_documento: row.cells[0].textContent.toLowerCase(),
                    numero_documento: row.cells[1].textContent.toLowerCase(),
                    nombres: row.cells[2].textContent.toLowerCase(),
                    email: row.cells[5].textContent.toLowerCase(),
                    telefono: row.cells[6].textContent.toLowerCase(),
                    estado: row.cells[7].textContent.trim().toLowerCase()
                };

                const matchesTipoDoc = filterValues.tipo_documento === '' || cells.tipo_documento.includes(filterValues.tipo_documento);
                const matchesNumeroDoc = cells.numero_documento.includes(filterValues.numero_documento);
                const matchesNombres = cells.nombres.includes(filterValues.nombres);
                const matchesEmail = cells.email.includes(filterValues.email);
                const matchesTelefono = cells.telefono.includes(filterValues.telefono);
                const matchesEstado = filterValues.estado === '' || cells.estado === filterValues.estado;

                if (matchesTipoDoc && matchesNumeroDoc && matchesNombres && matchesEmail && matchesTelefono && matchesEstado) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            }
        });
    }

    for (const key in filters) {
        if (filters[key]) {
            filters[key].addEventListener('keyup', filterTable);
            filters[key].addEventListener('change', filterTable);
        }
    }

    sunatBtn.addEventListener('click', function() {
        const selectedOption = filters.tipo_documento.options[filters.tipo_documento.selectedIndex];
        const docCode = selectedOption ? selectedOption.getAttribute('data-codigo') : null;
        const docNumber = filters.numero_documento.value.trim();

        if (!docCode || !docNumber) {
            alert('Por favor, seleccione un tipo de documento y ingrese un número.');
            return;
        }

        let queryType = '';
        if (docCode === '1') {
            queryType = 'dni';
        } else if (docCode === '6') {
            queryType = 'ruc';
        } else {
            alert('La consulta solo es válida para DNI o RUC.');
            return;
        }

        sunatBtn.textContent = 'Buscando...';
        sunatBtn.disabled = true;

        fetch(`consulta_api_externa.php?tipo=${queryType}&numero=${docNumber}`)
            .then(response => {
                if (!response.ok) {
                    throw new Error('La respuesta de la red no fue exitosa.');
                }
                return response.json();
            })
            .then(data => {
                if (data.error) {
                    throw new Error(data.error);
                }
                filters.nombres.value = data.nombre || '';
                // No se autocompleta la dirección en el filtro, solo en el form.

                // Auto-seleccionar el tipo de documento y filtrar
                for (let i = 0; i < filters.tipo_documento.options.length; i++) {
                    if (filters.tipo_documento.options[i].getAttribute('data-codigo') === docCode) {
                        filters.tipo_documento.selectedIndex = i;
                        break;
                    }
                }

                filterTable(); // Aplicar todos los filtros
            })
            .catch(error => {
                console.error('Error al consultar la API:', error);
                alert('No se pudo obtener la información: ' + error.message);
            })
            .finally(() => {
                sunatBtn.textContent = 'SUNAT';
                sunatBtn.disabled = false;
            });
    });
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
