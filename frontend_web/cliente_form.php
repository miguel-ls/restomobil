<?php
session_start();
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('Location: index.php');
    exit();
}

require_once 'config.php';
$page_title = 'Formulario de Cliente';
include_once 'templates/header.php';

$cliente = [
    'id' => '', 'id_tipo_documento_identidad' => '', 'numero_documento' => '',
    'nombres_apellidos' => '', 'direccion' => '', 'codigo_ubigeo' => '',
    'email' => '', 'telefono' => '', 'estado' => 'Activado'
];
$is_edit = false;

if (isset($_GET['id'])) {
    $is_edit = true;
    $cliente_id = $_GET['id'];
    $page_title = 'Editar Cliente';
    $api_url = API_BASE_URL . "clientes.php?id=$cliente_id";
    $response = @file_get_contents($api_url);
    if ($response) {
        $cliente = json_decode($response, true);
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
                <h1><?php echo $is_edit ? 'Editar Cliente' : 'Crear Nuevo Cliente'; ?></h1>
                <a href="clientes.php" class="btn btn-secondary">Volver a Lista</a>
            </div>

            <div class="form-container">
                <form action="cliente_handler.php" method="POST">
                    <input type="hidden" name="id" value="<?php echo htmlspecialchars($cliente['id']); ?>">

                    <div class="form-group">
                        <label for="id_tipo_documento_identidad">Tipo de Documento</label>
                        <select id="id_tipo_documento_identidad" name="id_tipo_documento_identidad" required>
                            <option value="">Seleccione...</option>
                            <?php foreach ($document_types as $type): ?>
                                <option value="<?php echo $type['id']; ?>" <?php echo ($cliente['id_tipo_documento_identidad'] == $type['id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($type['nombre']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="numero_documento">N° de Documento</label>
                        <div style="display: flex; gap: 10px;">
                            <input type="text" id="numero_documento" name="numero_documento" value="<?php echo htmlspecialchars($cliente['numero_documento']); ?>" required style="flex-grow: 1;">
                            <button type="button" class="btn" id="sunat-btn" style="flex-shrink: 0;">Sunat</button>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="nombres_apellidos">Nombres y Apellidos</label>
                        <input type="text" id="nombres_apellidos" name="nombres_apellidos" value="<?php echo htmlspecialchars($cliente['nombres_apellidos']); ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="direccion">Dirección</label>
                        <input type="text" id="direccion" name="direccion" value="<?php echo htmlspecialchars($cliente['direccion']); ?>">
                    </div>

                    <div class="form-group">
                        <label for="codigo_ubigeo">Código de Ubigeo</label>
                        <input type="text" id="codigo_ubigeo" name="codigo_ubigeo" value="<?php echo htmlspecialchars($cliente['codigo_ubigeo']); ?>">
                    </div>

                    <div class="form-group">
                        <label for="email">Email</label>
                        <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($cliente['email']); ?>">
                    </div>

                    <div class="form-group">
                        <label for="telefono">Teléfono</label>
                        <input type="text" id="telefono" name="telefono" value="<?php echo htmlspecialchars($cliente['telefono']); ?>">
                    </div>

                    <?php if ($is_edit): ?>
                    <div class="form-group">
                        <label for="estado">Estado</label>
                        <select id="estado" name="estado">
                            <option value="Activado" <?php echo ($cliente['estado'] === 'Activado') ? 'selected' : ''; ?>>Activado</option>
                            <option value="Desactivado" <?php echo ($cliente['estado'] === 'Desactivado') ? 'selected' : ''; ?>>Desactivado</option>
                        </select>
                    </div>
                    <?php endif; ?>

                    <div class="form-actions">
                        <button type="submit" class="btn"><?php echo $is_edit ? 'Actualizar Cliente' : 'Crear Cliente'; ?></button>
                    </div>
                </form>
            </div>
        </div>
    </main>
</div>

<script>
// Placeholder for SUNAT API call
document.getElementById('sunat-btn').addEventListener('click', function() {
    const docNumber = document.getElementById('numero_documento').value;
    if(docNumber) {
        alert('Consultando a SUNAT/RENIEC con el número: ' + docNumber);
        // Here you would typically make an API call to a government service
        // and populate fields like 'nombres_apellidos' and 'direccion'.
        // For this example, we'll just show an alert.
    } else {
        alert('Por favor, ingrese un número de documento.');
    }
});
</script>
