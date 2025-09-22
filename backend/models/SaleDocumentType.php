<?php
require_once __DIR__ . '/../core/Database.php';

class SaleDocumentType {
    private $conn;

    public function __construct() {
        $this->conn = Database::getInstance();
    }

    public function readAll() {
        $query = "CALL sp_getAllTiposComprobante()";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    public function readOne($id) {
        $query = "CALL sp_getOneTipoComprobante(:id)";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function create($codigo, $nombre) {
        $query = "CALL sp_createTipoComprobante(:codigo, :nombre)";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':codigo', $codigo);
        $stmt->bindParam(':nombre', $nombre);
        if ($stmt->execute()) {
            return $stmt;
        }
        return false;
    }

    public function update($id, $codigo, $nombre, $estado) {
        $query = "CALL sp_updateTipoComprobante(:id, :codigo, :nombre, :estado)";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':codigo', $codigo);
        $stmt->bindParam(':nombre', $nombre);
        $stmt->bindParam(':estado', $estado, PDO::PARAM_BOOL);
        return $stmt->execute();
    }

    public function delete($id) {
        $query = "CALL sp_deleteTipoComprobante(:id)";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }
}
?>
