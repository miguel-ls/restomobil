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
        $query = "CALL sp_getAllProducts(:id, :nombre, :descripcion, :precio, :categoria_nombre, :estado, :page_number, :page_size)";
        $stmt = $this->conn->prepare($query);

        // Asignar valores de los filtros, usando null si no están definidos
        $id = $filters['id'] ?? null;
        $nombre = $filters['nombre'] ?? null;
        $descripcion = $filters['descripcion'] ?? null;
        $precio = $filters['precio'] ?? null;
        $categoria_nombre = $filters['categoria_nombre'] ?? null;
        $estado = $filters['estado'] ?? null;

        // Enlazar parámetros
        $this->bindAllParams($stmt, $filters, ['page' => $page, 'page_size' => $page_size]);

        $stmt->execute();
        return $stmt;
    }

    public function countAll($filters = []) {
        $query = "CALL sp_countAllProducts(:id, :nombre, :descripcion, :precio, :categoria_nombre, :estado)";
        $stmt = $this->conn->prepare($query);

        // Enlazar parámetros
        $this->bindAllParams($stmt, $filters);

        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row['total_records'];
    }

    private function bindAllParams($stmt, $filters, $pagination = []) {
        $id = $filters['id'] ?? null;
        $nombre = $filters['nombre'] ?? null;
        $descripcion = $filters['descripcion'] ?? null;
        $precio = $filters['precio'] ?? null;
        $categoria_nombre = $filters['categoria_nombre'] ?? null;
        $estado = $filters['estado'] ?? null;
        $page = $pagination['page'] ?? null;
        $page_size = $pagination['page_size'] ?? null;

        $this->bindParamIfValue($stmt, ':id', $id, PDO::PARAM_INT);
        $this->bindParamIfValue($stmt, ':nombre', $nombre, PDO::PARAM_STR);
        $this->bindParamIfValue($stmt, ':descripcion', $descripcion, PDO::PARAM_STR);
        $this->bindParamIfValue($stmt, ':precio', $precio, PDO::PARAM_STR);
        $this->bindParamIfValue($stmt, ':categoria_nombre', $categoria_nombre, PDO::PARAM_STR);
        $this->bindParamIfValue($stmt, ':estado', $estado, PDO::PARAM_STR);

        if ($page !== null) {
            $stmt->bindParam(':page_number', $page, PDO::PARAM_INT);
        }
        if ($page_size !== null) {
            $stmt->bindParam(':page_size', $page_size, PDO::PARAM_INT);
        }
    }

    private function bindParamIfValue($stmt, $param, $value, $type) {
        if ($value !== null) {
            $stmt->bindParam($param, $value, $type);
        } else {
            $stmt->bindValue($param, null, PDO::PARAM_NULL);
        }
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
