<?php
session_start();
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('Location: index.php');
    exit();
}

require_once 'config.php';
$page_title = 'Formulario de Proveedor';
include_once 'templates/header.php';

$proveedor = [
    'id' => '', 'id_tipo_documento_identidad' => '', 'numero_documento' => '',
    'nombres_apellidos' => '', 'direccion' => '', 'codigo_ubigeo' => '',
    'email' => '', 'telefono' => '', 'estado' => 'Activado'
];
$is_edit = false;

if (isset($_GET['id'])) {
    $is_edit = true;
    $proveedor_id = $_GET['id'];
    $page_title = 'Editar Proveedor';
    $api_url = API_BASE_URL . "proveedores.php?id=$proveedor_id";
    $response = @file_get_contents($api_url);
    if ($response) {
        $proveedor = json_decode($response, true);
    }
}

function getIdentityDocumentTypes() {
    $api_url = API_BASE_URL . 'tipo_documento_identidad.php';
    $response = file_get_contents($api_url);
    $data = json_decode($response, true);
    return $data['records'] ?? [];
}

$document_types = getIdentityDocumentTypes();
?>

<div class="dashboard-container">
    <?php include_once 'templates/sidebar.php'; ?>
    <main class="main-content">
        <div class="container">
            <div class="page-header">
                <h1><?php echo $is_edit ? 'Editar Proveedor' : 'Crear Nuevo Proveedor'; ?></h1>
                <a href="proveedores.php" class="btn btn-secondary">Volver a Lista</a>
            </div>

            <div class="form-container">
                <form action="proveedor_handler.php" method="POST">
                    <input type="hidden" name="id" value="<?php echo htmlspecialchars($proveedor['id']); ?>">

                    <div class="form-group">
                        <label for="id_tipo_documento_identidad">Tipo de Documento</label>
                        <select id="id_tipo_documento_identidad" name="id_tipo_documento_identidad" required>
                            <option value="">Seleccione...</option>
                            <?php foreach ($document_types as $type): ?>
                                <option
                                    value="<?php echo $type['id']; ?>"
                                    data-codigo="<?php echo htmlspecialchars($type['codigo']); ?>"
                                    <?php echo ($proveedor['id_tipo_documento_identidad'] == $type['id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($type['nombre']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="numero_documento">N° de Documento</label>
                        <div style="display: flex; gap: 10px;">
                            <input type="text" id="numero_documento" name="numero_documento" value="<?php echo htmlspecialchars($proveedor['numero_documento']); ?>" required style="flex-grow: 1;">
                            <button type="button" class="btn" id="sunat-btn" style="flex-shrink: 0; display: none;">Sunat</button>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="nombres_apellidos">Nombres y Apellidos / Razón Social</label>
                        <input type="text" id="nombres_apellidos" name="nombres_apellidos" value="<?php echo htmlspecialchars($proveedor['nombres_apellidos']); ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="direccion">Dirección</label>
                        <input type="text" id="direccion" name="direccion" value="<?php echo htmlspecialchars($proveedor['direccion']); ?>">
                    </div>

                    <div class="form-group">
                        <label for="codigo_ubigeo">Código de Ubigeo</label>
                        <input type="text" id="codigo_ubigeo" name="codigo_ubigeo" value="<?php echo htmlspecialchars($proveedor['codigo_ubigeo']); ?>">
                    </div>

                    <div class="form-group">
                        <label for="email">Email</label>
                        <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($proveedor['email']); ?>">
                    </div>

                    <div class="form-group">
                        <label for="telefono">Teléfono</label>
                        <input type="text" id="telefono" name="telefono" value="<?php echo htmlspecialchars($proveedor['telefono']); ?>">
                    </div>

                    <?php if ($is_edit): ?>
                    <div class="form-group">
                        <label for="estado">Estado</label>
                        <select id="estado" name="estado">
                            <option value="Activado" <?php echo ($proveedor['estado'] === 'Activado') ? 'selected' : ''; ?>>Activado</option>
                            <option value="Desactivado" <?php echo ($proveedor['estado'] === 'Desactivado') ? 'selected' : ''; ?>>Desactivado</option>
                        </select>
                    </div>
                    <?php endif; ?>

                    <div class="form-actions">
                        <button type="submit" class="btn"><?php echo $is_edit ? 'Actualizar Proveedor' : 'Crear Proveedor'; ?></button>
                    </div>
                </form>
            </div>
        </div>
    </main>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const tipoDocumentoSelect = document.getElementById('id_tipo_documento_identidad');
    const sunatBtn = document.getElementById('sunat-btn');
    const numeroDocumentoInput = document.getElementById('numero_documento');
    const nombresInput = document.getElementById('nombres_apellidos');
    const direccionInput = document.getElementById('direccion');
    const ubigeoInput = document.getElementById('codigo_ubigeo');

    const validDocumentCodes = ['1', '6']; // 1 for DNI, 6 for RUC

    function toggleSunatButton() {
        const selectedOption = tipoDocumentoSelect.options[tipoDocumentoSelect.selectedIndex];
        const docCode = selectedOption ? selectedOption.getAttribute('data-codigo') : null;

        if (docCode && validDocumentCodes.includes(docCode)) {
            sunatBtn.style.display = 'inline-block';
        } else {
            sunatBtn.style.display = 'none';
        }
    }

    sunatBtn.addEventListener('click', function() {
        const selectedOption = tipoDocumentoSelect.options[tipoDocumentoSelect.selectedIndex];
        const docCode = selectedOption ? selectedOption.getAttribute('data-codigo') : null;
        const docNumber = numeroDocumentoInput.value.trim();

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
            return;
        }

        sunatBtn.textContent = 'Buscando...';
        sunatBtn.disabled = true;

        fetch(`consulta_api_externa.php?tipo=${queryType}&numero=${docNumber}`)
            .then(response => response.json())
            .then(data => {
                if (data.error) {
                    throw new Error(data.error);
                }
                nombresInput.value = data.nombre || '';
                direccionInput.value = data.direccion || '';
                ubigeoInput.value = data.ubigeo || '';
            })
            .catch(error => {
                console.error('Error al consultar la API:', error);
                alert('No se pudo obtener la información: ' + error.message);
            })
            .finally(() => {
                sunatBtn.textContent = 'Sunat';
                sunatBtn.disabled = false;
            });
    });

    toggleSunatButton();
    tipoDocumentoSelect.addEventListener('change', toggleSunatButton);
});
</script>

<?php
if (isset($_GET['error'])) {
    echo "<script>showAlert('Error de Validación', '" . htmlspecialchars($_GET['error']) . "');</script>";
}
?>