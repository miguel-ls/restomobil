<?php
require_once __DIR__ . '/../core/Database.php';

class Empresa {
    private $conn;

    public function __construct() {
        $this->conn = Database::getInstance();
    }

    public function readAll() {
        $query = "CALL sp_getAllEmpresas()";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    public function readOne($id) {
        $query = "CALL sp_getOneEmpresa(:id)";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function create(
        $nombre_largo, $nombre_corto, $ruc, $direccion, $id_departamento, $id_provincia, $id_distrito,
        $telefonos, $email, $web, $logo_url, $observaciones, $sunat_envio_estado, $sunat_api_url, $sunat_api_key
    ) {
        $query = "CALL sp_createEmpresa(:nombre_largo, :nombre_corto, :ruc, :direccion, :id_departamento, :id_provincia, :id_distrito, :telefonos, :email, :web, :logo_url, :observaciones, :sunat_envio_estado, :sunat_api_url, :sunat_api_key)";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':nombre_largo', $nombre_largo);
        $stmt->bindParam(':nombre_corto', $nombre_corto);
        $stmt->bindParam(':ruc', $ruc);
        $stmt->bindParam(':direccion', $direccion);
        $stmt->bindParam(':id_departamento', $id_departamento);
        $stmt->bindParam(':id_provincia', $id_provincia);
        $stmt->bindParam(':id_distrito', $id_distrito);
        $stmt->bindParam(':telefonos', $telefonos);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':web', $web);
        $stmt->bindParam(':logo_url', $logo_url);
        $stmt->bindParam(':observaciones', $observaciones);
        $stmt->bindParam(':sunat_envio_estado', $sunat_envio_estado, PDO::PARAM_BOOL);
        $stmt->bindParam(':sunat_api_url', $sunat_api_url);
        $stmt->bindParam(':sunat_api_key', $sunat_api_key);

        if ($stmt->execute()) {
            return $stmt;
        }
        return false;
    }

    public function update(
        $id, $nombre_largo, $nombre_corto, $ruc, $direccion, $id_departamento, $id_provincia, $id_distrito,
        $telefonos, $email, $web, $logo_url, $observaciones, $estado, $sunat_envio_estado, $sunat_api_url, $sunat_api_key
    ) {
        $query = "CALL sp_updateEmpresa(:id, :nombre_largo, :nombre_corto, :ruc, :direccion, :id_departamento, :id_provincia, :id_distrito, :telefonos, :email, :web, :logo_url, :observaciones, :estado, :sunat_envio_estado, :sunat_api_url, :sunat_api_key)";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':nombre_largo', $nombre_largo);
        $stmt->bindParam(':nombre_corto', $nombre_corto);
        $stmt->bindParam(':ruc', $ruc);
        $stmt->bindParam(':direccion', $direccion);
        $stmt->bindParam(':id_departamento', $id_departamento);
        $stmt->bindParam(':id_provincia', $id_provincia);
        $stmt->bindParam(':id_distrito', $id_distrito);
        $stmt->bindParam(':telefonos', $telefonos);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':web', $web);
        $stmt->bindParam(':logo_url', $logo_url);
        $stmt->bindParam(':observaciones', $observaciones);
        $stmt->bindParam(':estado', $estado, PDO::PARAM_BOOL);
        $stmt->bindParam(':sunat_envio_estado', $sunat_envio_estado, PDO::PARAM_BOOL);
        $stmt->bindParam(':sunat_api_url', $sunat_api_url);
        $stmt->bindParam(':sunat_api_key', $sunat_api_key);

        return $stmt->execute();
    }

    public function delete($id) {
        $query = "CALL sp_deleteEmpresa(:id)";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }

    public function getDepartamentos() {
        $query = "CALL sp_getDepartamentos()";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    public function getProvincias($id_departamento) {
        $query = "CALL sp_getProvincias(:id_departamento)";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id_departamento', $id_departamento);
        $stmt->execute();
        return $stmt;
    }

    public function getDistritos($id_provincia) {
        $query = "CALL sp_getDistritos(:id_provincia)";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id_provincia', $id_provincia);
        $stmt->execute();
        return $stmt;
    }
}
?>
