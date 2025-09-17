<?php
session_start();

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('Location: index.php?error=Por+favor+inicie+sesión');
    exit();
}

$page_title = 'Crear Nuevo Pedido';
include_once 'templates/header.php';
include_once __DIR__ . '/../backend/config/app_config.php';

function getAPIdata($endpoint) {
    $api_url = "http://localhost/restaurante_system/backend/api/v1/$endpoint";
    $ch = curl_init($api_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($ch);
    curl_close($ch);
    $data = json_decode($response, true);
    return isset($data['records']) ? $data['records'] : $data;
}

$is_editing = false;
$order_data = null;

if (isset($_GET['id'])) {
    $is_editing = true;
    $order_id = intval($_GET['id']);
    $page_title = "Editar Pedido #$order_id";
    $order_data = getAPIdata("pedidos.php?id=$order_id");
}

$mesas_endpoint = $is_editing ? 'mesas.php' : 'mesas.php?status=available';
$mesas = getAPIdata($mesas_endpoint);
$mozos = getAPIdata('usuarios.php?rol=Mozo');
$productos = getAPIdata('productos.php');
?>

<div class="dashboard-container">
    <?php include_once 'templates/sidebar.php'; ?>

    <main class="main-content">
        <div class="container">
            <div class="page-header">
                <h1><?php echo $page_title; ?></h1>
            </div>

            <div id="order-form-container" class="order-grid">
                <div class="product-list-container">
                    <h3>Productos Disponibles</h3>
                    <input type="text" id="product-search" placeholder="Buscar producto...">
                    <div id="product-list">
                        <?php if(!empty($productos)): ?>
                            <?php foreach ($productos as $producto): ?>
                                <div class="product-item" data-id="<?php echo $producto['id']; ?>" data-nombre="<?php echo htmlspecialchars($producto['nombre']); ?>" data-precio="<?php echo $producto['precio']; ?>">
                                    <h4><?php echo htmlspecialchars($producto['nombre']); ?></h4>
                                    <p><?php echo CURRENCY_SYMBOL; ?><?php echo htmlspecialchars(number_format($producto['precio'], 2)); ?></p>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="order-details" id="order-details">
                    <h3>Detalles del Pedido</h3>
                    <form id="order-form">
                        <div class="form-group">
                            <label for="id_mesa">Mesa</label>
                            <select id="id_mesa" name="id_mesa" required>
                                <option value="">Seleccione una mesa</option>
                                <?php if ($is_editing && $order_data) {
                                    $mesa_en_lista = false;
                                    foreach ($mesas as $mesa) { if ($mesa['id'] == $order_data['id_mesa']) $mesa_en_lista = true; }
                                    if (!$mesa_en_lista) {
                                        echo "<option value=\"{$order_data['id_mesa']}\" selected>{$order_data['numero_mesa']} (Ocupada)</option>";
                                    }
                                }?>
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

                        <div class="table-container desktop-only">
                            <table id="order-items-table">
                                <thead><tr><th>Producto</th><th>Cantidad</th><th>Precio</th><th>Subtotal</th><th></th></tr></thead>
                                <tbody></tbody>
                            </table>
                        </div>
                        <div id="order-items-cards" class="mobile-only"></div>

                        <div class="order-total-container">
                            <strong>Total:</strong>
                            <span id="order-total" style="font-weight: bold;"><?php echo CURRENCY_SYMBOL; ?>0.00</span>
                        </div>

                        <div class="form-actions">
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
    const productList = document.getElementById('product-list');
    const orderDetailsContainer = document.getElementById('order-details');
    const orderTotalElement = document.getElementById('order-total');
    const orderForm = document.getElementById('order-form');
    const productSearch = document.getElementById('product-search');

    const currencySymbol = '<?php echo CURRENCY_SYMBOL; ?>';
    const isEditing = <?php echo json_encode($is_editing); ?>;
    const orderId = <?php echo json_encode($is_editing ? $order_id : null); ?>;
    let initialOrderData = <?php echo json_encode($is_editing ? $order_data : null); ?>;
    let currentOrder = {};

    function renderOrderItems() {
        const tableBody = document.querySelector('#order-items-table tbody');
        const cardsContainer = document.getElementById('order-items-cards');
        tableBody.innerHTML = '';
        cardsContainer.innerHTML = '';
        let total = 0;
        for (const productId in currentOrder) {
            const item = currentOrder[productId];
            const subtotal = item.precio * item.cantidad;
            total += subtotal;

            tableBody.innerHTML += `<tr><td>${item.nombre}</td><td><input type="number" class="item-quantity" value="${item.cantidad}" min="1" data-id="${item.id}"></td><td>${currencySymbol}${item.precio.toFixed(2)}</td><td>${currencySymbol}${subtotal.toFixed(2)}</td><td><button type="button" class="btn-delete delete-item" data-id="${item.id}">X</button></td></tr>`;

            cardsContainer.innerHTML += `<div class="order-item-card"><div class="card-item-header">${item.nombre}</div><div class="card-item-body"><div class="card-item-row"><span>Precio:</span><span>${currencySymbol}${item.precio.toFixed(2)}</span></div><div class="card-item-row"><span>Cantidad:</span><input type="number" class="item-quantity" value="${item.cantidad}" min="1" data-id="${item.id}"></div><div class="card-item-row"><span>Subtotal:</span><strong>${currencySymbol}${subtotal.toFixed(2)}</strong></div></div><div class="card-item-footer"><button type="button" class="btn-delete delete-item" data-id="${item.id}">Eliminar</button></div></div>`;
        }
        orderTotalElement.textContent = `${currencySymbol}${total.toFixed(2)}`;
    }

    if (isEditing && initialOrderData && initialOrderData.items) {
        initialOrderData.items.forEach(item => {
            currentOrder[item.id_producto] = { id: item.id_producto, nombre: item.nombre_producto, precio: parseFloat(item.precio_unitario), cantidad: parseInt(item.cantidad, 10) };
        });
        renderOrderItems();
    }

    productList.addEventListener('click', function(e) {
        const productItem = e.target.closest('.product-item');
        if (!productItem) return;
        const productId = productItem.dataset.id;
        if (currentOrder[productId]) { currentOrder[productId].cantidad++; } else { currentOrder[productId] = { id: productId, nombre: productItem.dataset.nombre, precio: parseFloat(productItem.dataset.precio), cantidad: 1 }; }
        renderOrderItems();
    });

    orderDetailsContainer.addEventListener('input', function(e) {
        if (e.target.classList.contains('item-quantity')) {
            const newQuantity = parseInt(e.target.value, 10);
            const productId = e.target.dataset.id;
            if (newQuantity > 0) { currentOrder[productId].cantidad = newQuantity; } else { delete currentOrder[productId]; }
            renderOrderItems();
        }
    });

    orderDetailsContainer.addEventListener('click', function(e) {
        if (e.target.classList.contains('delete-item')) {
            delete currentOrder[e.target.dataset.id];
            renderOrderItems();
        }
    });

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
        const url = isEditing ? `http://localhost/restaurante_system/backend/api/v1/pedidos.php?id=${orderId}` : 'http://localhost/restaurante_system/backend/api/v1/pedidos.php';
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
