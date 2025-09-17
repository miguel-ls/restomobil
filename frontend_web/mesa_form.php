<?php
session_start();

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('Location: index.php?error=Por+favor+inicie+sesión');
    exit();
}

include_once 'templates/header.php';

$table_data = [
    'id' => '',
    'numero_mesa' => '',
    'capacidad' => '',
    'estado' => 'disponible' // Valor por defecto
];
$page_title = 'Crear Mesa';
$is_editing = false;

if (isset($_GET['id'])) {
    $is_editing = true;
    $table_id = $_GET['id'];
    $api_url = "http://localhost/restaurante_system/backend/api/v1/mesas.php?id=$table_id";
    $response = @file_get_contents($api_url);
    if ($response) {
        $table_data = json_decode($response, true);
    }
    $page_title = 'Editar Mesa';
}

$estados = ['disponible', 'ocupada', 'reservada', 'mantenimiento'];
?>

<div class="dashboard-container">
    <?php include_once 'templates/sidebar.php'; ?>

    <main class="main-content">
        <div class="container">
            <div class="page-header">
                <h1><?php echo $page_title; ?></h1>
            </div>

            <div class="form-container">
                <form action="mesa_handler.php" method="POST">
                    <input type="hidden" name="id" value="<?php echo htmlspecialchars($table_data['id']); ?>">

                    <div class="form-group">
                        <label for="numero_mesa">Número de Mesa</label>
                        <input type="text" id="numero_mesa" name="numero_mesa" value="<?php echo htmlspecialchars($table_data['numero_mesa']); ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="capacidad">Capacidad</label>
                        <input type="number" id="capacidad" name="capacidad" value="<?php echo htmlspecialchars($table_data['capacidad']); ?>" required min="1">
                    </div>

                    <div class="form-group">
                        <label for="estado">Estado</label>
                        <select id="estado" name="estado" required>
                            <?php foreach ($estados as $estado): ?>
                                <option value="<?php echo $estado; ?>" <?php if ($estado == $table_data['estado']) echo 'selected'; ?>>
                                    <?php echo ucfirst($estado); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="btn">Guardar Cambios</button>
                        <a href="mesas.php" class="btn btn-secondary">Cancelar</a>
                    </div>
                </form>
            </div>
        </div>
    </main>
</div>

<?php
include_once 'templates/footer.php';
?>
