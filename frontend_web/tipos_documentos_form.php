<?php
session_start();
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('Location: index.php');
    exit();
}

$page_title = 'Crear Tipo de Documento de Venta';
$type_data = ['id' => '', 'codigo' => '', 'nombre' => '', 'descripcion' => '', 'estado' => 1];
$is_editing = false;

if (isset($_GET['id'])) {
    $is_editing = true;
    $type_id = intval($_GET['id']);
    $page_title = 'Editar Tipo de Documento de Venta';

    require_once 'config.php';
    $api_url = API_BASE_URL . "tipos_documentos.php?id=$type_id";
    $response = @file_get_contents($api_url);
    if ($response) {
        $type_data = json_decode($response, true);
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
                <a href="tipos_documentos.php" class="btn btn-secondary">Volver</a>
            </div>
            <div class="form-container">
                <form id="tipo-documento-form">
                    <input type="hidden" name="id" value="<?php echo htmlspecialchars($type_data['id'] ?? ''); ?>">
                    <div class="form-group">
                        <label for="codigo">Código SUNAT</label>
                        <input type="text" id="codigo" name="codigo" value="<?php echo htmlspecialchars($type_data['codigo'] ?? ''); ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="nombre">Nombre</label>
                        <input type="text" id="nombre" name="nombre" value="<?php echo htmlspecialchars($type_data['nombre'] ?? ''); ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="descripcion">Descripción</label>
                        <textarea id="descripcion" name="descripcion" rows="4"><?php echo htmlspecialchars($type_data['descripcion'] ?? ''); ?></textarea>
                    </div>
                    <?php if ($is_editing): ?>
                    <div class="form-group">
                        <label for="estado">Estado</label>
                        <select id="estado" name="estado">
                            <option value="1" <?php echo (($type_data['estado'] ?? 1) == 1) ? 'selected' : ''; ?>>Activo</option>
                            <option value="0" <?php echo (($type_data['estado'] ?? 1) == 0) ? 'selected' : ''; ?>>Inactivo</option>
                        </select>
                    </div>
                    <?php endif; ?>
                    <div class="form-actions">
                        <button type="submit" class="btn"><?php echo $is_editing ? 'Actualizar' : 'Crear'; ?></button>
                    </div>
                </form>
            </div>
        </div>
    </main>
</div>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('tipo-documento-form');
    const isEditing = <?php echo json_encode($is_editing); ?>;
    const apiBaseUrl = '<?php echo API_BASE_URL; ?>';

    form.addEventListener('submit', async function(e) {
        e.preventDefault();

        const formData = new FormData(form);
        const data = Object.fromEntries(formData.entries());
        const typeId = data.id;

        const method = isEditing ? 'PUT' : 'POST';
        const url = isEditing ? `${apiBaseUrl}tipos_documentos.php?id=${typeId}` : `${apiBaseUrl}tipos_documentos.php`;

        if (data.estado !== undefined) {
            data.estado = data.estado == '1';
        }

        try {
            const response = await fetch(url, {
                method: method,
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(data)
            });

            const result = await response.json();

            if (!response.ok) {
                throw new Error(result.message || `Error ${method === 'PUT' ? 'actualizando' : 'creando'} el tipo de documento.`);
            }

            const successMessage = result.message || `Tipo de documento ${method === 'PUT' ? 'actualizado' : 'creado'} con éxito.`;
            window.location.href = `tipos_documentos.php?success=${encodeURIComponent(successMessage)}`;

        } catch (error) {
            // Assuming showAlert is a global function defined in another script
            if (typeof showAlert === 'function') {
                showAlert('Error', error.message);
            } else {
                alert('Error: ' + error.message);
            }
        }
    });
});
</script>
