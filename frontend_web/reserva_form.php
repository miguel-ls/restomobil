<?php
session_start();

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('Location: index.php?error=Por+favor+inicie+sesión');
    exit();
}

$page_title = 'Crear Reserva';
include_once 'templates/header.php';

function getAPIdata($endpoint) {
    $api_url = "http://localhost/restaurante_system/backend/api/v1/$endpoint";
    $ch = curl_init($api_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($ch);
    curl_close($ch);
    $data = json_decode($response, true);
    return isset($data['records']) ? $data['records'] : [];
}

$mesas = getAPIdata('mesas.php');
$is_editing = false;
$reserva_data = [
    'id' => '', 'id_mesa' => '', 'nombre_cliente' => '', 'telefono_cliente' => '',
    'email_cliente' => '', 'fecha_reserva' => '', 'cantidad_personas' => '',
    'estado' => 'confirmada', 'observaciones' => ''
];

if (isset($_GET['id'])) {
    $is_editing = true;
    $page_title = 'Editar Reserva';
    $reserva_id = intval($_GET['id']);
    $api_url = "http://localhost/restaurante_system/backend/api/v1/reservas.php?id=$reserva_id";
    $response = @file_get_contents($api_url);
    if ($response) {
        $reserva_data = json_decode($response, true);
    }
}
?>

<div class="dashboard-container">
    <?php include_once 'templates/sidebar.php'; ?>

    <main class="main-content">
        <div class="container">
            <div class="page-header">
                <h1><?php echo $page_title; ?></h1>
                <a href="reservas.php" class="btn btn-secondary">Volver</a>
            </div>
            <div class="form-container">
                <form action="reserva_handler.php" method="POST">
                    <input type="hidden" name="id" value="<?php echo htmlspecialchars($reserva_data['id']); ?>">

                    <div class="form-group">
                        <label for="nombre_cliente">Nombre del Cliente</label>
                        <input type="text" id="nombre_cliente" name="nombre_cliente" value="<?php echo htmlspecialchars($reserva_data['nombre_cliente']); ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="telefono_cliente">Teléfono</label>
                        <input type="tel" id="telefono_cliente" name="telefono_cliente" value="<?php echo htmlspecialchars($reserva_data['telefono_cliente']); ?>">
                    </div>
                    <div class="form-group">
                        <label for="email_cliente">Email</label>
                        <input type="email" id="email_cliente" name="email_cliente" value="<?php echo htmlspecialchars($reserva_data['email_cliente']); ?>">
                    </div>
                    <div class="form-group">
                        <label for="fecha_reserva">Fecha y Hora</label>
                        <input type="datetime-local" id="fecha_reserva" name="fecha_reserva" value="<?php echo !empty($reserva_data['fecha_reserva']) ? date('Y-m-d\TH:i', strtotime($reserva_data['fecha_reserva'])) : ''; ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="cantidad_personas">Cantidad de Personas</label>
                        <input type="number" id="cantidad_personas" name="cantidad_personas" value="<?php echo htmlspecialchars($reserva_data['cantidad_personas']); ?>" required min="1">
                    </div>
                    <div class="form-group">
                        <label for="id_mesa">Mesa Asignada</label>
                        <select id="id_mesa" name="id_mesa" required>
                            <option value="">Seleccione una mesa</option>
                            <?php foreach ($mesas as $mesa): ?>
                                <option value="<?php echo $mesa['id']; ?>" <?php if ($mesa['id'] == $reserva_data['id_mesa']) echo 'selected'; ?>>
                                    <?php echo htmlspecialchars($mesa['numero_mesa']); ?> (Cap: <?php echo $mesa['capacidad']; ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <?php if ($is_editing): ?>
                    <div class="form-group">
                        <label for="estado">Estado</label>
                        <select id="estado" name="estado" required>
                            <option value="confirmada" <?php if ('confirmada' == $reserva_data['estado']) echo 'selected'; ?>>Confirmada</option>
                            <option value="completada" <?php if ('completada' == $reserva_data['estado']) echo 'selected'; ?>>Completada</option>
                            <option value="cancelada" <?php if ('cancelada' == $reserva_data['estado']) echo 'selected'; ?>>Cancelada</option>
                        </select>
                    </div>
                    <?php endif; ?>
                    <div class="form-group">
                        <label for="observaciones">Observaciones</label>
                        <textarea id="observaciones" name="observaciones" rows="3"><?php echo htmlspecialchars($reserva_data['observaciones']); ?></textarea>
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="btn">Guardar Reserva</button>
                    </div>
                </form>
            </div>
        </div>
    </main>
</div>

<?php
include_once 'templates/footer.php';
?>
