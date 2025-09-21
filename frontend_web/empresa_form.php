<?php
session_start();
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('Location: index.php');
    exit();
}

require_once 'config.php';

$page_title = 'Crear Empresa';
$empresa_data = [
    'id' => '', 'nombre_largo' => '', 'nombre_corto' => '', 'ruc' => '', 'direccion' => '',
    'id_departamento' => '', 'id_provincia' => '', 'id_distrito' => '', 'telefonos' => '',
    'email' => '', 'web' => '', 'logo_url' => '', 'observaciones' => '', 'estado' => 1,
    'sunat_envio_estado' => 0, 'sunat_api_url' => '', 'sunat_api_key' => ''
];
$is_editing = false;

if (isset($_GET['id'])) {
    $is_editing = true;
    $empresa_id = intval($_GET['id']);
    $page_title = 'Editar Empresa';

    $api_url = API_BASE_URL . "empresas.php?id=$empresa_id";
    $response = @file_get_contents($api_url);
    if ($response) {
        $empresa_data = json_decode($response, true);
    }
}

include_once 'templates/header.php';
?>

<div class="dashboard-container">
    <?php include_once 'templates/sidebar.php'; ?>
    <main class="main-content">
        <div class="container">
            <div class="page-header">
                <h1><?php echo $page_title; ?></h1>
                <a href="empresas.php" class="btn btn-secondary">Volver a la lista</a>
            </div>
            <div class="form-container">
                <form action="empresa_handler.php" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="id" value="<?php echo htmlspecialchars($empresa_data['id']); ?>">
                    <?php if ($is_editing): ?>
                        <input type="hidden" name="_method" value="PUT">
                    <?php endif; ?>

                    <div class="form-group">
                        <label for="nombre_largo">Nombre de Empresa Larga</label>
                        <input type="text" id="nombre_largo" name="nombre_largo" value="<?php echo htmlspecialchars($empresa_data['nombre_largo']); ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="nombre_corto">Nombre de Empresa Corta</label>
                        <input type="text" id="nombre_corto" name="nombre_corto" value="<?php echo htmlspecialchars($empresa_data['nombre_corto']); ?>">
                    </div>
                    <div class="form-group">
                        <label for="ruc">Número de RUC</label>
                        <input type="text" id="ruc" name="ruc" value="<?php echo htmlspecialchars($empresa_data['ruc']); ?>" required maxlength="11">
                    </div>
                    <div class="form-group">
                        <label for="direccion">Dirección</label>
                        <textarea id="direccion" name="direccion" rows="3"><?php echo htmlspecialchars($empresa_data['direccion']); ?></textarea>
                    </div>

                    <fieldset>
                        <legend>Ubicación</legend>
                        <div class="form-row">
                            <div class="form-group">
                                <label for="id_departamento">Departamento</label>
                                <select id="id_departamento" name="id_departamento"></select>
                            </div>
                            <div class="form-group">
                                <label for="id_provincia">Provincia</label>
                                <select id="id_provincia" name="id_provincia"></select>
                            </div>
                            <div class="form-group">
                                <label for="id_distrito">Distrito</label>
                                <select id="id_distrito" name="id_distrito"></select>
                            </div>
                        </div>
                    </fieldset>

                    <div class="form-group">
                        <label for="telefonos">Teléfonos</label>
                        <input type="text" id="telefonos" name="telefonos" value="<?php echo htmlspecialchars($empresa_data['telefonos']); ?>">
                    </div>
                    <div class="form-group">
                        <label for="email">Email</label>
                        <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($empresa_data['email']); ?>">
                    </div>
                    <div class="form-group">
                        <label for="web">Web</label>
                        <input type="url" id="web" name="web" value="<?php echo htmlspecialchars($empresa_data['web']); ?>">
                    </div>
                    <div class="form-group">
                        <label for="observaciones">Observaciones</label>
                        <textarea id="observaciones" name="observaciones" rows="3"><?php echo htmlspecialchars($empresa_data['observaciones']); ?></textarea>
                    </div>
                    <div class="form-group">
                        <label for="estado">Estado</label>
                        <select id="estado" name="estado">
                            <option value="1" <?php echo ($empresa_data['estado'] == 1) ? 'selected' : ''; ?>>Activo</option>
                            <option value="0" <?php echo ($empresa_data['estado'] == 0) ? 'selected' : ''; ?>>Inactivo</option>
                        </select>
                    </div>

                    <fieldset>
                        <legend>Logo de la Empresa</legend>
                        <div class="form-group">
                            <label for="logo">Cargar Logo</label>
                            <input type="file" id="logo" name="logo" accept="image/*">
                            <input type="hidden" name="remove_logo" id="remove_logo" value="0">
                            <div id="logo-preview-container" style="margin-top: 10px; <?php echo empty($empresa_data['logo_url']) ? 'display: none;' : ''; ?>">
                                <img id="logo-preview" src="<?php echo htmlspecialchars($empresa_data['logo_url']); ?>" alt="Logo Preview" style="max-width: 150px; max-height: 150px;">
                                <span id="logo-name"><?php echo basename($empresa_data['logo_url']); ?></span>
                                <button type="button" id="remove-logo-btn" class="btn btn-sm btn-delete">X</button>
                            </div>
                        </div>
                    </fieldset>

                    <fieldset>
                        <legend>Envío Sunat</legend>
                        <div class="form-group">
                            <label for="sunat_envio_estado">Estado de Envío a SUNAT</label>
                            <select id="sunat_envio_estado" name="sunat_envio_estado">
                                <option value="1" <?php echo ($empresa_data['sunat_envio_estado'] == 1) ? 'selected' : ''; ?>>Activado</option>
                                <option value="0" <?php echo ($empresa_data['sunat_envio_estado'] == 0) ? 'selected' : ''; ?>>Desactivado</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="sunat_api_url">URL API Sunat</label>
                            <input type="text" id="sunat_api_url" name="sunat_api_url" value="<?php echo htmlspecialchars($empresa_data['sunat_api_url']); ?>">
                        </div>
                        <div class="form-group">
                            <label for="sunat_api_key">Key API Sunat</label>
                            <input type="text" id="sunat_api_key" name="sunat_api_key" value="<?php echo htmlspecialchars($empresa_data['sunat_api_key']); ?>">
                        </div>
                    </fieldset>

                    <div class="form-actions">
                        <button type="submit" class="btn"><?php echo $is_editing ? 'Actualizar Empresa' : 'Grabar Empresa'; ?></button>
                    </div>
                </form>
            </div>
        </div>
    </main>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const apiBaseUrl = '<?php echo API_BASE_URL; ?>';
    const depSelect = document.getElementById('id_departamento');
    const provSelect = document.getElementById('id_provincia');
    const distSelect = document.getElementById('id_distrito');

    const selectedDep = '<?php echo $empresa_data['id_departamento']; ?>';
    const selectedProv = '<?php echo $empresa_data['id_provincia']; ?>';
    const selectedDist = '<?php echo $empresa_data['id_distrito']; ?>';

    function loadSelect(selectElement, data, selectedValue) {
        selectElement.innerHTML = '<option value="">Seleccione...</option>';
        data.forEach(item => {
            const option = new Option(item.nombre, item.id, false, item.id === selectedValue);
            selectElement.add(option);
        });
        selectElement.disabled = false;
    }

    function fetchAndLoad(url, selectElement, selectedValue, callback) {
        fetch(url)
            .then(response => response.json())
            .then(data => {
                loadSelect(selectElement, data.records || [], selectedValue);
                if (callback) callback();
            });
    }

    // Load Departments
    fetchAndLoad(`${apiBaseUrl}departamentos.php`, depSelect, selectedDep, () => {
        if (selectedDep) {
            depSelect.dispatchEvent(new Event('change'));
        }
    });

    // Department change event
    depSelect.addEventListener('change', function() {
        distSelect.innerHTML = '<option value="">Seleccione...</option>';
        distSelect.disabled = true;
        if (this.value) {
            fetchAndLoad(`${apiBaseUrl}provincias.php?dep_id=${this.value}`, provSelect, selectedProv, () => {
                if (selectedProv) {
                     provSelect.dispatchEvent(new Event('change'));
                }
            });
        } else {
            provSelect.innerHTML = '<option value="">Seleccione...</option>';
            provSelect.disabled = true;
        }
    });

    // Province change event
    provSelect.addEventListener('change', function() {
        if (this.value) {
            fetchAndLoad(`${apiBaseUrl}distritos.php?prov_id=${this.value}`, distSelect, selectedDist);
        } else {
            distSelect.innerHTML = '<option value="">Seleccione...</option>';
            distSelect.disabled = true;
        }
    });

    // Logo Preview
    const logoInput = document.getElementById('logo');
    const logoPreviewContainer = document.getElementById('logo-preview-container');
    const logoPreview = document.getElementById('logo-preview');
    const logoName = document.getElementById('logo-name');
    const removeLogoBtn = document.getElementById('remove-logo-btn');
    const removeLogoHidden = document.getElementById('remove_logo');

    logoInput.addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(event) {
                logoPreview.src = event.target.result;
                logoName.textContent = file.name;
                logoPreviewContainer.style.display = 'block';
                removeLogoHidden.value = '0';
            }
            reader.readAsDataURL(file);
        }
    });

    removeLogoBtn.addEventListener('click', function() {
        logoInput.value = ''; // Clear file input
        logoPreview.src = '';
        logoName.textContent = '';
        logoPreviewContainer.style.display = 'none';
        removeLogoHidden.value = '1';
    });
});
</script>
