<?php
session_start();
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('Location: index.php');
    exit();
}

$page_title = 'Gestión de Punto de Venta';
include_once 'templates/header.php';

function getTables() {
    // Incluir configuración de la API
    require_once 'config.php';
    $api_url = API_BASE_URL . 'mesas.php';
    $ch = curl_init($api_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    $response = curl_exec($ch);
    curl_close($ch);
    return json_decode($response, true);
}

$tables_data = getTables();
?>

<div class="dashboard-container">
    <?php include_once 'templates/sidebar.php'; ?>
    <main class="main-content">
        <div class="container">
            <div class="page-header">
                <h1>Gestión de Mesas</h1>
                <a href="mesa_form.php" class="btn">Crear Mesa</a>
            </div>
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Punto de Venta</th>
                            <th>Capacidad</th>
                            <th>Estado</th>
                            <th>Tipo</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (isset($tables_data['records']) && !empty($tables_data['records'])): ?>
                            <?php foreach ($tables_data['records'] as $table): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($table['id']); ?></td>
                                    <td><?php echo htmlspecialchars($table['numero_mesa']); ?></td>
                                    <td><?php echo htmlspecialchars($table['capacidad']); ?></td>
                                    <td>
                                        <span class="status status-<?php echo htmlspecialchars($table['estado']); ?>">
                                            <?php echo htmlspecialchars(ucfirst($table['estado'])); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <input type="checkbox" <?php echo ($table['es_libre'] ?? false) ? 'checked' : ''; ?> disabled>
                                    </td>
                                    <td class="actions-cell">
                                        <a href="mesa_form.php?id=<?php echo $table['id']; ?>" class="btn btn-edit">Editar</a>
                                        <a href="mesa_delete_handler.php?id=<?php echo $table['id']; ?>" class="btn btn-delete" onclick="return confirm('¿Estás seguro?');">Eliminar</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="6">No se encontraron mesas.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>
</div>

