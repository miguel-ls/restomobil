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
            </div>

            <div class="filter-container">
                <div class="filter-group">
                    <input type="text" id="search-box" placeholder="Buscar Cliente (Nombre o RUC)">
                    <button type="button" class="btn" id="sunat-btn">SUNAT</button>
                </div>
            </div>

            <fieldset class="form-frame">
                <form action="cliente_handler.php" method="POST" id="cliente-form">
                    <input type="hidden" name="id" id="cliente-id">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="id_tipo_documento_identidad">Tipo de Documento</label>
                            <select id="id_tipo_documento_identidad" name="id_tipo_documento_identidad" required>
                                <option value="">Seleccione...</option>
                                <?php foreach ($document_types as $type): ?>
                                    <option
                                        value="<?php echo $type['id']; ?>"
                                        data-codigo="<?php echo htmlspecialchars($type['codigo']); ?>">
                                        <?php echo htmlspecialchars($type['nombre']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="numero_documento">RUC / DNI</label>
                            <input type="text" id="numero_documento" name="numero_documento" required>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="nombres_apellidos">Nombre del Cliente</label>
                            <input type="text" id="nombres_apellidos" name="nombres_apellidos" required>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="direccion">Dirección</label>
                            <input type="text" id="direccion" name="direccion">
                        </div>
                        <div class="form-group">
                            <label for="codigo_ubigeo">Ubigeo</label>
                            <input type="text" id="codigo_ubigeo" name="codigo_ubigeo">
                        </div>
                    </div>
                    <div class="form-actions">
                        <button type="submit" class="btn" id="form-submit-btn">Crear Cliente</button>
                    </div>
                </form>
            </fieldset>

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
                                <tr data-cliente='<?php echo json_encode($cliente); ?>'>
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
                                        <button type="button" class="btn btn-edit">Editar</button>
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
    const searchBox = document.getElementById('search-box');
    const tableRows = document.querySelectorAll('#clientes-table tbody tr');
    const sunatBtn = document.getElementById('sunat-btn');
    const clienteForm = document.getElementById('cliente-form');
    const clienteIdInput = document.getElementById('cliente-id');
    const tipoDocumentoSelect = document.getElementById('id_tipo_documento_identidad');
    const numeroDocumentoInput = document.getElementById('numero_documento');
    const nombresApellidosInput = document.getElementById('nombres_apellidos');
    const direccionInput = document.getElementById('direccion');
    const codigoUbigeoInput = document.getElementById('codigo_ubigeo');
    const submitButton = document.getElementById('form-submit-btn');

    function filterTable() {
        const searchTerm = searchBox.value.toLowerCase();
        tableRows.forEach(row => {
            if (row.cells.length > 1) {
                const docNumber = row.cells[1].textContent.toLowerCase();
                const name = row.cells[2].textContent.toLowerCase();
                if (docNumber.includes(searchTerm) || name.includes(searchTerm)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            }
        });
    }

    function populateForm(row) {
        const clienteData = JSON.parse(row.getAttribute('data-cliente'));

        clienteIdInput.value = clienteData.id;
        tipoDocumentoSelect.value = clienteData.id_tipo_documento_identidad;
        numeroDocumentoInput.value = clienteData.numero_documento;
        nombresApellidosInput.value = clienteData.nombres_apellidos;
        direccionInput.value = clienteData.direccion;
        codigoUbigeoInput.value = clienteData.codigo_ubigeo;

        submitButton.textContent = 'Actualizar Cliente';
    }

    function resetForm() {
        clienteForm.reset();
        clienteIdInput.value = '';
        submitButton.textContent = 'Crear Cliente';
    }

    searchBox.addEventListener('keyup', filterTable);

    tableRows.forEach(row => {
        // We add the listener to the whole row, except the last cell with the buttons
        for (let i = 0; i < row.cells.length - 1; i++) {
            row.cells[i].addEventListener('click', () => populateForm(row));
        }

        const editButton = row.querySelector('.btn-edit');
        if (editButton) {
            editButton.addEventListener('click', () => populateForm(row));
        }
    });

    sunatBtn.addEventListener('click', function() {
        const docNumber = searchBox.value.trim();
        let queryType = '';
        let docCode = '';

        if (docNumber.length === 8) {
            queryType = 'dni';
            docCode = '1'; // Assuming '1' is the code for DNI
        } else if (docNumber.length === 11) {
            queryType = 'ruc';
            docCode = '6'; // Assuming '6' is the code for RUC
        } else {
            alert('Por favor, ingrese un número de DNI (8 dígitos) o RUC (11 dígitos) en el buscador.');
            return;
        }

        sunatBtn.textContent = 'Buscando...';
        sunatBtn.disabled = true;
        resetForm();

        fetch(`consulta_api_externa.php?tipo=${queryType}&numero=${docNumber}`)
            .then(response => response.json())
            .then(data => {
                if (data.error) {
                    throw new Error(data.error);
                }
                nombresApellidosInput.value = data.nombre || '';
                direccionInput.value = data.direccion || '';
                codigoUbigeoInput.value = data.ubigeo || '';
                numeroDocumentoInput.value = docNumber;

                // Auto-seleccionar el tipo de documento en el formulario
                for (let i = 0; i < tipoDocumentoSelect.options.length; i++) {
                    if (tipoDocumentoSelect.options[i].getAttribute('data-codigo') === docCode) {
                        tipoDocumentoSelect.selectedIndex = i;
                        break;
                    }
                }
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

    // Add a clear button to the form
    const clearButton = document.createElement('button');
    clearButton.type = 'button';
    clearButton.textContent = 'Limpiar';
    clearButton.classList.add('btn', 'btn-secondary');
    clearButton.addEventListener('click', resetForm);
    submitButton.parentNode.insertBefore(clearButton, submitButton.nextSibling);
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
