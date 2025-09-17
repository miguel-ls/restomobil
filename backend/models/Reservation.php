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
        $database = new Database();
        $this->conn = $database->getConnection();
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
}
?>
