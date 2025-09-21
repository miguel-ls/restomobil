<?php
session_start();
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('Location: index.php');
    exit();
}

$page_title = 'Gestión de Empresas';
include_once 'templates/header.php';

function getEmpresas() {
    require_once 'config.php';
    $api_url = API_BASE_URL . 'empresas.php';
    $ch = curl_init($api_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    $response = curl_exec($ch);
    curl_close($ch);
    return json_decode($response, true);
}

$empresas_data = getEmpresas();
?>

<div class="dashboard-container">
    <?php include_once 'templates/sidebar.php'; ?>
    <main class="main-content">
        <div class="container">
            <div class="page-header">
                <h1>Catálogo de Empresas</h1>
                <a href="empresa_form.php" class="btn">Crear Empresa</a>
            </div>
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>Nombre Corto</th>
                            <th>RUC</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (isset($empresas_data['records']) && !empty($empresas_data['records'])): ?>
                            <?php foreach ($empresas_data['records'] as $empresa): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($empresa['nombre_corto']); ?></td>
                                    <td><?php echo htmlspecialchars($empresa['ruc']); ?></td>
                                    <td>
                                        <span class="status <?php echo $empresa['estado'] ? 'status-active' : 'status-inactive'; ?>">
                                            <?php echo $empresa['estado'] ? 'Activo' : 'Inactivo'; ?>
                                        </span>
                                    </td>
                                    <td class="actions-cell">
                                        <a href="empresa_form.php?id=<?php echo $empresa['id']; ?>" class="btn btn-edit">Editar</a>
                                        <a href="empresa_delete_handler.php?id=<?php echo $empresa['id']; ?>" class="btn btn-delete" onclick="return confirm('¿Estás seguro de eliminar esta empresa?');">Eliminar</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="4">No se encontraron empresas.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>
</div>
