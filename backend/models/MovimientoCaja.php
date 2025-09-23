<?php
require_once __DIR__ . '/../core/Database.php';

class MovimientoCaja {
    private $conn;
    private $table_name = "movimientos_caja";

    public function __construct() {
        $this->conn = Database::getInstance();
    }

    public function readAll($filters = [], $page = 1, $page_size = 10) {
        $query = "SELECT m.id, m.fecha, m.tipo_movimiento, m.importe, m.descripcion, m.usuario_id, u.nombre_completo as usuario_nombre
                  FROM " . $this->table_name . " m
                  LEFT JOIN usuarios u ON m.usuario_id = u.id";
        $where_clauses = [];
        $params = [];
        if (!empty($filters['fecha_inicio'])) {
            $where_clauses[] = "DATE(m.fecha) >= :fecha_inicio";
            $params[':fecha_inicio'] = $filters['fecha_inicio'];
        }
        if (!empty($filters['fecha_fin'])) {
            $where_clauses[] = "DATE(m.fecha) <= :fecha_fin";
            $params[':fecha_fin'] = $filters['fecha_fin'];
        }
        if (!empty($filters['tipo_movimiento'])) {
            $where_clauses[] = "m.tipo_movimiento = :tipo_movimiento";
            $params[':tipo_movimiento'] = $filters['tipo_movimiento'];
        }
        if (count($where_clauses) > 0) {
            $query .= " WHERE " . implode(' AND ', $where_clauses);
        }
        $query .= " ORDER BY m.fecha DESC";
        if ($page_size > 0) {
            $offset = ($page - 1) * $page_size;
            $query .= " LIMIT :offset, :page_size";
            $params[':offset'] = $offset;
            $params[':page_size'] = $page_size;
        }
        $stmt = $this->conn->prepare($query);
        foreach ($params as $key => &$val) {
             $stmt->bindParam($key, $val, is_int($val) ? PDO::PARAM_INT : PDO::PARAM_STR);
        }
        $stmt->execute();
        return $stmt;
    }

    public function countAll($filters = []) {
        $query = "SELECT COUNT(*) as total_records FROM " . $this->table_name;
        $where_clauses = [];
        $params = [];
        if (!empty($filters['fecha_inicio'])) {
            $where_clauses[] = "DATE(fecha) >= :fecha_inicio";
            $params[':fecha_inicio'] = $filters['fecha_inicio'];
        }
        if (!empty($filters['fecha_fin'])) {
            $where_clauses[] = "DATE(fecha) <= :fecha_fin";
            $params[':fecha_fin'] = $filters['fecha_fin'];
        }
        if (!empty($filters['tipo_movimiento'])) {
            $where_clauses[] = "tipo_movimiento = :tipo_movimiento";
            $params[':tipo_movimiento'] = $filters['tipo_movimiento'];
        }
        if (count($where_clauses) > 0) {
            $query .= " WHERE " . implode(' AND ', $where_clauses);
        }
        $stmt = $this->conn->prepare($query);
        foreach ($params as $key => &$val) {
            $stmt->bindParam($key, $val, PDO::PARAM_STR);
        }
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row['total_records'];
    }

    public function create($fecha, $tipo_movimiento, $importe, $descripcion, $usuario_id) {
        $query = "INSERT INTO " . $this->table_name . " (fecha, tipo_movimiento, importe, descripcion, usuario_id) VALUES (:fecha, :tipo_movimiento, :importe, :descripcion, :usuario_id)";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':fecha', $fecha);
        $stmt->bindParam(':tipo_movimiento', $tipo_movimiento);
        $stmt->bindParam(':importe', $importe);
        $stmt->bindParam(':descripcion', $descripcion);
        $stmt->bindParam(':usuario_id', $usuario_id);
        if ($stmt->execute()) {
            return $this->conn->lastInsertId();
        }
        return false;
    }

    public function readOne($id) {
        $query = "SELECT id, fecha, tipo_movimiento, importe, descripcion, usuario_id FROM " . $this->table_name . " WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function update($id, $fecha, $tipo_movimiento, $importe, $descripcion) {
        $query = "UPDATE " . $this->table_name . " SET fecha = :fecha, tipo_movimiento = :tipo_movimiento, importe = :importe, descripcion = :descripcion WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':fecha', $fecha);
        $stmt->bindParam(':tipo_movimiento', $tipo_movimiento);
        $stmt->bindParam(':importe', $importe);
        $stmt->bindParam(':descripcion', $descripcion);
        if ($stmt->execute()) {
            return true;
        }
        return false;
    }

    public function delete($id) {
        $query = "DELETE FROM " . $this->table_name . " WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        if ($stmt->execute()) {
            return true;
        }
        return false;
    }
}
?>
