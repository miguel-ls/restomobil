<?php
require_once __DIR__ . '/../core/Database.php';

class Reservation {
    private $conn;

    public function __construct() {
        $this->conn = Database::getInstance();
    }

    public function readAll() {
        $query = "CALL sp_getAllReservations()";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    public function readOne($id) {
        $query = "CALL sp_readOneReservation(:id)";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function create($id_mesa, $nombre_cliente, $telefono, $email, $fecha, $personas, $observaciones) {
        $query = "CALL sp_createReservation(:id_mesa, :nombre_cliente, :telefono, :email, :fecha, :personas, :observaciones)";
        $stmt = $this->conn->prepare($query);

        // Limpiar datos
        $id_mesa = htmlspecialchars(strip_tags($id_mesa));
        $nombre_cliente = htmlspecialchars(strip_tags($nombre_cliente));
        $telefono = htmlspecialchars(strip_tags($telefono));
        $email = htmlspecialchars(strip_tags($email));
        $fecha = htmlspecialchars(strip_tags($fecha));
        $personas = htmlspecialchars(strip_tags($personas));
        $observaciones = htmlspecialchars(strip_tags($observaciones));

        $stmt->bindParam(':id_mesa', $id_mesa);
        $stmt->bindParam(':nombre_cliente', $nombre_cliente);
        $stmt->bindParam(':telefono', $telefono);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':fecha', $fecha);
        $stmt->bindParam(':personas', $personas);
        $stmt->bindParam(':observaciones', $observaciones);

        if ($stmt->execute()) {
            return $stmt;
        }
        return false;
    }

    public function update($id, $id_mesa, $nombre_cliente, $telefono, $email, $fecha, $personas, $estado, $observaciones) {
        $query = "CALL sp_updateReservation(:id, :id_mesa, :nombre_cliente, :telefono, :email, :fecha, :personas, :estado, :observaciones)";
        $stmt = $this->conn->prepare($query);

        // Limpiar datos
        $id = htmlspecialchars(strip_tags($id));
        $id_mesa = htmlspecialchars(strip_tags($id_mesa));
        $nombre_cliente = htmlspecialchars(strip_tags($nombre_cliente));
        $telefono = htmlspecialchars(strip_tags($telefono));
        $email = htmlspecialchars(strip_tags($email));
        $fecha = htmlspecialchars(strip_tags($fecha));
        $personas = htmlspecialchars(strip_tags($personas));
        $estado = htmlspecialchars(strip_tags($estado));
        $observaciones = htmlspecialchars(strip_tags($observaciones));

        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':id_mesa', $id_mesa);
        $stmt->bindParam(':nombre_cliente', $nombre_cliente);
        $stmt->bindParam(':telefono', $telefono);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':fecha', $fecha);
        $stmt->bindParam(':personas', $personas);
        $stmt->bindParam(':estado', $estado);
        $stmt->bindParam(':observaciones', $observaciones);

        return $stmt->execute();
    }

    public function cancel($id) {
        $query = "CALL sp_cancelReservation(:id)";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }
}
?>
