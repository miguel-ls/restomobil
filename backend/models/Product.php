<?php
require_once __DIR__ . '/../core/Database.php';

class Product {
    private $conn;
    private $table_name = "productos";

    public function __construct() {
        $this->conn = Database::getInstance();
    }

    /**
     * Lee todos los productos de la base de datos, con opción de filtrar por múltiples campos.
     *
     * @return PDOStatement
     */
    public function readAll($filters = [], $page = 1, $page_size = 12) {
        $query = "CALL sp_getAllProducts(:id, :nombre, :descripcion, :precio, :categoria_nombre, :estado, :controlar_stock, :page_number, :page_size)";
        $stmt = $this->conn->prepare($query);

        // Asignar valores de los filtros, usando null si no están definidos
        $id = $filters['id'] ?? null;
        $nombre = $filters['nombre'] ?? null;
        $descripcion = $filters['descripcion'] ?? null;
        $precio = $filters['precio'] ?? null;
        $categoria_nombre = $filters['categoria_nombre'] ?? null;
        $estado = $filters['estado'] ?? null;
        $controlar_stock = isset($filters['controlar_stock']) && $filters['controlar_stock'] !== '' ? filter_var($filters['controlar_stock'], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) : null;

        // Enlazar parámetros
        $stmt->bindValue(':id', $id, $id === null ? PDO::PARAM_NULL : PDO::PARAM_INT);
        $stmt->bindValue(':nombre', $nombre, $nombre === null ? PDO::PARAM_NULL : PDO::PARAM_STR);
        $stmt->bindValue(':descripcion', $descripcion, $descripcion === null ? PDO::PARAM_NULL : PDO::PARAM_STR);
        $stmt->bindValue(':precio', $precio, $precio === null ? PDO::PARAM_NULL : PDO::PARAM_STR);
        $stmt->bindValue(':categoria_nombre', $categoria_nombre, $categoria_nombre === null ? PDO::PARAM_NULL : PDO::PARAM_STR);
        $stmt->bindValue(':estado', $estado, $estado === null ? PDO::PARAM_NULL : PDO::PARAM_STR);
        $stmt->bindValue(':controlar_stock', $controlar_stock, $controlar_stock === null ? PDO::PARAM_NULL : PDO::PARAM_BOOL);
        $stmt->bindValue(':page_number', $page, PDO::PARAM_INT);
        $stmt->bindValue(':page_size', $page_size, PDO::PARAM_INT);

        $stmt->execute();
        return $stmt;
    }

    public function countAll($filters = []) {
        $query = "CALL sp_countAllProducts(:id, :nombre, :descripcion, :precio, :categoria_nombre, :estado, :controlar_stock)";
        $stmt = $this->conn->prepare($query);

        // Asignar valores de los filtros, usando null si no están definidos
        $id = $filters['id'] ?? null;
        $nombre = $filters['nombre'] ?? null;
        $descripcion = $filters['descripcion'] ?? null;
        $precio = $filters['precio'] ?? null;
        $categoria_nombre = $filters['categoria_nombre'] ?? null;
        $estado = $filters['estado'] ?? null;
        $controlar_stock = isset($filters['controlar_stock']) && $filters['controlar_stock'] !== '' ? filter_var($filters['controlar_stock'], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) : null;

        // Enlazar parámetros
        $stmt->bindValue(':id', $id, $id === null ? PDO::PARAM_NULL : PDO::PARAM_INT);
        $stmt->bindValue(':nombre', $nombre, $nombre === null ? PDO::PARAM_NULL : PDO::PARAM_STR);
        $stmt->bindValue(':descripcion', $descripcion, $descripcion === null ? PDO::PARAM_NULL : PDO::PARAM_STR);
        $stmt->bindValue(':precio', $precio, $precio === null ? PDO::PARAM_NULL : PDO::PARAM_STR);
        $stmt->bindValue(':categoria_nombre', $categoria_nombre, $categoria_nombre === null ? PDO::PARAM_NULL : PDO::PARAM_STR);
        $stmt->bindValue(':estado', $estado, $estado === null ? PDO::PARAM_NULL : PDO::PARAM_STR);
        $stmt->bindValue(':controlar_stock', $controlar_stock, $controlar_stock === null ? PDO::PARAM_NULL : PDO::PARAM_BOOL);

        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row['total_records'];
    }

    /**
     * Crea un nuevo producto.
     * @return PDOStatement|false
     */
    public function create($nombre, $descripcion, $precio, $id_categoria, $estado, $controlar_stock) {
        $query = "CALL sp_createProduct(:nombre, :descripcion, :precio, :id_categoria, :estado, :controlar_stock)";
        $stmt = $this->conn->prepare($query);

        // Limpiar datos
        $nombre = htmlspecialchars(strip_tags($nombre));
        $descripcion = htmlspecialchars(strip_tags($descripcion));
        $precio = htmlspecialchars(strip_tags($precio));
        $id_categoria = htmlspecialchars(strip_tags($id_categoria));
        $estado = htmlspecialchars(strip_tags($estado));
        $controlar_stock = (bool)$controlar_stock;

        // Enlazar parámetros
        $stmt->bindParam(':nombre', $nombre);
        $stmt->bindParam(':descripcion', $descripcion);
        $stmt->bindParam(':precio', $precio);
        $stmt->bindParam(':id_categoria', $id_categoria);
        $stmt->bindParam(':estado', $estado);
        $stmt->bindParam(':controlar_stock', $controlar_stock, PDO::PARAM_BOOL);

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
    public function update($id, $nombre, $descripcion, $precio, $id_categoria, $estado, $controlar_stock) {
        $query = "CALL sp_updateProduct(:id, :nombre, :descripcion, :precio, :id_categoria, :estado, :controlar_stock)";
        $stmt = $this->conn->prepare($query);

        // Limpiar datos
        $id = htmlspecialchars(strip_tags($id));
        $nombre = htmlspecialchars(strip_tags($nombre));
        $descripcion = htmlspecialchars(strip_tags($descripcion));
        $precio = htmlspecialchars(strip_tags($precio));
        $id_categoria = htmlspecialchars(strip_tags($id_categoria));
        $estado = htmlspecialchars(strip_tags($estado));
        $controlar_stock = (bool)$controlar_stock;

        // Enlazar parámetros
        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':nombre', $nombre);
        $stmt->bindParam(':descripcion', $descripcion);
        $stmt->bindParam(':precio', $precio);
        $stmt->bindParam(':id_categoria', $id_categoria);
        $stmt->bindParam(':estado', $estado);
        $stmt->bindParam(':controlar_stock', $controlar_stock, PDO::PARAM_BOOL);

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