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

    /**
     * Busca un usuario por su nombre de usuario.
     *
     * @param string $username El nombre de usuario a buscar.
     * @return bool True si el usuario existe, False si no.
     */
    public function findByUsername($username) {
        $query = "CALL sp_getUserByUsername(:username)";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':username', $username);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            // Asignar valores a las propiedades del objeto
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

    /**
     * Verifica la contraseña del usuario.
     *
     * @param string $password La contraseña en texto plano.
     * @return bool True si la contraseña es correcta, False si no.
     */
    public function verifyPassword($password) {
        return password_verify($password, $this->password_hash);
    }
}
?>
