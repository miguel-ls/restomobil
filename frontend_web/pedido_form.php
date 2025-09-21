<?php
session_start();

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('Location: index.php?error=Por+favor+inicie+sesión');
    exit();
}

$view = $_GET['view'] ?? 'edit'; // 'edit', 'pago', or 'caja_create'
$is_pago_view = $view === 'pago';
$is_caja_create_view = $view === 'caja_create';
$is_paid = false;

$page_title = 'Crear Nuevo Pedido';
if ($is_caja_create_view) {
    $page_title = 'Crear Pedido de Caja';
}
include_once 'templates/header.php';
include_once __DIR__ . '/config.php';

function fetchFromAPI($endpoint) {
    $api_url = API_BASE_URL . $endpoint;
    $ch = curl_init($api_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    $response = curl_exec($ch);
    if (curl_errno($ch)) {
        error_log("cURL Error fetching $api_url: " . curl_error($ch));
        curl_close($ch);
        return ['error' => 'Error de comunicación con la API.'];
    }
    curl_close($ch);
    $data = json_decode($response, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        error_log("JSON Decode Error for $api_url. Response: $response");
        return ['error' => 'Respuesta inválida de la API.'];
    }
    return $data;
}

$is_editing = false;
$order_data = null;
if (isset($_GET['id'])) {
    $is_editing = true;
    $order_id = intval($_GET['id']);
    $page_title = $is_pago_view ? "Pagar Pedido #$order_id" : "Editar Pedido #$order_id";
    $order_data = fetchFromAPI("pedidos.php?id=$order_id");
    if (isset($order_data['error']) || !$order_data) {
        $page_title = "Error";
        $order_data = null;
    } else {
        $is_paid = $order_data['estado'] === 'pagado';
    }
}

if ($is_editing && $order_data) {
    $mesas_data = fetchFromAPI('mesas.php');
} else if ($is_caja_create_view) {
    // For new "Caja" orders, fetch tables where es_libre = 1 (Tipo de Mesa Libre Activado)
    $mesas_data = fetchFromAPI('mesas.php?es_libre=1');
}
else {
    $mesas_data = fetchFromAPI('mesas.php?status=available');
}

$rol_to_fetch = $is_caja_create_view ? 'Cajero' : 'Mozo';
$mozos_data = fetchFromAPI("usuarios.php?rol=$rol_to_fetch");
$productos_data = fetchFromAPI('productos.php?estado=activo');
$categorias_data = fetchFromAPI('categorias.php');

$mesas = isset($mesas_data['records']) ? $mesas_data['records'] : [];
$mozos = isset($mozos_data['records']) ? $mozos_data['records'] : [];
$productos = isset($productos_data['records']) ? $productos_data['records'] : [];
$categorias = isset($categorias_data['records']) ? $categorias_data['records'] : [];

$form_action = "pedido_handler.php" . ($is_editing ? "?id=$order_id" : "");
if ($is_pago_view) {
    $form_action .= ($is_editing ? "&" : "?") . "view=pago";
} else if ($is_caja_create_view) {
    $form_action .= "?view=caja_create";
}

?>

<div class="dashboard-container">
    <?php include_once 'templates/sidebar.php'; ?>
    <main class="main-content">
        <div class="container">
            <div class="page-header">
                <h1><?php echo $page_title; ?></h1>
            </div>

<div id="order-form-container" class="order-grid <?php if ($is_paid) echo 'is-paid'; ?>">
                <div class="product-list-container">
                    <h3>Productos Disponibles</h3>
                    <div id="category-filters" class="category-filters">
                        <button class="btn-category active" data-category="all">Todos</button>
                        <?php foreach ($categorias as $categoria): ?>
                            <button class="btn-category" data-category="<?php echo htmlspecialchars($categoria['nombre']); ?>">
                                <?php echo htmlspecialchars($categoria['nombre']); ?>
                            </button>
                        <?php endforeach; ?>
                    </div>
                    <input type="text" id="product-search" placeholder="Buscar producto...">
                    <div id="product-list">
                        <?php foreach ($productos as $producto): ?>
                            <div class="product-item"
                                 data-id="<?php echo $producto['id']; ?>"
                                 data-nombre="<?php echo htmlspecialchars($producto['nombre']); ?>"
                                 data-precio="<?php echo $producto['precio']; ?>"
                                 data-categoria-tipo="<?php echo htmlspecialchars($producto['categoria_tipo']); ?>"
                                 data-category="<?php echo htmlspecialchars($producto['categoria_nombre']); ?>">
                                <h4><?php echo htmlspecialchars($producto['nombre']); ?></h4>
                                <p><?php echo CURRENCY_SYMBOL; ?><?php echo htmlspecialchars(number_format($producto['precio'], 2)); ?></p>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div class="order-details" id="order-details">
                    <div class="order-details-header">
                        <h3>Detalles del Pedido</h3>
                        <?php if ($is_editing && isset($order_data['estado'])): ?>
                            <span class="status status-<?php echo htmlspecialchars($order_data['estado']); ?>">
                                <?php echo htmlspecialchars(ucfirst(str_replace('_', ' ', $order_data['estado']))); ?>
                            </span>
                        <?php endif; ?>
                    </div>
                    <form id="order-form" method="POST" action="<?php echo $form_action; ?>">
                        <?php
                            $default_estado = 'recibido';
                            if ($is_pago_view) {
                                $default_estado = 'pagado';
                            } else if ($is_caja_create_view) {
                                $default_estado = 'completado';
                            }
                        ?>
                        <input type="hidden" name="estado" id="estado" value="<?php echo htmlspecialchars($order_data['estado'] ?? $default_estado); ?>">

                        <div class="form-group">
                            <label for="id_mesa">Mesa</label>
                            <select id="id_mesa" name="id_mesa" required <?php if ($is_paid) echo 'disabled'; ?>>
                                <?php
                                if (empty($mesas) && $is_editing && $order_data) {
                                    echo "<option value=\"{$order_data['id_mesa']}\" selected>Mesa {$order_data['id_mesa']} (Actual)</option>";
                                }

                                foreach ($mesas as $mesa):
                                    $is_selected = $is_editing && isset($order_data['id_mesa']) && $order_data['id_mesa'] == $mesa['id'];
                                    $is_available = $mesa['estado'] == 'disponible';
                                    $is_selectable = $is_available || $is_selected;
                                ?>
                                    <option value="<?php echo $mesa['id']; ?>" <?php if ($is_selected) echo 'selected'; ?> <?php if (!$is_selectable && !$is_pago_view) echo 'disabled'; ?>>
                                        <?php echo htmlspecialchars($mesa['numero_mesa'] . ' (' . ucfirst($mesa['estado']) . ')'); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="id_usuario_mozo">Mozo</label>
                            <select id="id_usuario_mozo" name="id_usuario_mozo" required <?php if ($is_paid) echo 'disabled'; ?>>
                                 <?php foreach ($mozos as $mozo): ?>
                                    <option value="<?php echo $mozo['id']; ?>" <?php if($is_editing && $order_data['id_usuario_mozo'] == $mozo['id']) echo 'selected'; ?>>
                                        <?php echo htmlspecialchars($mozo['nombre_completo']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

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

                        <?php if ($is_editing && !$is_pago_view): ?>
                        <fieldset class="status-actions-frame">
                            <legend>Acciones Rápidas de Estado</legend>
                            <div class="btn-group">
                                <button type="button" class="btn btn-abierto status-btn" data-status="abierto">Abierto</button>
                                <button type="button" class="btn btn-completado status-btn" data-status="completado">Completado</button>
                                <button type="button" class="btn btn-cancelado status-btn" data-status="cancelado">Cancelado</button>
                            </div>
                        </fieldset>
                        <?php endif; ?>
                        
                        <div class="form-actions">
                            <button type="submit" class="btn" <?php if ($is_pago_view && !$is_paid) echo 'style="background-color: #28a745; color: white;"'; ?> <?php if ($is_paid) echo 'disabled'; ?>>
                                <?php
                                    if ($is_paid) {
                                        echo 'Pagado';
                                    } else if ($is_pago_view) {
                                        echo 'Pagar';
                                    } else if ($is_caja_create_view) {
                                        echo 'Crear Pedido';
                                    } else {
                                        echo ($is_editing ? 'Actualizar' : 'Crear') . ' Pedido';
                                    }
                                ?>
                            </button>
                            <a href="<?php echo ($is_pago_view || $is_caja_create_view) ? 'caja.php' : 'pedidos.php'; ?>" class="btn btn-secondary">
                                Volver a <?php echo ($is_pago_view || $is_caja_create_view) ? 'Caja' : 'Lista'; ?>
                            </a>
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
    const categoryFilters = document.getElementById('category-filters');
    const orderForm = document.getElementById('order-form');
    const estadoInput = document.getElementById('estado');

    const currencySymbol = '<?php echo CURRENCY_SYMBOL; ?>';
    const apiBaseUrl = '<?php echo API_BASE_URL; ?>';
    const isEditing = <?php echo json_encode($is_editing); ?>;
    const isPagoView = <?php echo json_encode($is_pago_view); ?>;
    const initialOrderData = <?php echo json_encode($is_editing ? $order_data : null); ?>;
    const mesas = <?php echo json_encode($mesas); ?>;

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

            const isService = item.categoria_tipo === 'servicios';

            const priceInput = isService
                ? `<input type="number" class="item-price" value="${item.precio.toFixed(2)}" data-id="${item.id}" step="0.01">`
                : `${currencySymbol}${item.precio.toFixed(2)}`;

            const obsRow = isService
                ? `<tr class="observation-row"><td colspan="5"><textarea class="item-observaciones" data-id="${item.id}" placeholder="Observaciones">${item.observaciones || ''}</textarea></td></tr>`
                : '';

            // Desktop Table Row
            tableBody.innerHTML += `
                <tr>
                    <td>${item.nombre}</td>
                    <td><input type="number" class="item-quantity" value="${item.cantidad}" min="1" data-id="${item.id}"></td>
                    <td>${priceInput}</td>
                    <td>${currencySymbol}${subtotal.toFixed(2)}</td>
                    <td><button type="button" class="btn-delete delete-item" data-id="${item.id}">X</button></td>
                </tr>
                ${obsRow}`;

            // Mobile Card
            const cardObsRow = isService
                ? `<div class="card-item-row"><span>Obs:</span><textarea class="item-observaciones" data-id="${item.id}" placeholder="Observaciones">${item.observaciones || ''}</textarea></div>`
                : '';

            cardsContainer.innerHTML += `
                <div class="order-item-card">
                    <div class="card-item-header">${item.nombre}</div>
                    <div class="card-item-body">
                        <div class="card-item-row"><span>Precio:</span><span>${priceInput}</span></div>
                        <div class="card-item-row"><span>Cantidad:</span><input type="number" class="item-quantity" value="${item.cantidad}" min="1" data-id="${item.id}"></div>
                        ${cardObsRow}
                        <div class="card-item-row"><span>Subtotal:</span><strong>${currencySymbol}${subtotal.toFixed(2)}</strong></div>
                    </div>
                    <div class="card-item-footer">
                        <button type="button" class="btn-delete delete-item" data-id="${item.id}">Eliminar</button>
                    </div>
                </div>`;
        }
        orderTotalElement.textContent = `${currencySymbol}${total.toFixed(2)}`;
    }

    function renderProducts(products) {
        productList.innerHTML = '';
        if (!products || products.length === 0) {
            productList.innerHTML = '<p>No se encontraron productos para esta categoría.</p>';
            return;
        }
        products.forEach(product => {
            const productItem = document.createElement('div');
            productItem.className = 'product-item';
            productItem.dataset.id = product.id;
            productItem.dataset.nombre = product.nombre;
            productItem.dataset.precio = product.precio;
            productItem.dataset.category = product.categoria_nombre;
            productItem.dataset.categoriaTipo = product.categoria_tipo;
            productItem.innerHTML = `<h4>${product.nombre}</h4><p>${currencySymbol}${parseFloat(product.precio).toFixed(2)}</p>`;
            productList.appendChild(productItem);
        });
    }

    async function fetchAndRenderProducts(category = 'all') {
        let url = `${apiBaseUrl}productos.php?estado=activo`;
        if (category !== 'all') {
            url += `&categoria_nombre=${encodeURIComponent(category)}`;
        }
        try {
            const response = await fetch(url);
            if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);
            const data = await response.json();
            renderProducts(data.records || []);
        } catch (error) {
            console.error("Error fetching products:", error);
            productList.innerHTML = '<p>Error al cargar los productos. Intente de nuevo más tarde.</p>';
        }
    }

    if (!isEditing && mesas.length === 0 && !isPagoView) {
        showAlert('No hay mesas disponibles', 'Todas las mesas están ocupadas. Por favor, libere una mesa antes de crear un nuevo pedido.');
        const formContainer = document.getElementById('order-form-container');
        if (formContainer) {
            formContainer.style.opacity = '0.5';
            formContainer.style.pointerEvents = 'none';
        }
    }

    if (isEditing && initialOrderData && initialOrderData.items) {
        initialOrderData.items.forEach(item => {
            currentOrder[item.id_producto] = {
                id: item.id_producto,
                nombre: item.nombre_producto,
                precio: parseFloat(item.precio_unitario),
                cantidad: parseInt(item.cantidad, 10),
                categoria_tipo: item.categoria_tipo,
                observaciones: item.observaciones
            };
        });
        renderOrderItems();
    }

    productList.addEventListener('click', function(e) {
        const productItem = e.target.closest('.product-item');
        if (!productItem) return;

        const productId = productItem.dataset.id;
        if (currentOrder[productId]) {
            currentOrder[productId].cantidad++;
        } else {
            currentOrder[productId] = {
                id: productId,
                nombre: productItem.dataset.nombre,
                precio: parseFloat(productItem.dataset.precio),
                cantidad: 1,
                categoria_tipo: productItem.dataset.categoriaTipo,
                observaciones: ''
            };
        }
        renderOrderItems();
    });

    orderDetailsContainer.addEventListener('click', function(e) {
        if (e.target.classList.contains('delete-item')) {
            delete currentOrder[e.target.dataset.id];
            renderOrderItems();
        }
    });

    orderDetailsContainer.addEventListener('input', function(e) {
        const productId = e.target.dataset.id;
        if (!currentOrder[productId]) return;

        if (e.target.classList.contains('item-quantity')) {
            const newQuantity = parseInt(e.target.value, 10);
            if (newQuantity > 0) {
                currentOrder[productId].cantidad = newQuantity;
            } else {
                delete currentOrder[productId];
            }
        } else if (e.target.classList.contains('item-price')) {
            currentOrder[productId].precio = parseFloat(e.target.value) || 0;
        } else if (e.target.classList.contains('item-observaciones')) {
            currentOrder[productId].observaciones = e.target.value;
        }
        renderOrderItems();
    });

    document.querySelectorAll('.status-btn').forEach(button => {
        button.addEventListener('click', function() {
            estadoInput.value = this.dataset.status;
            orderForm.requestSubmit();
        });
    });

    orderForm.addEventListener('submit', function(e) {
        const itemsInput = document.createElement('input');
        itemsInput.type = 'hidden';
        itemsInput.name = 'items';
        itemsInput.value = JSON.stringify(Object.values(currentOrder));
        this.appendChild(itemsInput);
    });

    categoryFilters.addEventListener('click', function(e) {
        const targetButton = e.target.closest('button.btn-category');
        if (!targetButton) return;

        categoryFilters.querySelector('.active')?.classList.remove('active');
        targetButton.classList.add('active');

        fetchAndRenderProducts(targetButton.dataset.category);
    });
});
</script>
