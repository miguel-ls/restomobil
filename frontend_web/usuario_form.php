<?php
session_start();
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true || $_SESSION['user_rol'] !== 'Administrador') {
    header('Location: dashboard.php?error=Acceso+no+autorizado');
    exit();
}

include_once 'templates/header.php';

function getAPIdata($endpoint) {
    $api_url = "http://localhost/restaurante_system/backend/api/v1/$endpoint";
    $response = @file_get_contents($api_url);
    $data = json_decode($response, true);
    return isset($data['records']) ? $data['records'] : $data;
}

$is_editing = false;
$user_data = ['id' => '', 'username' => '', 'nombre_completo' => '', 'email' => '', 'id_rol' => '', 'activo' => 1];
$page_title = 'Crear Usuario';

$roles = getAPIdata('usuarios.php?resource=roles');

if (isset($_GET['id'])) {
    $is_editing = true;
    $user_id = intval($_GET['id']);
    $page_title = 'Editar Usuario';
    $user_data = getAPIdata("usuarios.php?id=$user_id");
}
?>

<div class="dashboard-container">
    <?php include_once 'templates/sidebar.php'; ?>
    <main class="main-content">
        <div class="container">
            <div class="page-header">
                <h1><?php echo $page_title; ?></h1>
                <a href="usuarios.php" class="btn btn-secondary">Volver</a>
            </div>
            <div class="form-container">
                <form action="usuario_handler.php" method="POST">
                    <input type="hidden" name="id" value="<?php echo htmlspecialchars($user_data['id']); ?>">
                    <div class="form-group">
                        <label for="username">Username</label>
                        <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($user_data['username']); ?>" <?php if ($is_editing) echo 'readonly'; ?> required>
                    </div>
                    <div class="form-group">
                        <label for="nombre_completo">Nombre Completo</label>
                        <input type="text" id="nombre_completo" name="nombre_completo" value="<?php echo htmlspecialchars($user_data['nombre_completo']); ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="email">Email</label>
                        <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user_data['email']); ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="password">Contraseña</label>
                        <input type="password" id="password" name="password" <?php if (!$is_editing) echo 'required'; ?>>
                        <small><?php if ($is_editing) echo 'Dejar en blanco para no cambiar.'; ?></small>
                    </div>
                    <div class="form-group">
                        <label for="id_rol">Rol</label>
                        <select id="id_rol" name="id_rol" required>
                            <?php foreach ($roles as $rol): ?>
                                <option value="<?php echo $rol['id']; ?>" <?php if ($rol['id'] == $user_data['id_rol']) echo 'selected'; ?>>
                                    <?php echo htmlspecialchars($rol['nombre_rol']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <?php if ($is_editing): ?>
                    <div class="form-group">
                        <label for="activo">Estado</label>
                        <select id="activo" name="activo" required>
                            <option value="1" <?php if ($user_data['activo'] == 1) echo 'selected'; ?>>Activo</option>
                            <option value="0" <?php if ($user_data['activo'] == 0) echo 'selected'; ?>>Inactivo</option>
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

