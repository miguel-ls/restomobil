<?php
class Reservation {
    private $conn;
    private $table_name = "reservas";

    public $id;
    public $id_mesa;
    public $nombre_cliente;
    public $telefono_cliente;
    public $email_cliente;
    public $fecha_reserva;
    public $cantidad_personas;
    public $estado;
    public $observaciones;

    public function __construct() {
        $this->conn = Database::getInstance();
    }

    function readAll() {
        $query = "SELECT
                    r.id,
                    r.id_mesa,
                    m.numero_mesa,
                    r.nombre_cliente,
                    r.telefono_cliente,
                    r.email_cliente,
                    r.fecha_reserva,
                    r.cantidad_personas,
                    r.estado,
                    r.observaciones
                FROM
                    " . $this->table_name . " r
                    LEFT JOIN
                        mesas m ON r.id_mesa = m.id
                ORDER BY
                    r.fecha_reserva DESC";

        $stmt = $this->conn->prepare($query);
        $stmt->execute();

        return $stmt;
    }

    function create() {
        $query = "INSERT INTO " . $this->table_name . " (id_mesa, nombre_cliente, telefono_cliente, email_cliente, fecha_reserva, cantidad_personas, estado, observaciones) VALUES (:id_mesa, :nombre_cliente, :telefono_cliente, :email_cliente, :fecha_reserva, :cantidad_personas, :estado, :observaciones)";
        $stmt = $this->conn->prepare($query);

        // Sanitize
        $this->id_mesa=htmlspecialchars(strip_tags($this->id_mesa));
        $this->nombre_cliente=htmlspecialchars(strip_tags($this->nombre_cliente));
        $this->telefono_cliente=htmlspecialchars(strip_tags($this->telefono_cliente));
        $this->email_cliente=htmlspecialchars(strip_tags($this->email_cliente));
        $this->fecha_reserva=htmlspecialchars(strip_tags($this->fecha_reserva));
        $this->cantidad_personas=htmlspecialchars(strip_tags($this->cantidad_personas));
        $this->estado=htmlspecialchars(strip_tags($this->estado));
        $this->observaciones=htmlspecialchars(strip_tags($this->observaciones));

        // Bind values
        $stmt->bindParam(":id_mesa", $this->id_mesa);
        $stmt->bindParam(":nombre_cliente", $this->nombre_cliente);
        $stmt->bindParam(":telefono_cliente", $this->telefono_cliente);
        $stmt->bindParam(":email_cliente", $this->email_cliente);
        $stmt->bindParam(":fecha_reserva", $this->fecha_reserva);
        $stmt->bindParam(":cantidad_personas", $this->cantidad_personas);
        $stmt->bindParam(":estado", $this->estado);
        $stmt->bindParam(":observaciones", $this->observaciones);

        if($stmt->execute()) {
            return true;
        }
        // Log error if execution fails
        error_log("Database error on create: " . implode(" - ", $stmt->errorInfo()));
        return false;
    }

    function readOne() {
        $query = "SELECT r.*, m.numero_mesa FROM " . $this->table_name . " r LEFT JOIN mesas m ON r.id_mesa = m.id WHERE r.id = ? LIMIT 0,1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->id);
        $stmt->execute();
        return $stmt;
    }

    function update() {
        $query = "UPDATE " . $this->table_name . " SET id_mesa = :id_mesa, nombre_cliente = :nombre_cliente, telefono_cliente = :telefono_cliente, email_cliente = :email_cliente, fecha_reserva = :fecha_reserva, cantidad_personas = :cantidad_personas, estado = :estado, observaciones = :observaciones WHERE id = :id";
        $stmt = $this->conn->prepare($query);

        // Sanitize
        $this->id=htmlspecialchars(strip_tags($this->id));
        $this->id_mesa=htmlspecialchars(strip_tags($this->id_mesa));
        $this->nombre_cliente=htmlspecialchars(strip_tags($this->nombre_cliente));
        $this->telefono_cliente=htmlspecialchars(strip_tags($this->telefono_cliente));
        $this->email_cliente=htmlspecialchars(strip_tags($this->email_cliente));
        $this->fecha_reserva=htmlspecialchars(strip_tags($this->fecha_reserva));
        $this->cantidad_personas=htmlspecialchars(strip_tags($this->cantidad_personas));
        $this->estado=htmlspecialchars(strip_tags($this->estado));
        $this->observaciones=htmlspecialchars(strip_tags($this->observaciones));

        // Bind values
        $stmt->bindParam(':id', $this->id);
        $stmt->bindParam(":id_mesa", $this->id_mesa);
        $stmt->bindParam(":nombre_cliente", $this->nombre_cliente);
        $stmt->bindParam(":telefono_cliente", $this->telefono_cliente);
        $stmt->bindParam(":email_cliente", $this->email_cliente);
        $stmt->bindParam(":fecha_reserva", $this->fecha_reserva);
        $stmt->bindParam(":cantidad_personas", $this->cantidad_personas);
        $stmt->bindParam(":estado", $this->estado);
        $stmt->bindParam(":observaciones", $this->observaciones);

        if($stmt->execute()) {
            return true;
        }
        // Log error if execution fails
        error_log("Database error on update: " . implode(" - ", $stmt->errorInfo()));
        return false;
    }

    function delete() {
        $query = "DELETE FROM " . $this->table_name . " WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $this->id=htmlspecialchars(strip_tags($this->id));
        $stmt->bindParam(1, $this->id);
        if($stmt->execute()) {
            return true;
        }
        return false;
    }
}
?>
