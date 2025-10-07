<?php
session_start();
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('Location: index.php');
    exit();
}

require_once 'config.php';

$page_title = 'Crear Almacén';
$almacen_data = [
    'id' => '',
    'nombre' => '',
    'estado' => 1 // Por defecto, Activo
];
$is_editing = false;

if (isset($_GET['id'])) {
    $is_editing = true;
    $almacen_id = intval($_GET['id']);
    $page_title = 'Editar Almacén';

    // Llamada a la API para obtener los datos del almacén
    $api_url = API_BASE_URL . "almacenes.php?id=$almacen_id";
    $response = @file_get_contents($api_url);
    if ($response) {
        $data = json_decode($response, true);
        if ($data) {
            $almacen_data = $data;
        }
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
                <a href="almacenes.php" class="btn btn-secondary">Volver a la lista</a>
            </div>
            <div class="form-container">
                <form id="almacen-form" action="almacen_handler.php" method="POST">
                    <input type="hidden" name="id" value="<?php echo htmlspecialchars($almacen_data['id']); ?>">

                    <div class="form-group">
                        <label for="nombre">Nombre del Almacén</label>
                        <input type="text" id="nombre" name="nombre" value="<?php echo htmlspecialchars($almacen_data['nombre']); ?>" required maxlength="255">
                    </div>

                    <div class="form-group">
                        <label for="estado">Estado</label>
                        <select id="estado" name="estado">
                            <option value="1" <?php echo (isset($almacen_data['estado']) && $almacen_data['estado'] == 1) ? 'selected' : ''; ?>>Activo</option>
                            <option value="0" <?php echo (isset($almacen_data['estado']) && $almacen_data['estado'] == 0) ? 'selected' : ''; ?>>Inactivo</option>
                        </select>
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="btn"><?php echo $is_editing ? 'Actualizar Almacén' : 'Grabar Almacén'; ?></button>
                    </div>
                </form>
            </div>
        </div>
    </main>
</div>

<script>
// No se necesita JS específico para este formulario.
</script>