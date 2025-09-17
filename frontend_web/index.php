<?php
$page_title = 'Inicio de Sesión';
include_once 'templates/header.php';
?>

<div class="login-container">
    <div class="login-form">
        <h2>Acceso al Sistema</h2>
        <?php
        if (isset($_GET['error'])) {
            echo '<p class="error-message">' . htmlspecialchars(urldecode($_GET['error'])) . '</p>';
        }
        ?>
        <form action="login_handler.php" method="POST">
            <div class="form-group">
                <label for="username">Nombre de Usuario</label>
                <input type="text" id="username" name="username" required>
            </div>
            <div class="form-group">
                <label for="password">Contraseña</label>
                <input type="password" id="password" name="password" required>
            </div>
            <button type="submit" class="btn">Ingresar</button>
        </form>
    </div>
</div>

<?php
include_once 'templates/footer.php';
?>
