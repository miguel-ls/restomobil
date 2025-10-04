<?php
require_once __DIR__ . '/../core/Database.php';

class TipoMovimiento {
    private $conn;
    private $table_name = "tipo_movimiento";

    public function __construct() {
        $db = Database::getInstance();
        $this->conn = $db;
    }

    public function readAll($filters = []) {
        $query = "CALL sp_getAllTiposMovimiento(:descripcion, :estado)";
        $stmt = $this->conn->prepare($query);

        $descripcion = $filters['descripcion'] ?? null;
        $estado = $filters['estado'] ?? null;

        $stmt->bindValue(':descripcion', $descripcion, $descripcion === null ? PDO::PARAM_NULL : PDO::PARAM_STR);
        $stmt->bindValue(':estado', $estado, $estado === null ? PDO::PARAM_NULL : PDO::PARAM_STR);

        $stmt->execute();
        return $stmt;
    }

    public function readOne($id) {
        $query = "CALL sp_getOneTipoMovimiento(:id)";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function create($tipo, $codigo, $descripcion) {
        $query = "CALL sp_createTipoMovimiento(:tipo, :codigo, :descripcion)";
        $stmt = $this->conn->prepare($query);

        $tipo = htmlspecialchars(strip_tags($tipo));
        $codigo = htmlspecialchars(strip_tags($codigo));
        $descripcion = htmlspecialchars(strip_tags($descripcion));

        $stmt->bindParam(':tipo', $tipo);
        $stmt->bindParam(':codigo', $codigo);
        $stmt->bindParam(':descripcion', $descripcion);

        if ($stmt->execute()) {
            return $stmt;
        }
        return false;
    }

    public function update($id, $tipo, $codigo, $descripcion, $estado) {
        $query = "CALL sp_updateTipoMovimiento(:id, :tipo, :codigo, :descripcion, :estado)";
        $stmt = $this->conn->prepare($query);

        $id = htmlspecialchars(strip_tags($id));
        $tipo = htmlspecialchars(strip_tags($tipo));
        $codigo = htmlspecialchars(strip_tags($codigo));
        $descripcion = htmlspecialchars(strip_tags($descripcion));
        $estado = htmlspecialchars(strip_tags($estado));

        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':tipo', $tipo);
        $stmt->bindParam(':codigo', $codigo);
        $stmt->bindParam(':descripcion', $descripcion);
        $stmt->bindParam(':estado', $estado);

        if ($stmt->execute()) {
            return true;
        }
        return false;
    }

    public function delete($id) {
        $query = "CALL sp_deleteTipoMovimiento(:id)";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);

        if ($stmt->execute()) {
            return true;
        }
        return false;
    }
}
?>