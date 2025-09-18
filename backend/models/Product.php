<?php
require_once __DIR__ . '/../core/Database.php';

class Product {
    private $conn;
    private $table_name = "productos";

    public function __construct() {
        $this->conn = Database::getInstance();
    }

    /**
     * Lee todos los productos de la base de datos, con opción de filtrar por estado.
     *
     * @return PDOStatement
     */
    public function readAll($estado = null) {
        $query = "CALL sp_getAllProducts(:estado)";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':estado', $estado, PDO::PARAM_STR);
        $stmt->execute();
        return $stmt;
    }

    /**
     * Crea un nuevo producto.
     * @return PDOStatement|false
     */
    public function create($nombre, $descripcion, $precio, $id_categoria, $estado) {
        $query = "CALL sp_createProduct(:nombre, :descripcion, :precio, :id_categoria, :estado)";
        $stmt = $this->conn->prepare($query);

        // Limpiar datos
        $nombre = htmlspecialchars(strip_tags($nombre));
        $descripcion = htmlspecialchars(strip_tags($descripcion));
        $precio = htmlspecialchars(strip_tags($precio));
        $id_categoria = htmlspecialchars(strip_tags($id_categoria));
        $estado = htmlspecialchars(strip_tags($estado));

        // Enlazar parámetros
        $stmt->bindParam(':nombre', $nombre);
        $stmt->bindParam(':descripcion', $descripcion);
        $stmt->bindParam(':precio', $precio);
        $stmt->bindParam(':id_categoria', $id_categoria);
        $stmt->bindParam(':estado', $estado);

        if ($stmt->execute()) {
            return $stmt;
        }
        return false;
    }

    /**
     * Lee un solo producto por su ID.
     * @return array|false
     */
    public function readOne($id) {
        $query = "CALL sp_readOneProduct(:id)";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Actualiza un producto existente.
     * @return bool
     */
    public function update($id, $nombre, $descripcion, $precio, $id_categoria, $estado) {
        $query = "CALL sp_updateProduct(:id, :nombre, :descripcion, :precio, :id_categoria, :estado)";
        $stmt = $this->conn->prepare($query);

        // Limpiar datos
        $id = htmlspecialchars(strip_tags($id));
        $nombre = htmlspecialchars(strip_tags($nombre));
        $descripcion = htmlspecialchars(strip_tags($descripcion));
        $precio = htmlspecialchars(strip_tags($precio));
        $id_categoria = htmlspecialchars(strip_tags($id_categoria));
        $estado = htmlspecialchars(strip_tags($estado));

        // Enlazar parámetros
        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':nombre', $nombre);
        $stmt->bindParam(':descripcion', $descripcion);
        $stmt->bindParam(':precio', $precio);
        $stmt->bindParam(':id_categoria', $id_categoria);
        $stmt->bindParam(':estado', $estado);

        if ($stmt->execute()) {
            return true;
        }
        return false;
    }

    /**
     * Elimina un producto.
     * @return bool
     */
    public function delete($id) {
        $query = "CALL sp_deleteProduct(:id)";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);

        if ($stmt->execute()) {
            return true;
        }
        return false;
    }
}
?>
