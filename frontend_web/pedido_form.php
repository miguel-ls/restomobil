<?php
session_start();

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('Location: index.php?error=Por+favor+inicie+sesión');
    exit();
}

$page_title = 'Crear Nuevo Pedido';
include_once 'templates/header.php';

// --- Funciones para obtener datos de la API ---
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
$mozos = getAPIdata('usuarios.php'); // Asumimos que todos los usuarios pueden ser mozos por ahora
$categorias = getAPIdata('categorias.php');
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
                <!-- Columna de la izquierda: Productos disponibles -->
                <div class="product-list-container">
                    <h3>Productos Disponibles</h3>
                    <input type="text" id="product-search" placeholder="Buscar producto...">
                    <div id="product-list">
                        <?php foreach ($productos as $producto): ?>
                            <div class="product-item" data-id="<?php echo $producto['id']; ?>" data-nombre="<?php echo htmlspecialchars($producto['nombre']); ?>" data-precio="<?php echo $producto['precio']; ?>">
                                <h4><?php echo htmlspecialchars($producto['nombre']); ?></h4>
                                <p>$<?php echo htmlspecialchars(number_format($producto['precio'], 2)); ?></p>
                                <p><small>Categoría: <?php echo htmlspecialchars($producto['categoria_nombre']); ?></small></p>
                            </div>
                        <?php endforeach; ?>
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
                                    <?php if ($mesa['estado'] === 'disponible'): ?>
                                        <option value="<?php echo $mesa['id']; ?>"><?php echo htmlspecialchars($mesa['numero_mesa']); ?></option>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="id_usuario_mozo">Mozo</label>
                            <select id="id_usuario_mozo" name="id_usuario_mozo" required>
                                <option value="">Seleccione un mozo</option>
                                <?php foreach ($mozos as $mozo): ?>
                                    <?php if ($mozo['activo']): ?>
                                        <option value="<?php echo $mozo['id']; ?>"><?php echo htmlspecialchars($mozo['nombre_completo']); ?></option>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </select>
                        </div>

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
                                <tbody>
                                    <!-- Los items del pedido se añadirán aquí con JS -->
                                </tbody>
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
                            <button type="submit" class="btn">Crear Pedido</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </main>
</div>

<style>
.order-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 25px;
}
.product-list-container {
    background: #fff;
    padding: 15px;
    border-radius: 8px;
    box-shadow: 0 2px 5px rgba(0,0,0,0.05);
    max-height: 70vh;
    overflow-y: auto;
}
#product-search {
    width: 100%;
    padding: 10px;
    margin-bottom: 15px;
    border: 1px solid var(--border-color);
    border-radius: 5px;
}
.product-item {
    padding: 10px;
    border: 1px solid var(--border-color);
    border-radius: 5px;
    margin-bottom: 10px;
    cursor: pointer;
    transition: background-color 0.2s;
}
.product-item:hover {
    background-color: var(--hover-light);
}
.product-item h4, .product-item p {
    margin: 0;
}
.order-details {
    background: #fff;
    padding: 15px;
    border-radius: 8px;
    box-shadow: 0 2px 5px rgba(0,0,0,0.05);
}
#order-items-table input[type="number"] {
    width: 60px;
    padding: 5px;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const productList = document.getElementById('product-list');
    const orderItemsTableBody = document.querySelector('#order-items-table tbody');
    const orderTotalElement = document.getElementById('order-total');
    const orderForm = document.getElementById('order-form');
    const productSearch = document.getElementById('product-search');

    let currentOrder = {}; // Objeto para mantener los items del pedido: { productId: { ... } }

    // 1. Añadir producto al pedido
    productList.addEventListener('click', function(e) {
        const productItem = e.target.closest('.product-item');
        if (!productItem) return;

        const productId = productItem.dataset.id;

        if (currentOrder[productId]) {
            // Si el producto ya está en el pedido, incrementa la cantidad
            currentOrder[productId].cantidad++;
        } else {
            // Si es nuevo, añadirlo al pedido
            currentOrder[productId] = {
                id: productId,
                nombre: productItem.dataset.nombre,
                precio: parseFloat(productItem.dataset.precio),
                cantidad: 1
            };
        }
        renderOrderTable();
    });

    // 2. Manejar cambios en la tabla de pedido (cantidad, eliminar)
    orderItemsTableBody.addEventListener('click', function(e) {
        const target = e.target;
        const productId = target.dataset.id;

        if (target.classList.contains('delete-item')) {
            delete currentOrder[productId];
        }
        renderOrderTable();
    });

    orderItemsTableBody.addEventListener('input', function(e) {
        const target = e.target;
        const productId = target.dataset.id;

        if (target.classList.contains('item-quantity')) {
            const newQuantity = parseInt(target.value, 10);
            if (newQuantity > 0) {
                currentOrder[productId].cantidad = newQuantity;
            } else {
                delete currentOrder[productId];
            }
        }
        renderOrderTable();
    });

    // 3. Renderizar (o re-renderizar) la tabla de items del pedido
    function renderOrderTable() {
        orderItemsTableBody.innerHTML = '';
        let total = 0;

        for (const productId in currentOrder) {
            const item = currentOrder[productId];
            const subtotal = item.precio * item.cantidad;
            total += subtotal;

            const row = document.createElement('tr');
            row.innerHTML = `
                <td>${item.nombre}</td>
                <td><input type="number" class="item-quantity" value="${item.cantidad}" min="1" data-id="${item.id}"></td>
                <td>$${item.precio.toFixed(2)}</td>
                <td>$${subtotal.toFixed(2)}</td>
                <td><button type="button" class="btn-delete delete-item" data-id="${item.id}">X</button></td>
            `;
            orderItemsTableBody.appendChild(row);
        }
        orderTotalElement.textContent = `$${total.toFixed(2)}`;
    }

    // 4. Filtrar la lista de productos
    productSearch.addEventListener('keyup', function() {
        const searchTerm = productSearch.value.toLowerCase();
        const allProducts = document.querySelectorAll('#product-list .product-item');
        allProducts.forEach(item => {
            const nombre = item.dataset.nombre.toLowerCase();
            if (nombre.includes(searchTerm)) {
                item.style.display = 'block';
            } else {
                item.style.display = 'none';
            }
        });
    });

    // 5. Enviar el formulario
    orderForm.addEventListener('submit', function(e) {
        e.preventDefault();

        const id_mesa = document.getElementById('id_mesa').value;
        const id_usuario_mozo = document.getElementById('id_usuario_mozo').value;
        const items = Object.values(currentOrder).map(item => ({ id: item.id, cantidad: item.cantidad }));

        if (!id_mesa || !id_usuario_mozo) {
            alert('Por favor, seleccione una mesa y un mozo.');
            return;
        }

        if (items.length === 0) {
            alert('El pedido está vacío. Por favor, añada al menos un producto.');
            return;
        }

        const orderData = {
            id_mesa: id_mesa,
            id_usuario_mozo: id_usuario_mozo,
            items: items
        };

        fetch('http://localhost/restaurante_system/backend/api/v1/pedidos.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(orderData)
        })
        .then(response => response.json())
        .then(data => {
            if (data.id) {
                window.location.href = `pedidos.php?success=Pedido+${data.id}+creado+exitosamente.`;
            } else {
                alert('Error al crear el pedido: ' + (data.message || 'Error desconocido.'));
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Ocurrió un error de red al intentar crear el pedido.');
        });
    });
});
</script>

<?php
include_once 'templates/footer.php';
?>
