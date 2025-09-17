<?php
require_once __DIR__ . '/../core/Database.php';

class User {
    private $conn;
    private $table_name = "usuarios";

    // Propiedades del objeto Usuario
    public $id;
    public $nombre_completo;
    public $email;
    public $password_hash;
    public $id_rol;
    public $activo;

    public function __construct() {
        $this->conn = Database::getInstance();
    }

    /**
     * Busca un usuario por su dirección de email.
     *
     * @param string $email El email del usuario a buscar.
     * @return bool True si el usuario existe, False si no.
     */
    public function findByEmail($email) {
        $query = "SELECT u.id, u.nombre_completo, u.email, u.password_hash, u.id_rol, r.nombre_rol
                  FROM " . $this->table_name . " u
                  JOIN roles r ON u.id_rol = r.id
                  WHERE u.email = :email AND u.activo = 1
                  LIMIT 0,1";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':email', $email);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            // Asignar valores a las propiedades del objeto
            $this->id = $row['id'];
            $this->nombre_completo = $row['nombre_completo'];
            $this->email = $row['email'];
            $this->password_hash = $row['password_hash'];
            $this->id_rol = $row['id_rol'];
            $this->nombre_rol = $row['nombre_rol']; // Propiedad añadida para conveniencia

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
