<?php
require_once __DIR__ . '/../core/Database.php';

class UnidadMedida {
    private $conn;

    public function __construct() {
        $this->conn = Database::getInstance();
    }

    public function readAll($filter, $limit, $offset) {
        $query = "CALL sp_read_unidades_medida(:filter, :limit, :offset)";
        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(':filter', $filter, PDO::PARAM_STR);
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);

        $stmt->execute();
        $records = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $stmt->closeCursor();

        return $records;
    }

    public function countAll($filter) {
        $query = "CALL sp_count_unidades_medida(:filter)";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':filter', $filter, PDO::PARAM_STR);

        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $stmt->closeCursor();

        return $result['total'] ?? 0;
    }

    public function readOne($id) {
        $query = "CALL sp_get_unidad_medida_by_id(:id)";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function create($codigo, $descripcion) {
        $query = "CALL sp_create_unidad_medida(:codigo, :descripcion)";
        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(':codigo', $codigo, PDO::PARAM_STR);
        $stmt->bindParam(':descripcion', $descripcion, PDO::PARAM_STR);

        if ($stmt->execute()) {
            return $stmt->fetch(PDO::FETCH_ASSOC);
        }
        return false;
    }

    public function update($id, $codigo, $descripcion, $estado) {
        $query = "CALL sp_update_unidad_medida(:id, :codigo, :descripcion, :estado)";
        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->bindParam(':codigo', $codigo, PDO::PARAM_STR);
        $stmt->bindParam(':descripcion', $descripcion, PDO::PARAM_STR);
        $stmt->bindParam(':estado', $estado, PDO::PARAM_INT);

        return $stmt->execute();
    }

    public function delete($id) {
        $query = "CALL sp_delete_unidad_medida(:id)";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        return $stmt->execute();
    }
}
?>