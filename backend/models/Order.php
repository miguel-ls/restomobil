<?php
require_once __DIR__ . '/../core/Database.php';

class Order {
    private $conn;

    public function __construct() {
        $this->conn = Database::getInstance();
    }

    /**
     * Lee todos los pedidos de la base de datos.
     * @return PDOStatement
     */
    public function readAll() {
        $query = "CALL sp_getAllOrders()";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    /**
     * Lee los detalles completos de un solo pedido por su ID.
     * @param int $id El ID del pedido.
     * @return array|false Un array con los detalles del pedido o false si no se encuentra.
     */
    public function readOne($id) {
        // 1. Obtener la cabecera del pedido
        $query_header = "CALL sp_getOrderDetail(:id)";
        $stmt_header = $this->conn->prepare($query_header);
        $stmt_header->bindParam(':id', $id);
        $stmt_header->execute();

        $order_details = $stmt_header->fetch(PDO::FETCH_ASSOC);

        // Si no se encuentra el pedido, devolver false
        if (!$order_details) {
            return false;
        }

        // Liberar el cursor para la siguiente consulta
        $stmt_header->closeCursor();

        // 2. Obtener los items del pedido
        $query_items = "CALL sp_getOrderItems(:id)";
        $stmt_items = $this->conn->prepare($query_items);
        $stmt_items->bindParam(':id', $id);
        $stmt_items->execute();

        $items = $stmt_items->fetchAll(PDO::FETCH_ASSOC);

        // 3. Combinar los resultados
        $order_details['items'] = $items;

        return $order_details;
    }

    /**
     * Crea un nuevo pedido en la base de datos.
     * @param int $id_mesa
     * @param int $id_usuario_mozo
     * @param array $items
     * @return PDOStatement|false
     */
    public function create($id_mesa, $id_usuario_mozo, $items) {
        $query = "CALL sp_createOrder(:id_mesa, :id_usuario_mozo, :items_json)";
        $stmt = $this->conn->prepare($query);

        // Convertir el array de items a JSON
        $items_json = json_encode($items);

        // Limpiar datos
        $id_mesa = htmlspecialchars(strip_tags($id_mesa));
        $id_usuario_mozo = htmlspecialchars(strip_tags($id_usuario_mozo));

        // Enlazar parámetros
        $stmt->bindParam(':id_mesa', $id_mesa);
        $stmt->bindParam(':id_usuario_mozo', $id_usuario_mozo);
        $stmt->bindParam(':items_json', $items_json);

        if ($stmt->execute()) {
            return $stmt;
        }
        return false;
    }

    /**
     * Actualiza el estado de un pedido.
     * @param int $id
     * @param string $status
     * @return bool
     */
    public function updateStatus($id, $status) {
        $query = "CALL sp_updateOrderStatus(:id, :status)";
        $stmt = $this->conn->prepare($query);

        $id = htmlspecialchars(strip_tags($id));
        $status = htmlspecialchars(strip_tags($status));

        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':status', $status);

        return $stmt->execute();
    }
}
?>
