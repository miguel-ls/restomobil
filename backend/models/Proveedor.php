<?php
require_once __DIR__ . '/../core/Database.php';

class Proveedor {
    private $conn;

    public function __construct() {
        $this->conn = Database::getInstance();
    }

    public function readAll() {
        $query = "CALL sp_getAllProveedores()";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    public function readOne($id) {
        $query = "CALL sp_getOneProveedor(:id)";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function create($id_tipo_documento_identidad, $numero_documento, $nombres_apellidos, $direccion, $codigo_ubigeo, $email, $telefono) {
        $query = "CALL sp_createProveedor(:id_tipo_documento_identidad, :numero_documento, :nombres_apellidos, :direccion, :codigo_ubigeo, :email, :telefono)";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id_tipo_documento_identidad', $id_tipo_documento_identidad);
        $stmt->bindParam(':numero_documento', $numero_documento);
        $stmt->bindParam(':nombres_apellidos', $nombres_apellidos);
        $stmt->bindParam(':direccion', $direccion);
        $stmt->bindParam(':codigo_ubigeo', $codigo_ubigeo);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':telefono', $telefono);
        if ($stmt->execute()) {
            return $stmt;
        }
        return false;
    }

    public function update($id, $id_tipo_documento_identidad, $numero_documento, $nombres_apellidos, $direccion, $codigo_ubigeo, $email, $telefono, $estado) {
        $query = "CALL sp_updateProveedor(:id, :id_tipo_documento_identidad, :numero_documento, :nombres_apellidos, :direccion, :codigo_ubigeo, :email, :telefono, :estado)";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':id_tipo_documento_identidad', $id_tipo_documento_identidad);
        $stmt->bindParam(':numero_documento', $numero_documento);
        $stmt->bindParam(':nombres_apellidos', $nombres_apellidos);
        $stmt->bindParam(':direccion', $direccion);
        $stmt->bindParam(':codigo_ubigeo', $codigo_ubigeo);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':telefono', $telefono);
        $stmt->bindParam(':estado', $estado);
        return $stmt->execute();
    }

    public function delete($id) {
        $query = "CALL sp_deleteProveedor(:id)";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }
}
?>