<?php
session_start();

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('Location: index.php?error=Por+favor+inicie+sesión');
    exit();
}

$page_title = 'Gestión de Mesas';
include_once 'templates/header.php';

function getTables() {
    $api_url = 'http://localhost/restaurante_system/backend/api/v1/mesas.php';
    $response = @file_get_contents($api_url);
    if ($response) {
        return json_decode($response, true);
    }
    return ['records' => []];
}

$tables_data = getTables();
?>

<div class="dashboard-container">
    <?php include_once 'templates/sidebar.php'; ?>

    <main class="main-content">
        <div class="container">
            <div class="page-header">
                <h1>Gestión de Mesas del Restaurante</h1>
                <a href="mesa_form.php" class="btn">Crear Mesa Nueva</a>
            </div>

            <?php
            if (isset($_GET['success'])) {
                echo '<p class="success-message">' . htmlspecialchars($_GET['success']) . '</p>';
            }
            if (isset($_GET['error'])) {
                echo '<p class="error-message">' . htmlspecialchars($_GET['error']) . '</p>';
            }
            ?>

            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Número de Mesa</th>
                            <th>Capacidad</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (isset($tables_data['records']) && !empty($tables_data['records'])): ?>
                            <?php foreach ($tables_data['records'] as $table): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($table['id']); ?></td>
                                    <td><?php echo htmlspecialchars($table['numero_mesa']); ?></td>
                                    <td><?php echo htmlspecialchars($table['capacidad']); ?> personas</td>
                                    <td>
                                        <span class="status status-<?php echo htmlspecialchars($table['estado']); ?>">
                                            <?php echo htmlspecialchars(ucfirst($table['estado'])); ?>
                                        </span>
                                    </td>
                                    <td class="actions-cell">
                                        <a href="mesa_form.php?id=<?php echo $table['id']; ?>" class="btn-edit">Editar</a>
                                        <a href="mesa_delete_handler.php?id=<?php echo $table['id']; ?>" class="btn-delete" onclick="return confirm('¿Estás seguro de que quieres eliminar esta mesa? Esta acción no se puede deshacer.');">Eliminar</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5">No se encontraron mesas.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>
</div>

<style>
.status {
    padding: 4px 8px;
    border-radius: 12px;
    font-size: 12px;
    font-weight: bold;
    color: #fff;
    text-transform: capitalize;
}
.status-disponible { background-color: #28a745; } /* Verde */
.status-ocupada { background-color: #dc3545; } /* Rojo */
.status-reservada { background-color: #ffc107; color: #000; } /* Amarillo */
.status-mantenimiento { background-color: #6c757d; } /* Gris */
</style>

<?php
include_once 'templates/footer.php';
?>
