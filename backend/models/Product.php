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
        $query = "CALL sp_getAllProducts()";

        $stmt = $this->conn->prepare($query);
        $stmt->execute();

        return $stmt;
    }
}
?>
