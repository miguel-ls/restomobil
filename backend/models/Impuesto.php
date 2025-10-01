<?php
require_once __DIR__ . '/../core/Database.php';

class Impuesto {
    private $conn;

    public function __construct() {
        $this->conn = Database::getInstance();
    }

    /**
     * Lee los impuestos con filtros y paginación.
     */
    public function readAll($codigo, $estado, $offset, $limit) {
        $query = "CALL sp_leer_impuestos(:codigo, :estado, :offset, :limit)";
        $stmt = $this->conn->prepare($query);

        // Bind de parámetros
        $stmt->bindParam(':codigo', $codigo, PDO::PARAM_STR);
        $stmt->bindParam(':estado', $estado, PDO::PARAM_BOOL);
        $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);

        $stmt->execute();
        return $stmt;
    }

    /**
     * Cuenta el número total de impuestos con filtros.
     */
    public function countAll($codigo, $estado) {
        $query = "CALL sp_contar_impuestos(:codigo, :estado, @p_total)";
        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(':codigo', $codigo, PDO::PARAM_STR);
        $stmt->bindParam(':estado', $estado, PDO::PARAM_BOOL);

        $stmt->execute();

        // Recuperar el valor del parámetro de salida
        $stmt_total = $this->conn->query("SELECT @p_total AS total");
        $total_row = $stmt_total->fetch(PDO::FETCH_ASSOC);
        return $total_row['total'];
    }

    /**
     * Lee un único impuesto por su ID.
     */
    public function readOne($id) {
        $query = "CALL sp_leer_impuesto_por_id(:id)";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Crea un nuevo impuesto.
     */
    public function create($codigo, $fecha_inicial, $fecha_final, $valor, $estado) {
        $query = "CALL sp_crear_impuesto(:codigo, :fecha_inicial, :fecha_final, :valor, :estado)";
        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(':codigo', $codigo);
        $stmt->bindParam(':fecha_inicial', $fecha_inicial);
        $stmt->bindParam(':fecha_final', $fecha_final);
        $stmt->bindParam(':valor', $valor);
        $stmt->bindParam(':estado', $estado, PDO::PARAM_BOOL);

        return $stmt->execute();
    }

    /**
     * Actualiza un impuesto existente.
     */
    public function update($id, $codigo, $fecha_inicial, $fecha_final, $valor, $estado) {
        $query = "CALL sp_actualizar_impuesto(:id, :codigo, :fecha_inicial, :fecha_final, :valor, :estado)";
        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->bindParam(':codigo', $codigo);
        $stmt->bindParam(':fecha_inicial', $fecha_inicial);
        $stmt->bindParam(':fecha_final', $fecha_final);
        $stmt->bindParam(':valor', $valor);
        $stmt->bindParam(':estado', $estado, PDO::PARAM_BOOL);

        return $stmt->execute();
    }

    /**
     * Elimina un impuesto por su ID.
     */
    public function delete($id) {
        $query = "CALL sp_eliminar_impuesto(:id)";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        return $stmt->execute();
    }

    /**
     * Obtiene los códigos únicos de impuestos para los combos de filtro.
     */
    public function readCodigos() {
        $query = "CALL sp_leer_codigos_impuesto()";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }
}
?>