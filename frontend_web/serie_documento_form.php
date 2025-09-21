<?php
session_start();
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('Location: index.php');
    exit();
}

require_once 'config.php';
$page_title = 'Crear Serie de Documento';
$serie_data = ['id' => '', 'id_tipo_documento' => '', 'serie' => '', 'estado' => 1];
$is_editing = false;

// Fetch document types for the dropdown
function getDocumentTypes() {
    $api_url = API_BASE_URL . 'tipos_documentos.php';
    $response = @file_get_contents($api_url);
    if ($response) {
        $data = json_decode($response, true);
        return $data['records'] ?? [];
    }
    return [];
}
$document_types = getDocumentTypes();


if (isset($_GET['id'])) {
    $is_editing = true;
    $serie_id = intval($_GET['id']);
    $page_title = 'Editar Serie de Documento';

    $api_url = API_BASE_URL . "series_documentos.php?id=$serie_id";
    $response = @file_get_contents($api_url);
    if ($response) {
        $serie_data = json_decode($response, true);
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
                <a href="series_documentos.php" class="btn btn-secondary">Volver a la Lista</a>
            </div>
            <div class="form-container">
                <form action="serie_documento_handler.php" method="POST">
                    <input type="hidden" name="id" value="<?php echo htmlspecialchars($serie_data['id']); ?>">

                    <div class="form-group">
                        <label for="id_tipo_documento">Tipo de Documento</label>
                        <select id="id_tipo_documento" name="id_tipo_documento" required>
                            <option value="">Seleccione un tipo</option>
                            <?php foreach ($document_types as $type): ?>
                                <option value="<?php echo $type['id']; ?>" <?php echo ($type['id'] == $serie_data['id_tipo_documento']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($type['nombre']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="serie">Serie</label>
                        <input type="text" id="serie" name="serie" value="<?php echo htmlspecialchars($serie_data['serie']); ?>" required maxlength="10">
                    </div>

                    <?php if ($is_editing): ?>
                    <div class="form-group">
                        <label for="estado">Estado</label>
                        <select id="estado" name="estado">
                            <option value="1" <?php echo ($serie_data['estado'] == 1) ? 'selected' : ''; ?>>Activo</option>
                            <option value="0" <?php echo ($serie_data['estado'] == 0) ? 'selected' : ''; ?>>Inactivo</option>
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
