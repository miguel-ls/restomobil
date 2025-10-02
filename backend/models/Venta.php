<?php
class Venta {
    private $pdo;
    private $table_name = "ventas";

    // Propiedades de la venta
    public $id;
    public $id_pedido;
    public $id_cliente;
    public $id_usuario_cajero;
    public $id_tipo_documento_venta;
    public $id_serie_documento;
    public $numero_documento;
    public $total;
    public $porcentaje;
    public $base;
    public $impuesto;
    public $estado;
    public $fecha_emision;

    public function __construct($db) {
        $this->pdo = $db;
    }

    // Crear una nueva venta a partir de un pedido
    public function crearVentaDesdePedido($id_pedido, $id_usuario_cajero, $id_tipo_documento_venta, $id_serie_documento, $id_cliente) {
        $query = "CALL sp_crear_venta_con_impuestos(:id_pedido, :id_cliente, :id_usuario_cajero, :id_tipo_documento_venta, :id_serie_documento)";

        $stmt = $this->pdo->prepare($query);

        // Vincular parámetros
        $stmt->bindParam(':id_pedido', $id_pedido, PDO::PARAM_INT);
        $stmt->bindParam(':id_cliente', $id_cliente, PDO::PARAM_INT);
        $stmt->bindParam(':id_usuario_cajero', $id_usuario_cajero, PDO::PARAM_INT);
        $stmt->bindParam(':id_tipo_documento_venta', $id_tipo_documento_venta, PDO::PARAM_INT);
        $stmt->bindParam(':id_serie_documento', $id_serie_documento, PDO::PARAM_INT);

        try {
            $stmt->execute();
            // El procedimiento devuelve una fila con el id_venta y numero_documento
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            // Manejar el error
            error_log("Error al ejecutar sp_crear_venta_con_impuestos: " . $e->getMessage());
            return ["error" => $e->getMessage()];
        }
    }

    // Leer todas las ventas con filtros y paginación
    public function leerTodo($filtros) {
        $query = "CALL sp_leer_ventas(
            :fecha_inicio,
            :fecha_fin,
            :estado,
            :id_tipo_documento,
            :search,
            :page,
            :limit
        )";

        $stmt = $this->pdo->prepare($query);

        // Vincular parámetros de los filtros
        $stmt->bindParam(':fecha_inicio', $filtros['fecha_inicio']);
        $stmt->bindParam(':fecha_fin', $filtros['fecha_fin']);
        $stmt->bindParam(':estado', $filtros['estado']);
        $stmt->bindParam(':id_tipo_documento', $filtros['id_tipo_documento'], PDO::PARAM_INT);
        $stmt->bindParam(':search', $filtros['search']);
        $stmt->bindParam(':page', $filtros['page'], PDO::PARAM_INT);
        $stmt->bindParam(':limit', $filtros['limit'], PDO::PARAM_INT);

        $stmt->execute();
        return $stmt;
    }

    // Contar el total de registros para la paginación
    public function contarTodo($filtros) {
        $query = "CALL sp_contar_ventas(
            :fecha_inicio,
            :fecha_fin,
            :estado,
            :id_tipo_documento,
            :search
        )";

        $stmt = $this->pdo->prepare($query);

        $stmt->bindParam(':fecha_inicio', $filtros['fecha_inicio']);
        $stmt->bindParam(':fecha_fin', $filtros['fecha_fin']);
        $stmt->bindParam(':estado', $filtros['estado']);
        $stmt->bindParam(':id_tipo_documento', $filtros['id_tipo_documento'], PDO::PARAM_INT);
        $stmt->bindParam(':search', $filtros['search']);

        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $stmt->closeCursor(); // Cerrar cursor para la siguiente llamada
        return $row['total'];
    }

    // Actualizar una venta y recalcular impuestos
    public function update($id, $fecha_emision) {
        $query = "CALL sp_update_venta(:id, :fecha_emision)";
        $stmt = $this->pdo->prepare($query);

        // Limpiar y vincular los parámetros
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->bindParam(':fecha_emision', $fecha_emision);

        try {
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error al ejecutar sp_update_venta: " . $e->getMessage());
            return false;
        }
    }

    // Leer una sola venta por ID
    public function leerUno($id) {
        $query = "CALL sp_leer_venta_por_id(:id)";
        $stmt = $this->pdo->prepare($query);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);

        try {
            $stmt->execute();

            // Obtener la fila de la venta principal
            $venta_data = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$venta_data) {
                return null;
            }

            // Mover al siguiente conjunto de resultados (los items)
            $stmt->nextRowset();

            // Obtener todos los items de la venta
            $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $venta_data['items'] = $items;

            $stmt->closeCursor();
            return $venta_data;

        } catch (PDOException $e) {
            error_log("Error al ejecutar sp_leer_venta_por_id: " . $e->getMessage());
            return null;
        }
    }
}
?>