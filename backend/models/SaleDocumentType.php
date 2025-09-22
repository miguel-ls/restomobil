<?php
require_once __DIR__ . '/../core/Database.php';

class SaleDocumentType {
    private $conn;

    public function __construct() {
        $this->conn = Database::getInstance();
    }

    public function readAll() {
        $query = "CALL sp_getAllTipoDocumentoVenta()";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    public function readOne($id) {
        $query = "CALL sp_getOneSaleDocumentType(:id)";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function create($codigo, $nombre, $descripcion) {
        $query = "CALL sp_createSaleDocumentType(:codigo, :nombre, :descripcion)";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':codigo', $codigo);
        $stmt->bindParam(':nombre', $nombre);
        $stmt->bindParam(':descripcion', $descripcion);
        if ($stmt->execute()) {
            return $stmt;
        }
        return false;
    }

    public function update($id, $codigo, $nombre, $descripcion, $estado) {
        $query = "CALL sp_updateSaleDocumentType(:id, :codigo, :nombre, :descripcion, :estado)";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':codigo', $codigo);
        $stmt->bindParam(':nombre', $nombre);
        $stmt->bindParam(':descripcion', $descripcion);
        $stmt->bindParam(':estado', $estado, PDO::PARAM_BOOL);
        return $stmt->execute();
    }

    public function delete($id) {
        $query = "CALL sp_deleteSaleDocumentType(:id)";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }
}
?>
