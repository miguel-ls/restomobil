<?php
session_start();

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true || $_SESSION['user_rol'] !== 'Administrador') {
    header('Location: dashboard.php?error=Acceso+no+autorizado');
    exit();
}

include_once 'templates/header.php';

// --- Estado inicial y variables ---
$user_data = [
    'id' => '',
    'username' => '',
    'nombre_completo' => '',
    'email' => '',
    'id_rol' => '',
    'activo' => 1 // Por defecto, un nuevo usuario está activo
];
$page_title = 'Crear Usuario';
$is_editing = false;

// --- Funciones de API ---
function getRoles() {
    $api_url = 'http://localhost/restaurante_system/backend/api/v1/usuarios.php?resource=roles';
    $response = file_get_contents($api_url);
    return $response ? json_decode($response, true)['records'] : [];
}

function getUserById($id) {
    $api_url = "http://localhost/restaurante_system/backend/api/v1/usuarios.php?id=$id";
    $response = file_get_contents($api_url);
    return $response ? json_decode($response, true) : null;
}

// --- Lógica de la página ---
$roles = getRoles();

if (isset($_GET['id'])) {
    $is_editing = true;
    $user_id = $_GET['id'];
    $fetched_user = getUserById($user_id);
    if ($fetched_user) {
        $user_data = $fetched_user;
    }
    $page_title = 'Editar Usuario';
}
?>

<div class="dashboard-container">
    <?php include_once 'templates/sidebar.php'; ?>

    <main class="main-content">
        <div class="container">
            <div class="page-header">
                <h1><?php echo $page_title; ?></h1>
            </div>

            <div class="form-container">
                <form action="usuario_handler.php" method="POST">
                    <input type="hidden" name="id" value="<?php echo htmlspecialchars($user_data['id']); ?>">

                    <div class="form-group">
                        <label for="username">Username</label>
                        <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($user_data['username']); ?>" <?php if ($is_editing) echo 'readonly'; ?> required>
                        <?php if ($is_editing): ?>
                            <small>El username no se puede cambiar.</small>
                        <?php endif; ?>
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
                        <?php if ($is_editing): ?>
                            <small>Dejar en blanco para no cambiar la contraseña.</small>
                        <?php endif; ?>
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
                        <button type="submit" class="btn">Guardar Cambios</button>
                        <a href="usuarios.php" class="btn btn-secondary">Cancelar</a>
                    </div>
                </form>
            </div>
        </div>
    </main>
</div>

<?php
include_once 'templates/footer.php';
?>
