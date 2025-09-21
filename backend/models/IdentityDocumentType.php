<?php
require_once __DIR__ . '/../core/Database.php';

class IdentityDocumentType {
    private $conn;

    public function __construct() {
        $this->conn = Database::getInstance();
    }

    public function readAll() {
        $query = "CALL sp_getAllIdentityDocumentTypes()";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    public function readOne($id) {
        $query = "CALL sp_getOneIdentityDocumentType(:id)";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function create($codigo, $nombre, $descripcion) {
        $query = "CALL sp_createIdentityDocumentType(:codigo, :nombre, :descripcion)";
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
        $query = "CALL sp_updateIdentityDocumentType(:id, :codigo, :nombre, :descripcion, :estado)";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':codigo', $codigo);
        $stmt->bindParam(':nombre', $nombre);
        $stmt->bindParam(':descripcion', $descripcion);
        $stmt->bindParam(':estado', $estado, PDO::PARAM_BOOL);
        return $stmt->execute();
    }

    public function delete($id) {
        $query = "CALL sp_deleteIdentityDocumentType(:id)";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }
}
?>
