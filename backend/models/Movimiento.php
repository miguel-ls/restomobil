<?php
require_once __DIR__ . '/../core/Database.php';

class Movimiento {
    private $conn;

    public function __construct() {
        $this->conn = Database::getInstance();
    }

    public function getAll($filter, $tipo_movimiento, $tipo_entidad, $limit, $offset) {
        $stmt = $this->conn->prepare("CALL sp_read_movimientos(:p_filter, :p_tipo_movimiento, :p_tipo_entidad, :p_limit, :p_offset)");
        $stmt->bindParam(':p_filter', $filter, PDO::PARAM_STR);
        $stmt->bindParam(':p_tipo_movimiento', $tipo_movimiento, PDO::PARAM_STR);
        $stmt->bindParam(':p_tipo_entidad', $tipo_entidad, PDO::PARAM_STR);
        $stmt->bindParam(':p_limit', $limit, PDO::PARAM_INT);
        $stmt->bindParam(':p_offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function count($filter, $tipo_movimiento, $tipo_entidad) {
        $stmt = $this->conn->prepare("CALL sp_count_movimientos(:p_filter, :p_tipo_movimiento, :p_tipo_entidad)");
        $stmt->bindParam(':p_filter', $filter, PDO::PARAM_STR);
        $stmt->bindParam(':p_tipo_movimiento', $tipo_movimiento, PDO::PARAM_STR);
        $stmt->bindParam(':p_tipo_entidad', $tipo_entidad, PDO::PARAM_STR);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $stmt->closeCursor();
        return $result['total'];
    }
}
?>