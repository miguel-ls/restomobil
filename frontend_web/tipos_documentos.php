<?php
session_start();
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('Location: index.php');
    exit();
}

$page_title = 'Tipos de Documentos de Venta';
include_once 'templates/header.php';

function getSaleDocumentTypes() {
    require_once 'config.php';
    $api_url = API_BASE_URL . 'tipos_documentos.php';
    $ch = curl_init($api_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    $response = curl_exec($ch);
    curl_close($ch);
    return json_decode($response, true);
}

$types_data = getSaleDocumentTypes();
?>

<div class="dashboard-container">
    <?php include_once 'templates/sidebar.php'; ?>
    <main class="main-content">
        <div class="container">
            <div class="page-header">
                <h1>Catálogo de Tipos de Documentos de Venta</h1>
                <a href="tipos_documentos_form.php" class="btn">Crear Tipo de Documento</a>
            </div>
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>Código</th>
                            <th>Nombre</th>
                            <th>Descripción</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (isset($types_data['records']) && !empty($types_data['records'])): ?>
                            <?php foreach ($types_data['records'] as $type): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($type['codigo']); ?></td>
                                    <td><?php echo htmlspecialchars($type['nombre']); ?></td>
                                    <td><?php echo htmlspecialchars($type['descripcion'] ?? ''); ?></td>
                                    <td>
                                        <span class="status <?php echo $type['estado'] ? 'status-active' : 'status-inactive'; ?>">
                                            <?php echo $type['estado'] ? 'Activo' : 'Inactivo'; ?>
                                        </span>
                                    </td>
                                    <td class="actions-cell">
                                        <a href="tipos_documentos_form.php?id=<?php echo $type['id']; ?>" class="btn btn-edit">Editar</a>
                                        <a href="tipos_documentos_delete_handler.php?id=<?php echo $type['id']; ?>" class="btn btn-delete" onclick="return confirm('¿Estás seguro?');">Eliminar</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="5">No se encontraron tipos de documento.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>
</div>
