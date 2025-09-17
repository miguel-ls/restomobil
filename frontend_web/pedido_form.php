<?php
session_start();

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('Location: index.php?error=Por+favor+inicie+sesión');
    exit();
}

$page_title = 'Crear Nuevo Pedido';
include_once 'templates/header.php';
include_once __DIR__ . '/../backend/config/app_config.php';

// --- Funciones para obtener datos de la API ---
function getAPIdata($endpoint) {
    $api_url = "http://localhost/restaurante_system/backend/api/v1/$endpoint";
    $ch = curl_init($api_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($ch);
    curl_close($ch);
    $data = json_decode($response, true);
    return isset($data['records']) ? $data['records'] : $data; // Devolver data si no hay 'records'
}

// Solo obtener mesas disponibles para pedidos nuevos. Para editar, se necesitan todas.
$mesas_endpoint = isset($_GET['id']) ? 'mesas.php' : 'mesas.php?status=available';
$mesas = getAPIdata($mesas_endpoint);
$mozos = getAPIdata('usuarios.php?rol=Mozo');
$productos = getAPIdata('productos.php');

$is_editing = false;
$order_data = null;

if (isset($_GET['id'])) {
    $is_editing = true;
    $order_id = intval($_GET['id']);
    $page_title = "Editar Pedido #$order_id";
    $order_data = getAPIdata("pedidos.php?id=$order_id");
}
?>

<div class="dashboard-container">
    <?php include_once 'templates/sidebar.php'; ?>

    <main class="main-content">
        <div class="container">
            <div class="page-header">
                <h1><?php echo $page_title; ?></h1>
            </div>

            <div id="order-form-container" class="order-grid">
                <!-- Columna de la izquierda: Productos disponibles -->
                <div class="product-list-container">
                    <h3>Productos Disponibles</h3>
                    <input type="text" id="product-search" placeholder="Buscar producto...">
                    <div id="product-list">
                        <?php if(!empty($productos)): ?>
                            <?php foreach ($productos as $producto): ?>
                                <div class="product-item" data-id="<?php echo $producto['id']; ?>" data-nombre="<?php echo htmlspecialchars($producto['nombre']); ?>" data-precio="<?php echo $producto['precio']; ?>">
                                    <h4><?php echo htmlspecialchars($producto['nombre']); ?></h4>
                                    <p><?php echo CURRENCY_SYMBOL; ?><?php echo htmlspecialchars(number_format($producto['precio'], 2)); ?></p>
                                    <p><small>Categoría: <?php echo htmlspecialchars($producto['categoria_nombre']); ?></small></p>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Columna de la derecha: Detalles del pedido -->
                <div class="order-details">
                    <h3>Detalles del Pedido</h3>
                    <form id="order-form">
                        <div class="form-group">
                            <label for="id_mesa">Mesa</label>
                            <select id="id_mesa" name="id_mesa" required>
                                <option value="">Seleccione una mesa</option>
                                <?php foreach ($mesas as $mesa): ?>
                                    <option value="<?php echo $mesa['id']; ?>" <?php if($is_editing && $order_data['id_mesa'] == $mesa['id']) echo 'selected'; ?>>
                                        <?php echo htmlspecialchars($mesa['numero_mesa']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="id_usuario_mozo">Mozo</label>
                            <select id="id_usuario_mozo" name="id_usuario_mozo" required>
                                <option value="">Seleccione un mozo</option>
                                <?php foreach ($mozos as $mozo): ?>
                                    <option value="<?php echo $mozo['id']; ?>" <?php if($is_editing && $order_data['id_usuario_mozo'] == $mozo['id']) echo 'selected'; ?>>
                                        <?php echo htmlspecialchars($mozo['nombre_completo']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <?php if ($is_editing): ?>
                        <div class="form-group">
                            <label for="estado">Estado del Pedido</label>
                            <select id="estado" name="estado" required>
                                <?php
                                $estados = ['recibido', 'en_preparacion', 'listo_para_servir', 'servido', 'pagado', 'cancelado'];
                                foreach ($estados as $estado) {
                                    $selected = ($order_data['estado'] == $estado) ? 'selected' : '';
                                    echo "<option value=\"$estado\" $selected>" . ucfirst(str_replace('_', ' ', $estado)) . "</option>";
                                }
                                ?>
                            </select>
                        </div>
                        <?php endif; ?>

                        <div class="table-container">
                            <table id="order-items-table">
                                <thead>
                                    <tr>
                                        <th>Producto</th>
                                        <th>Cantidad</th>
                                        <th>Precio</th>
                                        <th>Subtotal</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                                <tfoot>
                                    <tr>
                                        <td colspan="3" style="text-align: right;"><strong>Total:</strong></td>
                                        <td id="order-total" style="font-weight: bold;">$0.00</td>
                                        <td></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>

                        <div class="form-actions" style="margin-top: 20px;">
                            <button type="submit" class="btn"><?php echo $is_editing ? 'Actualizar Pedido' : 'Crear Pedido'; ?></button>
                            <a href="pedidos.php" class="btn btn-secondary">Cancelar</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </main>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // --- Variables y referencias del DOM ---
    const productList = document.getElementById('product-list');
    const orderItemsTableBody = document.querySelector('#order-items-table tbody');
    const orderTotalElement = document.getElementById('order-total');
    const orderForm = document.getElementById('order-form');
    const productSearch = document.getElementById('product-search');

    const currencySymbol = '<?php echo CURRENCY_SYMBOL; ?>';
    const isEditing = <?php echo json_encode($is_editing); ?>;
    const orderId = <?php echo json_encode($is_editing ? $order_id : null); ?>;
    let initialOrderData = <?php echo json_encode($is_editing ? $order_data : null); ?>;

    let currentOrder = {};

    // --- Lógica de Inicialización ---
    if (isEditing && initialOrderData && initialOrderData.items) {
        initialOrderData.items.forEach(item => {
            currentOrder[item.id_producto] = {
                id: item.id_producto,
                nombre: item.nombre_producto,
                precio: parseFloat(item.precio_unitario),
                cantidad: parseInt(item.cantidad, 10)
            };
        });
        renderOrderTable();
    }

    // --- Lógica de Eventos ---

    orderForm.addEventListener('submit', function(e) {
        e.preventDefault();
        const id_mesa = document.getElementById('id_mesa').value;
        const id_usuario_mozo = document.getElementById('id_usuario_mozo').value;
        const items = Object.values(currentOrder).map(item => ({ id: item.id, cantidad: item.cantidad }));

        if (!id_mesa || !id_usuario_mozo || items.length === 0) {
            alert('Por favor, complete todos los campos y añada al menos un producto.');
            return;
        }

        const orderData = { id_mesa, id_usuario_mozo, items };
        if (isEditing) {
            orderData.estado = document.getElementById('estado').value;
        }
        const method = isEditing ? 'PUT' : 'POST';
        const url = isEditing
            ? `http://localhost/restaurante_system/backend/api/v1/pedidos.php?id=${orderId}`
            : 'http://localhost/restaurante_system/backend/api/v1/pedidos.php';

        fetch(url, {
            method: method,
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(orderData)
        })
        .then(response => response.json())
        .then(data => {
            if (data.message) {
                const newId = isEditing ? orderId : data.id;
                window.location.href = `pedidos.php?success=Pedido+${newId}+${isEditing ? 'actualizado' : 'creado'}+exitosamente.`;
            } else {
                alert('Error: ' + (data.message || 'Error desconocido.'));
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Ocurrió un error de red.');
        });
    });

    // Re-pegar las funciones que no cambiaron para que el script esté completo
    productList.addEventListener('click', function(e) {
        const productItem = e.target.closest('.product-item');
        if (!productItem) return;
        const productId = productItem.dataset.id;
        if (currentOrder[productId]) {
            currentOrder[productId].cantidad++;
        } else {
            currentOrder[productId] = { id: productId, nombre: productItem.dataset.nombre, precio: parseFloat(productItem.dataset.precio), cantidad: 1 };
        }
        renderOrderTable();
    });
    orderItemsTableBody.addEventListener('click', function(e) {
        if (e.target.classList.contains('delete-item')) {
            delete currentOrder[e.target.dataset.id];
            renderOrderTable();
        }
    });
    orderItemsTableBody.addEventListener('input', function(e) {
        if (e.target.classList.contains('item-quantity')) {
            const newQuantity = parseInt(e.target.value, 10);
            if (newQuantity > 0) {
                currentOrder[e.target.dataset.id].cantidad = newQuantity;
            } else {
                delete currentOrder[e.target.dataset.id];
            }
            renderOrderTable();
        }
    });
    function renderOrderTable() {
        orderItemsTableBody.innerHTML = '';
        let total = 0;
        for (const productId in currentOrder) {
            const item = currentOrder[productId];
            const subtotal = item.precio * item.cantidad;
            total += subtotal;
            const row = document.createElement('tr');
            row.innerHTML = `
                <td data-label="Producto">${item.nombre}</td>
                <td data-label="Cantidad"><input type="number" class="item-quantity" value="${item.cantidad}" min="1" data-id="${item.id}"></td>
                <td data-label="Precio">${currencySymbol}${item.precio.toFixed(2)}</td>
                <td data-label="Subtotal">${currencySymbol}${subtotal.toFixed(2)}</td>
                <td data-label="Acción"><button type="button" class="btn-delete delete-item" data-id="${item.id}">X</button></td>
            `;
            orderItemsTableBody.appendChild(row);
        }
        orderTotalElement.textContent = `${currencySymbol}${total.toFixed(2)}`;
    }
    productSearch.addEventListener('keyup', function() {
        const searchTerm = productSearch.value.toLowerCase();
        document.querySelectorAll('#product-list .product-item').forEach(item => {
            item.style.display = item.dataset.nombre.toLowerCase().includes(searchTerm) ? 'block' : 'none';
        });
    });
});
</script>

<?php
include_once 'templates/footer.php';
?>
