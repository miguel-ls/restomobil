<?php
require_once __DIR__ . '/../core/Database.php';

class Table {
    private $conn;
    private $table_name = "mesas";

    public function __construct() {
        $this->conn = Database::getInstance();
    }

    public function readAll() {
        $query = "CALL sp_getAllTables()";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    public function readOne($id) {
        $query = "CALL sp_readOneTable(:id)";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function create($numero_mesa, $capacidad, $estado) {
        $query = "CALL sp_createTable(:numero_mesa, :capacidad, :estado)";
        $stmt = $this->conn->prepare($query);

        $numero_mesa = htmlspecialchars(strip_tags($numero_mesa));
        $capacidad = htmlspecialchars(strip_tags($capacidad));
        $estado = htmlspecialchars(strip_tags($estado));

        $stmt->bindParam(':numero_mesa', $numero_mesa);
        $stmt->bindParam(':capacidad', $capacidad);
        $stmt->bindParam(':estado', $estado);

        if ($stmt->execute()) {
            return $stmt;
        }
        return false;
    }

    public function update($id, $numero_mesa, $capacidad, $estado) {
        $query = "CALL sp_updateTable(:id, :numero_mesa, :capacidad, :estado)";
        $stmt = $this->conn->prepare($query);

        $id = htmlspecialchars(strip_tags($id));
        $numero_mesa = htmlspecialchars(strip_tags($numero_mesa));
        $capacidad = htmlspecialchars(strip_tags($capacidad));
        $estado = htmlspecialchars(strip_tags($estado));

        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':numero_mesa', $numero_mesa);
        $stmt->bindParam(':capacidad', $capacidad);
        $stmt->bindParam(':estado', $estado);

        return $stmt->execute();
    }

    public function delete($id) {
        $query = "CALL sp_deleteTable(:id)";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }
}
?>
