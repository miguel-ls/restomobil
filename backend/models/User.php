<?php
require_once __DIR__ . '/../core/Database.php';

class User {
    private $conn;
    private $table_name = "usuarios";

    // Propiedades del objeto Usuario
    public $id;
    public $username;
    public $nombre_completo;
    public $email;
    public $password_hash;
    public $id_rol;
    public $nombre_rol;
    public $activo;

    public function __construct() {
        $this->conn = Database::getInstance();
    }

    // --- Métodos de Autenticación ---

    public function findByUsername($username) {
        $query = "CALL sp_getUserByUsername(:username)";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':username', $username);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $this->id = $row['id'];
            $this->username = $row['username'];
            $this->nombre_completo = $row['nombre_completo'];
            $this->email = $row['email'];
            $this->password_hash = $row['password_hash'];
            $this->id_rol = $row['id_rol'];
            $this->nombre_rol = $row['nombre_rol'];
            return true;
        }
        return false;
    }

    public function verifyPassword($password) {
        return password_verify($password, $this->password_hash);
    }

    // --- Métodos CRUD ---

    public function readAll() {
        $query = "CALL sp_getAllUsers()";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    public function readOne($id) {
        $query = "CALL sp_readOneUser(:id)";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function create($username, $nombre_completo, $email, $password, $id_rol) {
        $password_hash = password_hash($password, PASSWORD_BCRYPT);
        $query = "CALL sp_createUser(:username, :nombre_completo, :email, :password_hash, :id_rol)";
        $stmt = $this->conn->prepare($query);

        // Limpieza de datos
        $username = htmlspecialchars(strip_tags($username));
        $nombre_completo = htmlspecialchars(strip_tags($nombre_completo));
        $email = htmlspecialchars(strip_tags($email));
        $id_rol = htmlspecialchars(strip_tags($id_rol));

        $stmt->bindParam(':username', $username);
        $stmt->bindParam(':nombre_completo', $nombre_completo);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':password_hash', $password_hash);
        $stmt->bindParam(':id_rol', $id_rol);

        if ($stmt->execute()) {
            return $stmt;
        }
        return false;
    }

    public function update($id, $nombre_completo, $email, $id_rol, $activo, $password) {
        $password_hash = null;
        if (!empty($password)) {
            $password_hash = password_hash($password, PASSWORD_BCRYPT);
        }

        $query = "CALL sp_updateUser(:id, :nombre_completo, :email, :id_rol, :activo, :password_hash)";
        $stmt = $this->conn->prepare($query);

        // Limpieza de datos
        $id = htmlspecialchars(strip_tags($id));
        $nombre_completo = htmlspecialchars(strip_tags($nombre_completo));
        $email = htmlspecialchars(strip_tags($email));
        $id_rol = htmlspecialchars(strip_tags($id_rol));
        $activo = htmlspecialchars(strip_tags($activo));

        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':nombre_completo', $nombre_completo);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':id_rol', $id_rol);
        $stmt->bindParam(':activo', $activo, PDO::PARAM_BOOL);
        $stmt->bindParam(':password_hash', $password_hash);

        return $stmt->execute();
    }

    public function delete($id) {
        $query = "CALL sp_deleteUser(:id)";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }

    // --- Métodos Auxiliares ---

    public function getAllRoles() {
        $query = "CALL sp_getAllRoles()";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }
}
?>
