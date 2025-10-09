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

// Determinar la URL de retorno
$from_page = $_GET['from'] ?? 'pedidos'; // 'pedidos' por defecto
$return_url = ($from_page === 'caja' || $is_caja_create_view || $is_pago_view) ? 'caja.php' : 'pedidos.php';
$return_page_name = ($from_page === 'caja' || $is_caja_create_view || $is_pago_view) ? 'Caja' : 'Lista';

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
                        <?php if ($is_editing && isset($order_data['estado'])): ?>
                            <span class="status status-<?php echo htmlspecialchars($order_data['estado']); ?>">
                                <?php echo htmlspecialchars(ucfirst(str_replace('_', ' ', $order_data['estado']))); ?>
                            </span>
                        <?php endif; ?>
                    </div>
                    <form id="order-form" method="POST" action="<?php echo $form_action; ?>">
                        <?php
                            $default_estado = 'abierto';
                            if ($is_pago_view) {
                                $default_estado = 'pagado';
                            } else if ($is_caja_create_view) {
                                $default_estado = 'completado';
                            }
                        ?>
                        <input type="hidden" name="estado" id="estado" value="<?php echo htmlspecialchars($order_data['estado'] ?? $default_estado); ?>">
                        <input type="hidden" name="next_action" id="next_action" value="">

                        <div class="form-actions" style="margin-bottom: 20px; padding-bottom: 20px; border-bottom: 1px solid #e0e0e0;">
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
                            <a id="volver-link" href="<?php echo $return_url; ?>" class="btn btn-secondary">
                                Volver a <?php echo $return_page_name; ?>
                            </a>
                        </div>

                        <div class="tab-nav">
                            <button type="button" class="tab-button active" data-tab="details">Detalle</button>
                            <button type="button" class="tab-button" data-tab="client">Cliente</button>
                        </div>

                        <div class="tab-content">
                            <div id="tab-details" class="tab-pane active">
                                <div class="form-group-row">
                                    <div class="form-group" style="flex-grow: 1;">
                                        <label for="id_mesa"><?php echo PUNTO_VENTA; ?></label>
                                        <!-- <select id="id_mesa" name="id_mesa" required <?php if ($is_paid) echo 'disabled'; ?>> -->
                                        <select id="id_mesa" name="id_mesa" >
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
                                    <div class="form-group" style="flex-grow: 1;">
                                        <label for="id_usuario_mozo"><?php echo VENDEDOR; ?></label>
                                        <!-- <select id="id_usuario_mozo" name="id_usuario_mozo" required <?php if ($is_paid) echo 'disabled'; ?>> -->
                                        <select id="id_usuario_mozo" name="id_usuario_mozo" >
                                            <?php foreach ($mozos as $mozo): ?>
                                                <option value="<?php echo $mozo['id']; ?>" <?php if($is_editing && $order_data['id_usuario_mozo'] == $mozo['id']) echo 'selected'; ?>>
                                                    <?php echo htmlspecialchars($mozo['nombre_completo']); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
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
                            </div>
                            <div id="tab-client" class="tab-pane">
                                <div class="form-group-row">
                                    <div class="form-group" style="flex-grow: 1;">
                                        <label for="id_tipo_documento_venta">Tipo de Comprobante</label>
                                        <select id="id_tipo_documento_venta" name="id_tipo_documento_venta" <?php if ($is_paid) echo 'disabled'; ?>>
                                            <option value="">Seleccione...</option>
                                        </select>
                                    </div>
                                    <div class="form-group" style="flex-grow: 1;">
                                        <label for="id_serie_documento">Serie</label>
                                        <select id="id_serie_documento" name="id_serie_documento" <?php if ($is_paid) echo 'disabled'; ?>>
                                            <option value="">--</option>
                                        </select>
                                    </div>
                                    <div class="form-group" style="flex-grow: 1; display: none;">
                                        <label for="numero_documento">Número</label>
                                        <input type="text" id="numero_documento" name="numero_documento" readonly>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label for="cliente_select">Buscar Cliente</label>
                                    <select id="cliente_select" name="cliente_select">
                                        <option value="">Seleccione un cliente...</option>
                                    </select>
                                </div>
                                <input type="hidden" id="id_cliente" name="id_cliente" value="<?php echo htmlspecialchars($order_data['id_cliente'] ?? ''); ?>">

                                <div class="form-group-row">
                                    <div class="form-group" style="flex-grow: 1;">
                                        <label for="id_tipo_documento_identidad_cliente">Tipo Documento</label>
                                        <select id="id_tipo_documento_identidad_cliente" name="id_tipo_documento_identidad_cliente">
                                            <option value="">Seleccione...</option>
                                        </select>
                                    </div>
                                    <div class="form-group" style="flex-grow: 2;">
                                        <label for="cliente_ruc">RUC / DNI</label>
                                        <div style="display: flex; gap: 10px;">
                                            <input type="text" id="cliente_ruc" name="cliente_ruc" value="<?php echo htmlspecialchars($order_data['ruc_cliente'] ?? ''); ?>" style="flex-grow: 1;">
                                            <button type="button" id="btn-sunat-main" class="btn" style="flex-shrink: 0;">Sunat</button>
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label for="cliente_nombre">Nombre del Cliente</label>
                                    <input type="text" id="cliente_nombre" name="cliente_nombre" value="<?php echo htmlspecialchars($order_data['nombre_cliente'] ?? ''); ?>">
                                </div>

                                <div class="form-group-row">
                                    <div class="form-group" style="flex-grow: 2;">
                                        <label for="cliente_direccion">Dirección</label>
                                        <input type="text" id="cliente_direccion" name="cliente_direccion" value="<?php echo htmlspecialchars($order_data['direccion_cliente'] ?? ''); ?>">
                                    </div>
                                    <div class="form-group" style="flex-grow: 1;">
                                        <label for="cliente_ubigeo">Ubigeo</label>
                                        <input type="text" id="cliente_ubigeo" name="cliente_ubigeo" value="<?php echo htmlspecialchars($order_data['codigo_ubigeo'] ?? ''); ?>">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <?php if ($is_editing): ?>
                        <fieldset class="status-actions-frame">
                            <legend>Acciones Rápidas de Estado</legend>
                            <div class="btn-group">
                                <?php if ($order_data['estado'] !== 'abierto'): ?>
                                    <button type="button" class="btn btn-abierto status-btn" data-status="abierto">Abrir</button>
                                <?php endif; ?>
                                <?php if ($order_data['estado'] !== 'completado'): ?>
                                    <button type="button" class="btn btn-completado status-btn" data-status="completado">Completar</button>
                                <?php endif; ?>
                                <?php if ($order_data['estado'] !== 'cancelado'): ?>
                                    <button type="button" class="btn btn-cancelado status-btn" data-status="cancelado">Cancelar</button>
                                <?php endif; ?>
                            </div>
                        </fieldset>
                        <?php endif; ?>
                        
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

            const isService = item.categoria_tipo === 'Servicios';

            const priceInput = isService
                ? `<input type="number" class="item-price" value="${item.precio.toFixed(2)}" data-id="${item.id}" step="0.01">`
                : `${currencySymbol}${item.precio.toFixed(2)}`;

            const obsRow = isService
                ? `<tr class="observation-row"><td colspan="5"><textarea style="width: 98%;" class="item-observaciones" data-id="${item.id}" placeholder="Observaciones">${item.observaciones || ''}</textarea></td></tr>`
                : '';

            tableBody.innerHTML += `
                <tr>
                    <td>${item.nombre}</td>
                    <td><input type="number" class="item-quantity" value="${item.cantidad}" min="1" data-id="${item.id}"></td>
                    <td>${priceInput}</td>
                    <td>${currencySymbol}${subtotal.toFixed(2)}</td>
                    <td><button type="button" class="btn-delete delete-item" data-id="${item.id}">X</button></td>
                </tr>
                ${obsRow}`;

            const cardObsRow = isService
                ? `<div class="card-item-row"><span>Obs:</span><textarea style="width: 95%;" class="item-observaciones" data-id="${item.id}" placeholder="Observaciones">${item.observaciones || ''}</textarea></div>`
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
        showAlert('No hay mesas disponibles', 'Todas las puntos de ventas están ocupadas. Por favor, libere un punto de venta antes de crear un nuevo pedido.');
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

        let shouldRender = true;
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
            shouldRender = false;
        }

        if (shouldRender) {
            renderOrderItems();
        }
    });

    document.querySelectorAll('.status-btn').forEach(button => {
        button.addEventListener('click', async function(e) {
            e.preventDefault();
            const newStatus = this.dataset.status;
            const orderId = isEditing ? initialOrderData.id : null;

            if (!orderId) {
                showAlert('Error', 'No se encontró el ID del pedido para actualizar.');
                return;
            }

            // El botón "Cancelar" no debe guardar cambios, por lo que mantiene la lógica original.
            if (newStatus === 'cancelado') {
                if (!confirm('¿Está seguro de que desea cancelar este pedido? Esta acción no se puede deshacer.')) {
                    return;
                }
                this.disabled = true;
                this.textContent = 'Cancelando...';
                try {
                    const response = await fetch(`${apiBaseUrl}pedidos.php?id=${orderId}&action=update_status`, {
                        method: 'PUT',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ estado: newStatus })
                    });
                    const result = await response.json();
                    if (!response.ok) throw new Error(result.message || 'Error desconocido.');

                    const volverLink = document.getElementById('volver-link');
                    const redirectUrl = volverLink ? volverLink.href : 'pedidos.php';
                    const finalUrl = new URL(redirectUrl, window.location.origin);
                    finalUrl.searchParams.set('success', 'El pedido ha sido cancelado.');
                    window.location.href = finalUrl.href;

                } catch (error) {
                    showAlert('Error', `No se pudo cancelar el pedido: ${error.message}`);
                    this.disabled = false;
                    this.textContent = 'Cancelar';
                }
                return;
            }

            // Para "Abrir" y "Completar", primero guardamos los cambios.
            // Establecemos la acción siguiente y enviamos el formulario.
            document.getElementById('next_action').value = newStatus;

            // Adjuntar los items al formulario antes de enviarlo
            const itemsInput = document.createElement('input');
            itemsInput.type = 'hidden';
            itemsInput.name = 'items';
            itemsInput.value = JSON.stringify(Object.values(currentOrder));
            orderForm.appendChild(itemsInput);

            // Cambiar el estado del botón principal para que el usuario entienda la acción
            const mainSubmitButton = orderForm.querySelector('button[type="submit"]');
            if(mainSubmitButton) {
                mainSubmitButton.textContent = 'Guardando y ' + newStatus.charAt(0).toUpperCase() + newStatus.slice(1) + '...';
                mainSubmitButton.disabled = true;
            }

            orderForm.submit();
        });
    });

    // --- INICIO: Funciones para validación de cierre de caja ---

    /**
     * Devuelve una fecha en formato YYYY-MM-DD.
     * Si no se proporciona una fecha, se utiliza la fecha actual.
     * @param {string|null} dateString - La fecha en formato ISO o similar.
     * @returns {string} La fecha formateada.
     */
    function getFormattedDate(dateString = null) {
        const date = dateString ? new Date(dateString) : new Date();
        const year = date.getFullYear();
        const month = String(date.getMonth() + 1).padStart(2, '0');
        const day = String(date.getDate()).padStart(2, '0');
        return `${year}-${month}-${day}`;
    }

    /**
     * Llama a la API para verificar si existe un cierre de caja para una fecha dada.
     * @param {string} fecha - La fecha a verificar en formato YYYY-MM-DD.
     * @returns {Promise<boolean>} - True si existe un cierre, false en caso contrario.
     */
    async function verificarCierreExistente(fecha) {
        try {
            const response = await fetch(`${apiBaseUrl}verificar_cierre.php?fecha=${fecha}`);
            if (!response.ok) {
                const errorData = await response.json();
                throw new Error(errorData.message || 'Error de comunicación con la API de verificación.');
            }
            const data = await response.json();
            return data.cierre_existente;
        } catch (error) {
            console.error('Error al verificar el cierre:', error);
            // Relanzamos el error para que sea manejado por quien llama a la función.
            throw error;
        }
    }

    // --- FIN: Funciones para validación de cierre de caja ---

    orderForm.addEventListener('submit', async function(e) {
        e.preventDefault();

        // --- INICIO: VALIDACIÓN DE CIERRE DE CAJA ---
        // Determinar la fecha del pedido: la de creación si se edita, o la actual si es nuevo.
        const fechaDelPedido = isEditing && initialOrderData.fecha_creacion
            ? getFormattedDate(initialOrderData.fecha_creacion)
            : getFormattedDate();

        try {
            const cierreExistente = await verificarCierreExistente(fechaDelPedido);
            if (cierreExistente) {
                showAlert('Operación Bloqueada', 'La fecha del pedido tiene un cierre de caja. No se puede crear ni modificar el pedido.');
                return; // Detener el envío del formulario.
            }
        } catch (error) {
            // Si la API falla, mostramos un error y detenemos la operación.
            showAlert('Error de Verificación', `No se pudo verificar el estado de la caja: ${error.message}. Por favor, intente de nuevo.`);
            return;
        }
        // --- FIN: VALIDACIÓN DE CIERRE DE CAJA ---

        if (isPagoView) {
            const serieSelect = document.getElementById('id_serie_documento');
            if (!serieSelect.value) {
                alert('Por favor, seleccione una serie para el comprobante.');
                return;
            }

            const submitButton = this.querySelector('button[type="submit"]');
            submitButton.disabled = true;
            submitButton.textContent = 'Procesando...';

            const payload = {
                id_pedido: initialOrderData.id,
                id_serie_documento: serieSelect.value,
                id_tipo_documento_venta: document.getElementById('id_tipo_documento_venta').value
            };

            try {
                const response = await fetch(`${apiBaseUrl}procesar_venta.php`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(payload)
                });
                const result = await response.json();
                if (!response.ok) throw new Error(result.message || 'Error desconocido en el servidor.');
                alert(`Venta generada con éxito. Número: ${result.numero_documento}`);
                window.location.href = 'ventas.php';
            } catch (error) {
                console.error('Error al procesar el pago:', error);
                alert('Error al procesar el pago: ' + error.message);
                submitButton.disabled = false;
                submitButton.textContent = 'Pagar';
            }
            return;
        }

        const tipoComprobanteSelect = document.getElementById('id_tipo_documento_venta');
        const selectedComprobante = tipoComprobanteSelect.options[tipoComprobanteSelect.selectedIndex];
        if (selectedComprobante && selectedComprobante.textContent === 'Factura') {
            const idCliente = document.getElementById('id_cliente').value;
            const clienteNombre = document.getElementById('cliente_nombre').value;
            const clienteDireccion = document.getElementById('cliente_direccion').value;
            if (!idCliente || !clienteNombre || !clienteDireccion) {
                alert('Para emitir una Factura, debe seleccionar un cliente con RUC, nombre y dirección.');
                return;
            }
        } else if (document.getElementById('id_cliente').value) {
            const clienteNombre = document.getElementById('cliente_nombre').value;
            if (!clienteNombre) {
                alert('El campo nombre del cliente es obligatorio si se ha seleccionado un cliente.');
                return;
            }
        }

        const itemsInput = document.createElement('input');
        itemsInput.type = 'hidden';
        itemsInput.name = 'items';
        itemsInput.value = JSON.stringify(Object.values(currentOrder));
        this.appendChild(itemsInput);
        this.submit();
    });

    categoryFilters.addEventListener('click', function(e) {
        const targetButton = e.target.closest('button.btn-category');
        if (!targetButton) return;
        categoryFilters.querySelector('.active')?.classList.remove('active');
        targetButton.classList.add('active');
        fetchAndRenderProducts(targetButton.dataset.category);
    });

    const tabNav = document.querySelector('.tab-nav');
    const tabPanes = document.querySelectorAll('.tab-content .tab-pane');
    tabNav.addEventListener('click', function(e) {
        const targetButton = e.target.closest('.tab-button');
        if (!targetButton) return;
        tabNav.querySelector('.active')?.classList.remove('active');
        tabPanes.forEach(pane => pane.classList.remove('active'));
        targetButton.classList.add('active');
        const tabId = targetButton.dataset.tab;
        document.getElementById('tab-' + tabId).classList.add('active');
    });

    const tipoComprobanteSelect = document.getElementById('id_tipo_documento_venta');
    const serieDocumentoSelect = document.getElementById('id_serie_documento');
    async function loadSeries(tipoDocId, selectedSerieId = null) {
        serieDocumentoSelect.innerHTML = '<option value="">Cargando...</option>';
        serieDocumentoSelect.disabled = true;
        if (!tipoDocId) {
            serieDocumentoSelect.innerHTML = '<option value="">--</option>';
            return;
        }
        try {
            const response = await fetch(`${apiBaseUrl}series_documentos.php?id_tipo_documento=${tipoDocId}`);
            const data = await response.json();
            serieDocumentoSelect.innerHTML = '<option value="">Seleccione...</option>';
            if (data.records && data.records.length > 0) {
                data.records.forEach(serie => {
                    const option = document.createElement('option');
                    option.value = serie.id;
                    option.textContent = serie.serie;
                    if (selectedSerieId && serie.id == selectedSerieId) option.selected = true;
                    serieDocumentoSelect.appendChild(option);
                });
                serieDocumentoSelect.disabled = false;
            } else {
                 serieDocumentoSelect.innerHTML = '<option value="">No hay series</option>';
            }
        } catch (error) {
            console.error('Error loading series:', error);
            serieDocumentoSelect.innerHTML = '<option value="">Error</option>';
        }
    }
    tipoComprobanteSelect.addEventListener('change', () => loadSeries(tipoComprobanteSelect.value));
    serieDocumentoSelect.addEventListener('change', async function() {
        const serieId = this.value;
        const numeroDocumentoInput = document.getElementById('numero_documento');
        if (!serieId) {
            numeroDocumentoInput.value = '';
            return;
        }
        numeroDocumentoInput.value = 'Cargando...';
        try {
            const response = await fetch(`${apiBaseUrl}correlativo_serie.php?id_serie_documento=${serieId}`);
            if (!response.ok) throw new Error('Respuesta de red no fue OK');
            const data = await response.json();
            numeroDocumentoInput.value = data.next_correlativo ? String(data.next_correlativo).padStart(8, '0') : 'Error';
        } catch (error) {
            console.error('Error fetching correlativo:', error);
            numeroDocumentoInput.value = 'Error al cargar';
        }
    });

    const clienteSelect = document.getElementById('cliente_select');
    const idClienteInput = document.getElementById('id_cliente');
    const clienteNombreInput = document.getElementById('cliente_nombre');
    const clienteTipoDocIdentidadSelect = document.getElementById('id_tipo_documento_identidad_cliente');
    const clienteRucInput = document.getElementById('cliente_ruc');
    const clienteDireccionInput = document.getElementById('cliente_direccion');
    const clienteUbigeoInput = document.getElementById('cliente_ubigeo');
    const btnSunatMain = document.getElementById('btn-sunat-main');

    async function loadClientsForSelect() {
        try {
            const response = await fetch(`${apiBaseUrl}clientes.php`);
            const data = await response.json();
            if (data.records) {
                data.records.forEach(cliente => {
                    const option = document.createElement('option');
                    option.value = cliente.id;
                    option.textContent = `${cliente.nombres_apellidos} - ${cliente.numero_documento}`;
                    option.dataset.clientData = JSON.stringify(cliente);
                    clienteSelect.appendChild(option);
                });
            }
        } catch (error) { console.error('Error loading clients:', error); }
    }
    clienteSelect.addEventListener('change', function() {
        const selectedOption = this.options[this.selectedIndex];
        if (selectedOption.value && selectedOption.dataset.clientData) {
            const clientData = JSON.parse(selectedOption.dataset.clientData);
            selectCliente(clientData);
        } else {
            clearClienteSelection();
        }
    });

    async function loadSaleDocumentTypes() {
        try {
            const response = await fetch(`${apiBaseUrl}tipos_documentos.php`);
            const data = await response.json();
            if (data.records) {
                data.records.forEach(tipo => {
                    const option = document.createElement('option');
                    option.value = tipo.id;
                    option.textContent = tipo.nombre;
                    if (isEditing && initialOrderData && initialOrderData.id_tipo_documento_venta == tipo.id) option.selected = true;
                    tipoComprobanteSelect.appendChild(option);
                });
            }
        } catch (error) { console.error('Error loading sale document types:', error); }
    }

    async function loadIdentityDocumentTypes(selectElement) {
        try {
            const response = await fetch(`${apiBaseUrl}tipo_documento_identidad.php`);
            const data = await response.json();
            if (data.records) {
                data.records.forEach(type => {
                    const option = document.createElement('option');
                    option.value = type.id;
                    option.dataset.codigo = type.codigo;
                    option.textContent = type.nombre;
                    selectElement.appendChild(option.cloneNode(true));
                });
            }
        } catch (error) { console.error('Error loading identity document types:', error); }
    }

    function clearClienteSelection() {
        idClienteInput.value = '';
        clienteNombreInput.value = '';
        clienteTipoDocIdentidadSelect.value = '';
        clienteRucInput.value = '';
        clienteDireccionInput.value = '';
        clienteUbigeoInput.value = '';
        [clienteNombreInput, clienteRucInput, clienteDireccionInput, clienteUbigeoInput].forEach(el => el.readOnly = false);
        clienteTipoDocIdentidadSelect.disabled = false;
    }

    function selectCliente(cliente) {
        idClienteInput.value = cliente.id;
        clienteNombreInput.value = cliente.nombres_apellidos;
        clienteTipoDocIdentidadSelect.value = cliente.id_tipo_documento_identidad;
        clienteRucInput.value = cliente.numero_documento;
        clienteDireccionInput.value = cliente.direccion;
        clienteUbigeoInput.value = cliente.codigo_ubigeo;
        clienteSelect.value = cliente.id;
        [clienteNombreInput, clienteRucInput, clienteDireccionInput, clienteUbigeoInput].forEach(el => el.readOnly = false);
        clienteTipoDocIdentidadSelect.disabled = false;
    }

    btnSunatMain.addEventListener('click', async () => {
        const docNumber = clienteRucInput.value.trim();
        const selectedOption = clienteTipoDocIdentidadSelect.options[clienteTipoDocIdentidadSelect.selectedIndex];
        const docCode = selectedOption ? selectedOption.dataset.codigo : null;
        if (!docNumber || !docCode) {
            alert('Por favor, ingrese un número de RUC o DNI y seleccione el tipo de documento correspondiente.');
            return;
        }
        let queryType = (docCode === '1') ? 'dni' : 'ruc';
        btnSunatMain.textContent = 'Buscando...';
        btnSunatMain.disabled = true;
        try {
            const response = await fetch(`consulta_api_externa.php?tipo=${queryType}&numero=${docNumber}`);
            const data = await response.json();
            if (data.error) throw new Error(data.error);
            clienteNombreInput.value = data.nombre || '';
            clienteDireccionInput.value = data.direccion || '';
            clienteUbigeoInput.value = data.ubigeo || '';
        } catch (error) {
            alert('Error al consultar: ' + error.message);
        } finally {
            btnSunatMain.textContent = 'Sunat';
            btnSunatMain.disabled = false;
        }
    });

    async function initializeForm() {
        await Promise.all([
            loadSaleDocumentTypes(),
            loadIdentityDocumentTypes(clienteTipoDocIdentidadSelect),
            loadClientsForSelect()
        ]);

        if (tipoComprobanteSelect.value) {
            await loadSeries(tipoComprobanteSelect.value, isEditing ? initialOrderData.id_serie_documento : null);
        }

        if (isEditing && initialOrderData) {
            if (initialOrderData.numero_documento) {
                document.getElementById('numero_documento').value = initialOrderData.numero_documento;
            }
            if (initialOrderData.id_cliente) {
                selectCliente({
                    id: initialOrderData.id_cliente,
                    nombres_apellidos: initialOrderData.nombre_cliente,
                    id_tipo_documento_identidad: initialOrderData.id_tipo_documento_identidad_cliente,
                    numero_documento: initialOrderData.ruc_cliente,
                    direccion: initialOrderData.direccion_cliente,
                    codigo_ubigeo: initialOrderData.codigo_ubigeo
                });
            }
        } else {
            clearClienteSelection();
        }
    }

    initializeForm();
});
</script>
<style>
.form-group-row {
    display: flex;
    gap: 20px;
}
#cliente_search_results {
    border: 1px solid #ccc;
    max-height: 150px;
    overflow-y: auto;
    position: absolute;
    background-color: white;
    width: calc(100% - 22px);
    z-index: 1000;
}
.search-result-item {
    padding: 8px 12px;
    cursor: pointer;
}
.search-result-item:hover {
    background-color: #f0f0f0;
}
.modal {
    display: none;
    position: fixed;
    z-index: 1001;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    overflow: auto;
    background-color: rgb(0,0,0);
    background-color: rgba(0,0,0,0.4);
}
.modal-content {
    background-color: #fefefe;
    margin: 10% auto;
    padding: 20px;
    border: 1px solid #888;
    width: 80%;
    max-width: 500px;
    border-radius: 5px;
}
.close-btn {
    color: #aaa;
    float: right;
    font-size: 28px;
    font-weight: bold;
}
.close-btn:hover,
.close-btn:focus {
    color: black;
    text-decoration: none;
    cursor: pointer;
}
</style>
