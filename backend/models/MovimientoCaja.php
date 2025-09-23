<?php
require_once __DIR__ . '/../core/Database.php';

class MovimientoCaja {
    private $conn;
    private $table_name = "movimientos_caja";

    public function __construct() {
        $this->conn = Database::getInstance();
    }

    /**
     * Lee todos los movimientos de caja con filtros y paginación.
     */
    public function readAll($filters = [], $page = 1, $page_size = 10) {
        $query = "SELECT m.id, m.fecha, m.tipo_movimiento, m.importe, m.descripcion, m.usuario_id, u.nombre_completo as usuario_nombre
                  FROM " . $this->table_name . " m
                  LEFT JOIN usuarios u ON m.usuario_id = u.id";

        $where_clauses = [];
        $params = [];

        if (!empty($filters['fecha_inicio'])) {
            $where_clauses[] = "m.fecha >= :fecha_inicio";
            $params[':fecha_inicio'] = $filters['fecha_inicio'];
        }
        if (!empty($filters['fecha_fin'])) {
            // Se suma un día para incluir todos los movimientos del día final
            $fecha_fin = date('Y-m-d', strtotime($filters['fecha_fin'] . ' +1 day'));
            $where_clauses[] = "m.fecha < :fecha_fin";
            $params[':fecha_fin'] = $fecha_fin;
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
            if (is_int($val)) {
                $stmt->bindParam($key, $val, PDO::PARAM_INT);
            } else {
                $stmt->bindParam($key, $val, PDO::PARAM_STR);
            }
        }

        $stmt->execute();
        return $stmt;
    }

    /**
     * Cuenta el total de registros con los filtros aplicados.
     */
    public function countAll($filters = []) {
        $query = "SELECT COUNT(*) as total_records FROM " . $this->table_name;

        $where_clauses = [];
        $params = [];

        if (!empty($filters['fecha_inicio'])) {
            $where_clauses[] = "fecha >= :fecha_inicio";
            $params[':fecha_inicio'] = $filters['fecha_inicio'];
        }
        if (!empty($filters['fecha_fin'])) {
            $fecha_fin = date('Y-m-d', strtotime($filters['fecha_fin'] . ' +1 day'));
            $where_clauses[] = "fecha < :fecha_fin";
            $params[':fecha_fin'] = $fecha_fin;
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

    /**
     * Crea un nuevo movimiento de caja.
     */
    public function create($fecha, $tipo_movimiento, $importe, $descripcion, $usuario_id) {
        $query = "INSERT INTO " . $this->table_name . " (fecha, tipo_movimiento, importe, descripcion, usuario_id) VALUES (:fecha, :tipo_movimiento, :importe, :descripcion, :usuario_id)";
        $stmt = $this->conn->prepare($query);

        // Limpiar datos
        $fecha = htmlspecialchars(strip_tags($fecha));
        $tipo_movimiento = htmlspecialchars(strip_tags($tipo_movimiento));
        $importe = htmlspecialchars(strip_tags($importe));
        $descripcion = htmlspecialchars(strip_tags($descripcion));
        $usuario_id = htmlspecialchars(strip_tags($usuario_id));

        // Enlazar parámetros
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

    /**
     * Lee un solo movimiento por su ID.
     */
    public function readOne($id) {
        $query = "SELECT id, fecha, tipo_movimiento, importe, descripcion, usuario_id FROM " . $this->table_name . " WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Actualiza un movimiento de caja existente.
     */
    public function update($id, $fecha, $tipo_movimiento, $importe, $descripcion) {
        $query = "UPDATE " . $this->table_name . " SET fecha = :fecha, tipo_movimiento = :tipo_movimiento, importe = :importe, descripcion = :descripcion WHERE id = :id";
        $stmt = $this->conn->prepare($query);

        // Limpiar datos
        $id = htmlspecialchars(strip_tags($id));
        $fecha = htmlspecialchars(strip_tags($fecha));
        $tipo_movimiento = htmlspecialchars(strip_tags($tipo_movimiento));
        $importe = htmlspecialchars(strip_tags($importe));
        $descripcion = htmlspecialchars(strip_tags($descripcion));

        // Enlazar parámetros
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

    /**
     * Elimina un movimiento de caja.
     */
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
