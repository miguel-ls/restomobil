<?php
require_once __DIR__ . '/../core/Database.php';

class Order {
    private $conn;

    public function __construct() {
        $this->conn = Database::getInstance();
    }

    public function readAll() {
        $query = "CALL sp_getAllOrders()";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    public function readByStatus($status) {
        $query = "CALL sp_getOrdersByStatus(:status)";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':status', $status);
        $stmt->execute();
        return $stmt;
    }

    public function readOne($id) {
        // 1. Obtener la cabecera del pedido
        $query_header = "CALL sp_getOrderDetail(:id)";
        $stmt_header = $this->conn->prepare($query_header);
        $stmt_header->bindParam(':id', $id);
        $stmt_header->execute();

        $order_details = $stmt_header->fetch(PDO::FETCH_ASSOC);

        if (!$order_details) {
            return false;
        }

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

    public function create($id_mesa, $id_usuario_mozo, $items, $estado = 'abierto', $id_cliente = null, $id_tipo_documento_venta = null) {
        $query = "CALL sp_createOrder(:id_mesa, :id_usuario_mozo, :items_json, :estado, :id_cliente, :id_tipo_documento_venta)";
        $stmt = $this->conn->prepare($query);
        $items_json = json_encode($items);
        $stmt->bindParam(':id_mesa', $id_mesa);
        $stmt->bindParam(':id_usuario_mozo', $id_usuario_mozo);
        $stmt->bindParam(':items_json', $items_json);
        $stmt->bindParam(':estado', $estado);
        $stmt->bindParam(':id_cliente', $id_cliente);
        $stmt->bindParam(':id_tipo_documento_venta', $id_tipo_documento_venta);
        if ($stmt->execute()) {
            return $stmt;
        }
        return false;
    }

    public function update($id, $id_mesa, $id_usuario_mozo, $status, $items, $id_cliente = null, $id_tipo_documento_venta = null) {
        $query = "CALL sp_updateOrder(:id, :id_mesa, :id_usuario_mozo, :status, :items_json, :id_cliente, :id_tipo_documento_venta)";
        $stmt = $this->conn->prepare($query);
        $items_json = json_encode($items);
        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':id_mesa', $id_mesa);
        $stmt->bindParam(':id_usuario_mozo', $id_usuario_mozo);
        $stmt->bindParam(':status', $status);
        $stmt->bindParam(':items_json', $items_json);
        $stmt->bindParam(':id_cliente', $id_cliente);
        $stmt->bindParam(':id_tipo_documento_venta', $id_tipo_documento_venta);
        return $stmt->execute();
    }
}
?>
