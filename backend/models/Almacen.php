<?php
require_once __DIR__ . '/../core/Database.php';

class Almacen {
    private $conn;

    public function __construct() {
        try {
            $this->conn = Database::getInstance();
        } catch (PDOException $e) {
            // En un caso real, podrías loguear el error o manejarlo de otra forma
            throw new Exception("Error al conectar a la base de datos: " . $e->getMessage());
        }
    }

    /**
     * Lee los almacenes con filtros y paginación.
     */
    public function readAll($nombre, $estado, $offset, $limit) {
        $query = "CALL sp_leer_almacenes(:nombre, :estado, :offset, :limit)";
        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(':nombre', $nombre, PDO::PARAM_STR);
        $stmt->bindParam(':estado', $estado, PDO::PARAM_BOOL);
        $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);

        $stmt->execute();
        $records = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $stmt->closeCursor();

        return $records;
    }

    /**
     * Cuenta el número total de almacenes con filtros.
     */
    public function countAll($nombre, $estado) {
        $query = "CALL sp_contar_almacenes(:nombre, :estado)";
        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(':nombre', $nombre, PDO::PARAM_STR);
        $stmt->bindParam(':estado', $estado, PDO::PARAM_BOOL);

        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $stmt->closeCursor();

        return $result['total'] ?? 0;
    }

    /**
     * Lee un único almacén por su ID.
     */
    public function readOne($id) {
        $query = "CALL sp_leer_almacen_por_id(:id)";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Crea un nuevo almacén y devuelve su ID.
     */
    public function create($nombre) {
        $query = "CALL sp_crear_almacen(:nombre)";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':nombre', $nombre, PDO::PARAM_STR);

        if ($stmt->execute()) {
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            $stmt->closeCursor();
            return $result ? $result['id'] : false;
        }
        return false;
    }

    /**
     * Actualiza un almacén existente.
     */
    public function update($id, $nombre, $estado) {
        $query = "CALL sp_actualizar_almacen(:id, :nombre, :estado)";
        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->bindParam(':nombre', $nombre, PDO::PARAM_STR);
        $stmt->bindParam(':estado', $estado, PDO::PARAM_BOOL);

        return $stmt->execute();
    }

    /**
     * Desactiva un almacén (eliminación lógica).
     */
    public function deactivate($id) {
        $query = "CALL sp_desactivar_almacen(:id)";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        return $stmt->execute();
    }
}
?>