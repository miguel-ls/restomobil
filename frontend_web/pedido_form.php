<?php
session_start();

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('Location: index.php?error=Por+favor+inicie+sesión');
    exit();
}

$page_title = 'Crear Nuevo Pedido';
include_once 'templates/header.php';
// Asumo que estos archivos de configuración y los CRUD básicos ya existen
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
    $page_title = "Editar Pedido #$order_id";
    $order_data = fetchFromAPI("pedidos.php?id=$order_id");
    if (isset($order_data['error']) || !$order_data) {
        // Redirigir o mostrar un mensaje de error amigable
        // Por simplicidad, aquí solo mostraremos un mensaje básico.
        $page_title = "Error";
        // Asegúrate de que el resto de la página pueda manejar $order_data como nulo
        $order_data = null;
        // Opcional: podrías incluir una plantilla de error aquí y salir.
        // include 'templates/error_view.php';
        // exit();
    }
}

if ($is_editing && $order_data) {
    // Al editar, queremos todas las mesas para poder mostrar la mesa actual del pedido aunque no esté disponible.
    $mesas_data = fetchFromAPI('mesas.php');
} else {
    // Para pedidos nuevos, solo mostrar mesas disponibles.
    $mesas_data = fetchFromAPI('mesas.php?status=available');
}
$mozos_data = fetchFromAPI('usuarios.php?rol=Mozo');
$productos_data = fetchFromAPI('productos.php?estado=activo');
$categorias_data = fetchFromAPI('categorias.php');

$mesas = isset($mesas_data['records']) ? $mesas_data['records'] : [];
$mozos = isset($mozos_data['records']) ? $mozos_data['records'] : [];
$productos = isset($productos_data['records']) ? $productos_data['records'] : [];
$categorias = isset($categorias_data['records']) ? $categorias_data['records'] : [];
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
                    <form id="order-form" method="POST" action="pedido_handler.php<?php if($is_editing) echo '?id=' . $order_id; ?>">
                        <input type="hidden" name="estado" id="estado" value="<?php echo htmlspecialchars($order_data['estado'] ?? 'recibido'); ?>">

                        <div class="form-group">
                            <label for="id_mesa">Mesa</label>
                            <select id="id_mesa" name="id_mesa" required>
                                <?php
                                if (empty($mesas) && $is_editing && $order_data) {
                                    // Si no hay mesas, pero estamos editando, al menos mostrar la mesa del pedido actual.
                                    // Esto es un fallback en caso de que la API falle pero tengamos datos del pedido.
                                    echo "<option value=\"{$order_data['id_mesa']}\" selected>Mesa {$order_data['id_mesa']} (Actual)</option>";
                                }

                                foreach ($mesas as $mesa):
                                    $is_selected = $is_editing && isset($order_data['id_mesa']) && $order_data['id_mesa'] == $mesa['id'];
                                    $is_available = $mesa['estado'] == 'disponible';
                                    // La mesa se puede seleccionar si está disponible, o si es la mesa que ya está seleccionada en el pedido que se edita.
                                    $is_selectable = $is_available || $is_selected;
                                ?>
                                    <option value="<?php echo $mesa['id']; ?>" <?php if ($is_selected) echo 'selected'; ?> <?php if (!$is_selectable) echo 'disabled'; ?>>
                                        <?php echo htmlspecialchars($mesa['numero_mesa']); ?> (<?php echo htmlspecialchars(ucfirst($mesa['estado']))); ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="id_usuario_mozo">Mozo</label>
                            <select id="id_usuario_mozo" name="id_usuario_mozo" required>
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

                        <?php if ($is_editing): ?>
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
                            <button type="submit" class="btn"><?php echo $is_editing ? 'Actualizar' : 'Crear'; ?> Pedido</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </main>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const mesas = <?php echo json_encode($mesas); ?>;
    if (mesas.length === 0) {
        showModal('No hay mesas disponibles', 'En este momento, todas las mesas están ocupadas o no disponibles. Por favor, inténtelo de nuevo más tarde.');
        // Opcional: deshabilitar el formulario
        const formContainer = document.getElementById('order-form-container');
        if (formContainer) {
            formContainer.style.opacity = '0.5';
            formContainer.style.pointerEvents = 'none';
        }
    }

    const productList = document.getElementById('product-list');
    const orderDetailsContainer = document.getElementById('order-details');
    const orderTotalElement = document.getElementById('order-total');
    const currencySymbol = '<?php echo CURRENCY_SYMBOL; ?>';
    const isEditing = <?php echo json_encode($is_editing); ?>;
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

    const statusButtons = document.querySelectorAll('.status-btn');
    const estadoInput = document.getElementById('estado');
    const orderForm = document.getElementById('order-form');

    statusButtons.forEach(button => {
        button.addEventListener('click', function() {
            const newStatus = this.dataset.status;
            estadoInput.value = newStatus;
            // requestSubmit() es una forma más robusta de activar el envío del formulario
            // programáticamente, asegurando que el evento 'submit' se dispare.
            orderForm.requestSubmit();
        });
    });

    orderForm.addEventListener('submit', function(e) {
        // e.preventDefault(); // Descomentar para depurar sin enviar
        const itemsInput = document.createElement('input');
        itemsInput.type = 'hidden';
        itemsInput.name = 'items';
        itemsInput.value = JSON.stringify(Object.values(currentOrder));
        this.appendChild(itemsInput);
        // El formulario se enviará de forma nativa
    });

    // Lógica para el filtro de categorías
    const categoryFilters = document.getElementById('category-filters');
    const productListContainer = document.getElementById('product-list');
    const apiBaseUrl = '<?php echo API_BASE_URL; ?>';
    const currencySymbol = '<?php echo CURRENCY_SYMBOL; ?>';

    function renderProducts(products) {
        productListContainer.innerHTML = '';
        if (!products || products.length === 0) {
            productListContainer.innerHTML = '<p>No se encontraron productos para esta categoría.</p>';
            return;
        }
        products.forEach(product => {
            const productItem = document.createElement('div');
            productItem.className = 'product-item';
            productItem.dataset.id = product.id;
            productItem.dataset.nombre = product.nombre;
            productItem.dataset.precio = product.precio;
            productItem.dataset.category = product.categoria_nombre;
            productItem.innerHTML = `<h4>${product.nombre}</h4><p>${currencySymbol}${parseFloat(product.precio).toFixed(2)}</p>`;
            productListContainer.appendChild(productItem);
        });
    }

    async function fetchAndRenderProducts(category = 'all') {
        let url = `${apiBaseUrl}productos.php?estado=activo`;
        if (category !== 'all') {
            url += `&categoria_nombre=${encodeURIComponent(category)}`;
        }

        try {
            const response = await fetch(url);
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            const data = await response.json();
            // The API returns { "records": [...] } or { "message": "..." } on no results
            renderProducts(data.records || []);
        } catch (error) {
            console.error("Error fetching products:", error);
            productListContainer.innerHTML = '<p>Error al cargar los productos. Intente de nuevo más tarde.</p>';
        }
    }

    categoryFilters.addEventListener('click', function(e) {
        const targetButton = e.target.closest('button.btn-category');
        if (!targetButton) return;

        const currentActive = categoryFilters.querySelector('.active');
        if (currentActive) {
            currentActive.classList.remove('active');
        }
        targetButton.classList.add('active');

        const selectedCategory = targetButton.dataset.category;
        fetchAndRenderProducts(selectedCategory);
    });
});
</script>
