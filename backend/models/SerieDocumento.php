<?php
require_once __DIR__ . '/../core/Database.php';

class SerieDocumento {
    private $conn;

    public function __construct() {
        $this->conn = Database::getInstance();
    }

    public function readAll() {
        $query = "CALL sp_getAllSeries()";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    public function readByTipoDocumento($id_tipo_documento) {
        // Usamos una consulta directa para evitar modificar el SP existente
        $query = "SELECT id, serie FROM series_documentos WHERE id_tipo_documento = :id_tipo_documento AND estado = 1 ORDER BY serie ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id_tipo_documento', $id_tipo_documento, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt;
    }

    public function readOne($id) {
        $query = "CALL sp_getOneSerie(:id)";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function create($id_tipo_documento, $serie) {
        $query = "CALL sp_createSerie(:id_tipo_documento, :serie)";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id_tipo_documento', $id_tipo_documento);
        $stmt->bindParam(':serie', $serie);
        if ($stmt->execute()) {
            return $stmt;
        }
        return false;
    }

    public function update($id, $id_tipo_documento, $serie, $estado) {
        $query = "CALL sp_updateSerie(:id, :id_tipo_documento, :serie, :estado)";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':id_tipo_documento', $id_tipo_documento);
        $stmt->bindParam(':serie', $serie);
        $stmt->bindParam(':estado', $estado, PDO::PARAM_BOOL);
        return $stmt->execute();
    }

    public function delete($id) {
        $query = "CALL sp_deleteSerie(:id)";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }
}
?>
