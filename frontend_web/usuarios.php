<?php
session_start();

// Proteger la página y asegurar que solo el administrador pueda acceder
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true || $_SESSION['user_rol'] !== 'Administrador') {
    header('Location: dashboard.php?error=Acceso+no+autorizado');
    exit();
}

$page_title = 'Gestión de Usuarios';
include_once 'templates/header.php';

function getUsers() {
    $api_url = 'http://localhost/restaurante_system/backend/api/v1/usuarios.php';
    $response = file_get_contents($api_url);
    if ($response) {
        return json_decode($response, true);
    }
    return ['records' => []];
}

$users_data = getUsers();
?>

<div class="dashboard-container">
    <?php include_once 'templates/sidebar.php'; ?>

    <main class="main-content">
        <div class="container">
            <div class="page-header">
                <h1>Gestión de Usuarios del Sistema</h1>
                <a href="usuario_form.php" class="btn">Crear Usuario Nuevo</a>
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
                            <th>Username</th>
                            <th>Nombre Completo</th>
                            <th>Email</th>
                            <th>Rol</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (isset($users_data['records']) && !empty($users_data['records'])): ?>
                            <?php foreach ($users_data['records'] as $user): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($user['id']); ?></td>
                                    <td><?php echo htmlspecialchars($user['username']); ?></td>
                                    <td><?php echo htmlspecialchars($user['nombre_completo']); ?></td>
                                    <td><?php echo htmlspecialchars($user['email']); ?></td>
                                    <td><?php echo htmlspecialchars($user['nombre_rol']); ?></td>
                                    <td>
                                        <span class="status <?php echo $user['activo'] ? 'status-active' : 'status-inactive'; ?>">
                                            <?php echo $user['activo'] ? 'Activo' : 'Inactivo'; ?>
                                        </span>
                                    </td>
                                    <td class="actions-cell">
                                        <a href="usuario_form.php?id=<?php echo $user['id']; ?>" class="btn-edit">Editar</a>
                                        <!-- El botón de eliminar solo se muestra si el usuario no es el admin principal (ID 1) -->
                                        <?php if ($user['id'] != 1): ?>
                                            <a href="usuario_delete_handler.php?id=<?php echo $user['id']; ?>" class="btn-delete" onclick="return confirm('¿Estás seguro de que quieres desactivar a este usuario?');">Desactivar</a>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7">No se encontraron usuarios.</td>
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
}
.status-active {
    background-color: #28a745; /* Verde */
}
.status-inactive {
    background-color: #6c757d; /* Gris */
}
</style>

<?php
include_once 'templates/footer.php';
?>
