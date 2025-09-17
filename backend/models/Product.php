<?php
require_once __DIR__ . '/../core/Database.php';

class Product {
    private $conn;
    private $table_name = "productos";

    public function __construct() {
        $this->conn = Database::getInstance();
    }

    /**
     * Lee todos los productos de la base de datos.
     *
     * @return PDOStatement
     */
    public function readAll() {
        $query = "SELECT p.id, p.nombre, p.descripcion, p.precio, c.nombre as categoria_nombre
                  FROM " . $this->table_name . " p
                  LEFT JOIN categorias_producto c ON p.id_categoria = c.id
                  ORDER BY p.nombre ASC";

        $stmt = $this->conn->prepare($query);
        $stmt->execute();

        return $stmt;
    }
}
?>
