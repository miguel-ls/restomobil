<?php
require_once __DIR__ . '/../core/Database.php';

class Category {
    private $conn;

    public function __construct() {
        $this->conn = Database::getInstance();
    }

    public function readAll() {
        $query = "CALL sp_getAllCategories()";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    public function readOne($id) {
        $query = "CALL sp_readOneCategory(:id)";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function create($nombre, $descripcion, $tipo_categoria) {
        $query = "CALL sp_createCategory(:nombre, :descripcion, :tipo_categoria)";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':nombre', $nombre);
        $stmt->bindParam(':descripcion', $descripcion);
        $stmt->bindParam(':tipo_categoria', $tipo_categoria);
        if ($stmt->execute()) {
            return $stmt;
        }
        return false;
    }

    public function update($id, $nombre, $descripcion, $tipo_categoria, $estado) {
        $query = "CALL sp_updateCategory(:id, :nombre, :descripcion, :tipo_categoria, :estado)";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':nombre', $nombre);
        $stmt->bindParam(':descripcion', $descripcion);
        $stmt->bindParam(':tipo_categoria', $tipo_categoria);
        $stmt->bindParam(':estado', $estado, PDO::PARAM_BOOL);
        return $stmt->execute();
    }

    public function delete($id) {
        $query = "CALL sp_deleteCategory(:id)";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }
}
?>
