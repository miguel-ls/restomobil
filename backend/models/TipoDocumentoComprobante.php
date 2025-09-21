<?php
class TipoDocumentoComprobante {
    private $conn;
    private $table_name = "tipo_documento_comprobante";

    public $id;
    public $codigo_sunat;
    public $descripcion;
    public $estado;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Método para leer todos los tipos de documento
    function read($keywords = "") {
        $query = "SELECT id, codigo_sunat, descripcion, estado FROM " . $this->table_name;
        if (!empty($keywords)) {
            $keywords = htmlspecialchars(strip_tags($keywords));
            $query .= " WHERE descripcion LIKE :keywords OR codigo_sunat LIKE :keywords";
        }
        $query .= " ORDER BY descripcion ASC";
        $stmt = $this->conn->prepare($query);
        if (!empty($keywords)) {
            $search_term = "%{$keywords}%";
            $stmt->bindParam(":keywords", $search_term);
        }
        $stmt->execute();
        return $stmt;
    }

    // Método para crear un nuevo tipo de documento
    function create() {
        $query = "INSERT INTO " . $this->table_name . " SET codigo_sunat=:codigo_sunat, descripcion=:descripcion, estado=:estado";
        $stmt = $this->conn->prepare($query);
        $this->codigo_sunat = htmlspecialchars(strip_tags($this->codigo_sunat));
        $this->descripcion = htmlspecialchars(strip_tags($this->descripcion));
        $this->estado = isset($this->estado) ? $this->estado : 1;
        $stmt->bindParam(":codigo_sunat", $this->codigo_sunat);
        $stmt->bindParam(":descripcion", $this->descripcion);
        $stmt->bindParam(":estado", $this->estado);
        if ($stmt->execute()) {
            $this->id = $this->conn->lastInsertId();
            return true;
        }
        return false;
    }

    // Método para leer un solo tipo de documento
    function readOne() {
        $query = "SELECT id, codigo_sunat, descripcion, estado FROM " . $this->table_name . " WHERE id = ? LIMIT 0,1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->id);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if($row) {
            $this->id = $row['id'];
            $this->codigo_sunat = $row['codigo_sunat'];
            $this->descripcion = $row['descripcion'];
            $this->estado = $row['estado'];
            return true;
        }
        return false;
    }

    // Método para actualizar un tipo de documento
    function update() {
        $query = "UPDATE " . $this->table_name . " SET codigo_sunat = :codigo_sunat, descripcion = :descripcion, estado = :estado WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $this->id = htmlspecialchars(strip_tags($this->id));
        $this->codigo_sunat = htmlspecialchars(strip_tags($this->codigo_sunat));
        $this->descripcion = htmlspecialchars(strip_tags($this->descripcion));
        $this->estado = htmlspecialchars(strip_tags($this->estado));
        $stmt->bindParam(':id', $this->id);
        $stmt->bindParam(':codigo_sunat', $this->codigo_sunat);
        $stmt->bindParam(':descripcion', $this->descripcion);
        $stmt->bindParam(':estado', $this->estado);
        if ($stmt->execute()) {
            return true;
        }
        return false;
    }

    // Método para eliminar un tipo de documento
    function delete() {
        $query = "DELETE FROM " . $this->table_name . " WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $this->id = htmlspecialchars(strip_tags($this->id));
        $stmt->bindParam(1, $this->id);
        if ($stmt->execute()) {
            return true;
        }
        return false;
    }
}
?>
