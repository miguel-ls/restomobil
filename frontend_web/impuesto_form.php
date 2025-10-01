<?php
session_start();
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('Location: index.php');
    exit();
}

require_once 'config.php';

$page_title = 'Crear Impuesto';
$impuesto_data = [
    'id' => '',
    'codigo' => '',
    'fecha_inicial' => '',
    'fecha_final' => '',
    'valor' => '',
    'estado' => 1 // Por defecto, Activo
];
$is_editing = false;

if (isset($_GET['id'])) {
    $is_editing = true;
    $impuesto_id = intval($_GET['id']);
    $page_title = 'Editar Impuesto';

    $api_url = API_BASE_URL . "impuestos.php?id=$impuesto_id";
    $response = @file_get_contents($api_url);
    if ($response) {
        $data = json_decode($response, true);
        if ($data) {
            $impuesto_data = $data;
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
                <a href="impuestos.php" class="btn btn-secondary">Volver a la lista</a>
            </div>
            <div class="form-container">
                <form id="impuesto-form" action="impuesto_handler.php" method="POST">
                    <input type="hidden" name="id" value="<?php echo htmlspecialchars($impuesto_data['id']); ?>">

                    <div class="form-group">
                        <label for="codigo">Código</label>
                        <input type="text" id="codigo" name="codigo" value="<?php echo htmlspecialchars($impuesto_data['codigo']); ?>" required maxlength="3">
                    </div>

                    <div class="form-group">
                        <label for="fecha_inicial">Fecha Inicial</label>
                        <input type="date" id="fecha_inicial" name="fecha_inicial" value="<?php echo htmlspecialchars($impuesto_data['fecha_inicial']); ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="fecha_final">Fecha Final (Opcional)</label>
                        <input type="date" id="fecha_final" name="fecha_final" value="<?php echo htmlspecialchars($impuesto_data['fecha_final']); ?>">
                    </div>

                    <div class="form-group">
                        <label for="valor">Valor</label>
                        <input type="number" id="valor" name="valor" value="<?php echo htmlspecialchars($impuesto_data['valor']); ?>" required step="0.01">
                    </div>

                    <div class="form-group">
                        <label for="estado">Estado</label>
                        <select id="estado" name="estado">
                            <option value="1" <?php echo ($impuesto_data['estado'] == 1) ? 'selected' : ''; ?>>Activo</option>
                            <option value="0" <?php echo ($impuesto_data['estado'] == 0) ? 'selected' : ''; ?>>Inactivo</option>
                        </select>
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="btn"><?php echo $is_editing ? 'Actualizar Impuesto' : 'Grabar Impuesto'; ?></button>
                    </div>
                </form>
            </div>
        </div>
    </main>
</div>

<script>
// No se necesita JS específico para este formulario,
// la validación se maneja con atributos HTML5 y en el backend.
</script>