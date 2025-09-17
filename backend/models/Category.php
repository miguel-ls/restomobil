<?php
require_once __DIR__ . '/../core/Database.php';

class Category {
    private $conn;
    private $table_name = "categorias_producto";

    public function __construct() {
        $this->conn = Database::getInstance();
    }

    /**
     * Lee todas las categorías.
     * @return PDOStatement
     */
    public function readAll() {
        $query = "CALL sp_getAllCategories()";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    /**
     * Crea una nueva categoría.
     * @return PDOStatement|false
     */
    public function create($nombre, $descripcion) {
        $query = "CALL sp_createCategory(:nombre, :descripcion)";
        $stmt = $this->conn->prepare($query);

        // Limpiar datos
        $nombre = htmlspecialchars(strip_tags($nombre));
        $descripcion = htmlspecialchars(strip_tags($descripcion));

        // Enlazar parámetros
        $stmt->bindParam(':nombre', $nombre);
        $stmt->bindParam(':descripcion', $descripcion);

        if ($stmt->execute()) {
            return $stmt;
        }
        return false;
    }

    /**
     * Lee una sola categoría por su ID.
     * @return array|false
     */
    public function readOne($id) {
        $query = "CALL sp_readOneCategory(:id)";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Actualiza una categoría existente.
     * @return bool
     */
    public function update($id, $nombre, $descripcion) {
        $query = "CALL sp_updateCategory(:id, :nombre, :descripcion)";
        $stmt = $this->conn->prepare($query);

        // Limpiar datos
        $id = htmlspecialchars(strip_tags($id));
        $nombre = htmlspecialchars(strip_tags($nombre));
        $descripcion = htmlspecialchars(strip_tags($descripcion));

        // Enlazar parámetros
        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':nombre', $nombre);
        $stmt->bindParam(':descripcion', $descripcion);

        if ($stmt->execute()) {
            return true;
        }
        return false;
    }

    /**
     * Elimina una categoría.
     * @return bool
     */
    public function delete($id) {
        $query = "CALL sp_deleteCategory(:id)";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);

        if ($stmt->execute()) {
            return true;
        }
        return false;
    }
}
?>
