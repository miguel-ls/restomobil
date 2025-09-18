<?php
session_start();
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true || $_SESSION['user_rol'] !== 'Administrador') {
    header('Location: dashboard.php?error=Acceso+no+autorizado');
    exit();
}

$page_title = 'Gestión de Usuarios';
include_once 'templates/header.php';

function getUsers() {
    // Incluir configuración de la API
    require_once 'config.php';
    $api_url = API_BASE_URL . 'usuarios.php';
    $ch = curl_init($api_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    $response = curl_exec($ch);
    curl_close($ch);
    return json_decode($response, true);
}

$users_data = getUsers();
?>

<div class="dashboard-container">
    <?php include_once 'templates/sidebar.php'; ?>
    <main class="main-content">
        <div class="container">
            <div class="page-header">
                <h1>Gestión de Usuarios</h1>
                <a href="usuario_form.php" class="btn">Crear Usuario</a>
            </div>
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
                                    <td><span class="status <?php echo $user['activo'] ? 'status-active' : 'status-inactive'; ?>"><?php echo $user['activo'] ? 'Activo' : 'Inactivo'; ?></span></td>
                                    <td class="actions-cell">
                                        <a href="usuario_form.php?id=<?php echo $user['id']; ?>" class="btn btn-edit">Editar</a>
                                        <?php if ($user['id'] != 1): ?>
                                            <a href="usuario_delete_handler.php?id=<?php echo $user['id']; ?>" class="btn btn-delete" onclick="return confirm('¿Estás seguro?');">Desactivar</a>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="7">No se encontraron usuarios.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>
</div>

